<?php
  // 세션 시작
  session_start();

  // 화면 출력 시 특수문자 보호
  function h($str){
    return htmlspecialchars((string)$str, ENT_QUOTES, "UTF-8");
  }

  // 로그인하지 않은 사용자는 접근 금지
  if(!isset($_SESSION['user_id'])){
    echo "
      <script>
        alert('로그인 후 접속해주세요.');
        location.href='../member_pg/login.php';
      </script>
    ";
    exit;
  }

    // DB 연결
  include "../common/db.php";

  // 로그인한 회원 번호
  $user_no = (int)$_SESSION['user_no'];

  // 공공데이터포털 인증키 파일 불러오기
  $holiday_service_key = "";

  if(file_exists("../common/holiday_key.php")){
    include "../common/holiday_key.php";
  }

  // 주소창에서 year, month 값 받기
  $year = $_GET['year'] ?? date("Y");
  $month = $_GET['month'] ?? date("n");

  $year = (int)$year;
  $month = (int)$month;

  if($month < 1 || $month > 12){
    $month = (int)date("n");
  }

  $month_text = sprintf("%02d", $month);

  // 공휴일 정보를 담을 배열
  $holidays = [];
  $api_message = "";

  // 공휴일 API 호출 함수
  function getHolidayInfo($year, $month_text, $service_key){
    $holiday_list = [];
    $message = "";

    if($service_key == "" || $service_key == "여기에_공공데이터포털_인코딩_인증키를_넣으세요"){
      return [$holiday_list, "공공데이터포털 인증키를 입력해주세요."];
    }

    $api_url = "http://apis.data.go.kr/B090041/openapi/service/SpcdeInfoService/getHoliDeInfo";

    $request_url = $api_url
      . "?ServiceKey=" . $service_key
      . "&solYear=" . $year
      . "&solMonth=" . $month_text;

    $xml_data = @file_get_contents($request_url);

    if($xml_data === false){
      return [$holiday_list, "공휴일 API 호출에 실패했습니다."];
    }

    $xml = @simplexml_load_string($xml_data);

    if($xml === false){
      return [$holiday_list, "공휴일 API 응답을 읽을 수 없습니다."];
    }

    if(isset($xml->header->resultCode) && (string)$xml->header->resultCode != "00"){
      $message = "API 오류: " . (string)$xml->header->resultMsg;
      return [$holiday_list, $message];
    }

    if(isset($xml->body->items->item)){
      foreach($xml->body->items->item as $item){
        $locdate = (string)$item->locdate;
        $dateName = (string)$item->dateName;
        $isHoliday = (string)$item->isHoliday;

        if($isHoliday == "Y"){
          $holiday_list[$locdate] = $dateName;
        }
      }
    }

    return [$holiday_list, $message];
  }

  // API에서 공휴일 정보 가져오기
  list($holidays, $api_message) = getHolidayInfo($year, $month_text, $holiday_service_key);

  // 달력 만들기용 날짜 정보
  $first_date = sprintf("%04d-%02d-01", $year, $month);
  $first_day = date("w", strtotime($first_date));
  $last_day = date("t", strtotime($first_date));

  $prev_time = strtotime($first_date . " -1 month");
  $next_time = strtotime($first_date . " +1 month");

  $prev_year = date("Y", $prev_time);
  $prev_month = date("n", $prev_time);

  $next_year = date("Y", $next_time);
  $next_month = date("n", $next_time);

  $today_key = date("Ymd");

  function get_status_text($status){
    if($status == "출석"){
      return "출석";
    }else if($status == "결석"){
      return "결석";
    }else if($status == "홀드"){
      return "홀드";
    }else if($status == "홀드신청중"){
      return "홀드신청중";
    }else if($status == "홀드취소요청중"){
      return "홀드취소요청중";
    }else{
      return "예정";
    }
  }

  // 수업 상태별 CSS 클래스 이름
    function get_status_class($status){
      if($status == "출석"){
        return "status_attend";
      }else if($status == "결석"){
        return "status_absent";
      }else if($status == "홀드"){
        return "status_hold";
      }else if($status == "홀드신청중"){
        return "status_hold_wait";
      }else if($status == "홀드취소요청중"){
        return "status_hold_cancel_wait";
      }else{
        return "status_plan";
      }
    }
  // DB에서 로그인한 학생의 수업 일정 가져오기
    $lesson_sql = "
        SELECT
          s.*,
          p.course_name,
          m.user_name,
          origin.lesson_date AS makeup_origin_date,

        (
          SELECT COUNT(*)
          FROM hk_hold_requests hr
          WHERE hr.lesson_no = s.no
          AND hr.member_no = s.member_no
          AND hr.request_type = '홀드신청'
          AND hr.request_status = '대기'
        ) AS hold_request_wait_count,

        (
          SELECT COUNT(*)
          FROM hk_hold_requests hr
          WHERE hr.lesson_no = s.no
          AND hr.member_no = s.member_no
          AND hr.request_type = '홀드취소'
          AND hr.request_status = '대기'
        ) AS hold_cancel_wait_count

      FROM hk_lesson_schedule s

      LEFT JOIN hk_payments p
      ON s.payment_no = p.no

      LEFT JOIN hk_members m
      ON s.member_no = m.no

      LEFT JOIN hk_lesson_schedule origin
      ON s.makeup_for_lesson_no = origin.no

      WHERE s.member_no = '$user_no'

      ORDER BY s.lesson_date ASC, s.lesson_time ASC, s.no ASC
    ";

  $lesson_result = mysqli_query($db, $lesson_sql);

  // 위쪽 카드 목록용 배열
  $lessons = [];

  // 달력 표시용 배열
  $calendar_lessons = [];

  if($lesson_result){
    while($row = mysqli_fetch_array($lesson_result, MYSQLI_ASSOC)){

      $lesson_time = strtotime($row['lesson_date']);

      if($lesson_time === false){
        continue;
      }

      // 20260619 형태. 달력 날짜와 비교할 때 사용
      $date_key = date("Ymd", $lesson_time);

      // 화면 출력용 날짜
      $date_text = date("Y.m.d", $lesson_time);

      // 상태값
      $raw_status = $row['attendance_status'] ?? '예정';

      // 아직 예정 수업인데 홀드 신청이 대기 중이면
      if($raw_status == "예정" && (int)$row['hold_request_wait_count'] > 0){
        $raw_status = "홀드신청중";
      }

      // 이미 홀드된 수업인데 홀드 취소 요청이 대기 중이면
      if($raw_status == "홀드" && (int)$row['hold_cancel_wait_count'] > 0){
        $raw_status = "홀드취소요청중";
      }

      $status_text = get_status_text($raw_status);

      // 위쪽 카드에서는 예정을 예정수업으로 보여줌
      $card_status_text = $status_text;

      if($status_text == "예정"){
        $card_status_text = "예정수업";
      }
            
      // 수업 입장 가능 시간 계산
      // 수업 시작 5분 전 ~ 수업 시작 후 2시간까지 입장 가능으로 임시 설정
      $now_time = time();

      $lesson_start_text = $row['lesson_date'] . " " . substr($row['lesson_time'], 0, 5);
      $lesson_start_time = strtotime($lesson_start_text);

      $can_enter = false;

                // 홀드 신청 가능 여부
          // 예정 상태이고, 아직 수업 시간이 지나지 않은 수업만 홀드 신청 가능
          $is_past_lesson = false;

          if($lesson_start_time !== false && $now_time > $lesson_start_time){
            $is_past_lesson = true;
          }

          $can_hold_request = false;

          if($raw_status == "예정" && $is_past_lesson == false){
            $can_hold_request = true;
          }

      if($lesson_start_time !== false){
        $enter_start_time = strtotime("-5 minutes", $lesson_start_time);
        $enter_end_time = strtotime("+2 hours", $lesson_start_time);

        if($now_time >= $enter_start_time && $now_time <= $enter_end_time){
          $can_enter = true;
        }
      }

        // 수업시간 표시용 만들기
        // DB에는 08:00만 저장되어 있어도 화면에는 08:00~08:50으로 표시
        $lesson_time_start = substr($row['lesson_time'], 0, 5);
        $lesson_time_end = "";

        $lesson_time_timestamp = strtotime($lesson_time_start);

        if($lesson_time_timestamp !== false){
          $lesson_time_end = date("H:i", strtotime("+50 minutes", $lesson_time_timestamp));
        }

        $lesson_time_display = $lesson_time_start;

        if($lesson_time_end != ""){
          $lesson_time_display = $lesson_time_start . "~" . $lesson_time_end;
        }

      $lesson_item = [
        "lesson_no" => $row['no'],
        "date_key" => $date_key,
        "date" => $date_text,
        "weekday" => $row['lesson_day'],
        "course" => $row['course_name'] ?? "화상 한국어",
        "time" => $lesson_time_display,
        "teacher" => $row['teacher_name'],
        "student" => $row['user_name'],
        "zoom_link" => $row['zoom_link'] ?? "",
        "can_enter" => $can_enter,
        "can_hold_request" => $can_hold_request,
        "status" => $card_status_text,
        "calendar_status" => $status_text,
        "status_class" => get_status_class($raw_status),
        "lesson_type" => $row['lesson_type'] ?? "정규수업",
        "makeup_for_lesson_no" => $row['makeup_for_lesson_no'] ?? "",
        "makeup_origin_date" => $row['makeup_origin_date'] ?? ""
      ];

      // 카드 목록에 넣기
      $lessons[] = $lesson_item;

      // 달력용 배열에 날짜별로 넣기
      if(!isset($calendar_lessons[$date_key])){
        $calendar_lessons[$date_key] = [];
      }

      $calendar_lessons[$date_key][] = $lesson_item;
    }
  }

      // 수업 목록 페이지네이션
      // 기본은 3개씩 표시
      // 처음 들어왔을 때만 다음 예정 수업이 가능하면 2번째 칸에 오도록 조정
      $per_page = 3;
      $total_lessons = count($lessons);

      // lesson_start가 있으면 사용자가 이전/다음을 누른 상태
      $lesson_start_param = $_GET['lesson_start'] ?? '';

      if($lesson_start_param !== ''){
        $start_index = (int)$lesson_start_param;
      }else{

        // 처음 들어왔을 때 기본 시작 위치
        $start_index = 0;

        // 바로 다음 예정 수업 찾기
        $next_lesson_index = -1;
        $now_time = time();

        for($i = 0; $i < $total_lessons; $i++){

          $lesson_date = $lessons[$i]['date'] ?? "";
          $lesson_time = $lessons[$i]['time'] ?? "";
          $lesson_status = $lessons[$i]['calendar_status'] ?? "";

          $lesson_datetime_text = $lesson_date . " " . substr($lesson_time, 0, 5);
          $lesson_datetime = strtotime($lesson_datetime_text);

          if($lesson_status == "예정" && $lesson_datetime !== false && $lesson_datetime >= $now_time){
            $next_lesson_index = $i;
            break;
          }
        }

        // 다음 예정 수업이 첫 수업이면 첫 번째 칸
        if($next_lesson_index == 0){
          $start_index = 0;
        }

        // 다음 예정 수업 앞에 지난 수업이 있으면, 다음 예정 수업이 두 번째 칸
        else if($next_lesson_index > 0){
          $start_index = $next_lesson_index - 1;
        }
      }

      // 범위 보정
      $max_start_index = $total_lessons - $per_page;

      if($max_start_index < 0){
        $max_start_index = 0;
      }

      if($start_index < 0){
        $start_index = 0;
      }

      if($start_index > $max_start_index){
        $start_index = $max_start_index;
      }

      // 실제 화면에 보여줄 수업 3개
      $current_lessons = array_slice($lessons, $start_index, $per_page);

      // 이전/다음 버튼용 값
      $prev_start_index = $start_index - $per_page;
      $next_start_index = $start_index + $per_page;

      if($prev_start_index < 0){
        $prev_start_index = 0;
      }

      $has_prev_lesson_page = ($start_index > 0);
      $has_next_lesson_page = ($next_start_index < $total_lessons);

      // 이전 버튼 URL
      $prev_params = $_GET;
      unset($prev_params['lesson_page']);
      $prev_params['lesson_start'] = $prev_start_index;
      $prev_lesson_url = "./schedule.php?" . http_build_query($prev_params);

      // 다음 버튼 URL
      $next_params = $_GET;
      unset($next_params['lesson_page']);
      $next_params['lesson_start'] = $next_start_index;
      $next_lesson_url = "./schedule.php?" . http_build_query($next_params);

      // 기존 코드에서 쓰고 있을 수 있는 변수 에러 방지용
      $total_pages = ceil($total_lessons / $per_page);

      if($total_pages < 1){
        $total_pages = 1;
      }

      $lesson_page = floor($start_index / $per_page) + 1;
    ?>

<!DOCTYPE html>
<html lang="ko">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <title>Hello Korean - Class Schedule</title>

  <link rel="stylesheet" href="../common/common.css">
  <link rel="stylesheet" href="./mypage.css">
</head>

<body>

<header>
  <div class="container">

    <a href="../main_pg/01main_pg.php">
      <img src="../main_pg/image/logo.png" alt="main_logo" class="logo">
    </a>

    <nav>
      <ul>
        <li><a href="../main_pg/01main_pg.php">Home</a></li>
        <li><a href="#">코스</a></li>
        <li><a href="#">강사소개</a></li>
        <li><a href="../course_pg/course_register.php" class="course_register_btn">수강신청</a></li>
        <li><a href="../contact_pg/contact.php">고객센터</a></li>
        <li><a href="../board_pg/board.php" class="board">게시판</a></li>

        <li>
          <a href="../mypage_pg/mypage.php" class="mypage_btn">My page</a>
        </li>

        <li>
          <a href="../member_pg/logout.php" class="login_btn">로그아웃</a>
        </li>

        <li>
          <a href="#" class="join_btn"><?php echo h($_SESSION['user_id']); ?>님</a>
        </li>
      </ul>
    </nav>

  </div>
</header>

<main>

  <section id="mypage_visual">
    <div class="container">
      <h2>My page</h2>
    </div>
  </section>

  <section id="mypage_content">
    <div class="container">

      <div class="mypage_nav">
        <ul>
          <li><a href="./mypage.php">수강신청현황</a></li>
          <li><a href="./schedule.php" class="active">수업스케줄</a></li>
          <li><a href="#" class="disabled" onclick="return false;">쿠폰관리</a></li>
          <li><a href="#" class="disabled" onclick="return false;">결제내역</a></li>
          <li><a href="./profile.php">개인정보수정</a></li>
        </ul>
      </div>

      <div class="mypage_box schedule_box">

        <!-- 예정된 수업 -->
        <div class="lesson_section top_lesson_section">

          <div class="lesson_title_row">
            <h4>수업스케줄</h4>
            <p>수업 입장은 수업 시작 5분 전부터 가능합니다.</p>
          </div>

          <?php
            foreach($current_lessons as $lesson){
          ?>

            <div class="lesson_card_new">

              <!-- 왼쪽 상태 탭 -->
                <div class="lesson_main_info">

                  <div class="lesson_status_tab">
                    <?php echo h($lesson['date']); ?> (<?php echo h($lesson['weekday']); ?>)
                  </div>

                <div class="lesson_course_line">
                  <span><?php echo h($lesson['course']); ?></span>

                <?php
                  if($lesson['lesson_type'] == "홀드 보강수업"){

                    $origin_date_text = "";

                    if($lesson['makeup_origin_date'] != ""){
                      $origin_date_text = date("Y.m.d", strtotime($lesson['makeup_origin_date']));
                    }
                ?>
                  <em style="margin-left:8px; padding:3px 8px; background-color:#6c63ff; color:#fff; border-radius:20px; font-size:12px; font-style:normal;">
                    <?php echo $origin_date_text; ?>의 보강수업
                  </em>
                <?php
                  }
                ?>

                  <strong><?php echo h($lesson['time']); ?></strong>
                </div>

                <div class="lesson_teacher_line">
                  <span>강사</span>
                  <strong><?php echo h($lesson['teacher']); ?></strong>
                </div>

              </div>

              <!-- 오른쪽 수업 입장 -->
              <div class="lesson_enter_area">

                <?php
                  // 출석 완료
                  if($lesson['calendar_status'] == "출석"){
                ?>

                <button type="button" class="enter_circle_btn enter_status_btn enter_status_attend" disabled>
                  <span class="attend_check_icon">✓</span>
                  <span>출석</span>
                </button>

                <?php
                  // 결석
                  }else if($lesson['calendar_status'] == "결석"){
                ?>

                  <button type="button" class="enter_circle_btn enter_status_btn enter_status_absent" disabled>
                    결석
                  </button>

                <?php
                  // 홀드
                  }else if($lesson['calendar_status'] == "홀드"){
                ?>

                  <button type="button" class="enter_circle_btn enter_status_btn enter_status_hold" disabled>
                    홀드
                  </button>

                <?php
                  // 예정 수업 + 입장 가능 시간 + 링크 있음
                  }else if($lesson['can_enter'] == true && $lesson['zoom_link'] != ""){
                ?>

                  <button
                    type="button"
                    class="enter_circle_btn enter_active"
                    onclick="window.open('<?php echo h($lesson['zoom_link']); ?>', '_blank');"
                  >
                    수업<br>입장
                  </button>

                <?php
                  // 입장 가능 시간이지만 링크가 아직 없음
                  }else if($lesson['can_enter'] == true && $lesson['zoom_link'] == ""){
                ?>

                  <button
                    type="button"
                    class="enter_circle_btn"
                    onclick="alert('수업 링크 준비중입니다.');"
                  >
                    수업<br>입장
                  </button>

                <?php
                  // 아직 입장 가능 시간이 아님
                  }else{
                ?>

                  <button
                    type="button"
                    class="enter_circle_btn enter_waiting"
                    onclick="alert('수업시작 5분전에 \'수업입장\' 버튼이 활성화 됩니다.');"
                  >
                    수업<br>입장
                  </button>

                <?php
                  }
                ?>

              </div>

              <!-- 하단 버튼 -->
                <div class="lesson_bottom_buttons">

                  <button type="button" disabled>
                    교재보기
                  </button>

                    <button type="button" disabled>
                      Feedback
                    </button>
                      <?php
                        // 수업 상태에 따라 홀드 버튼 이름 변경
                        $hold_btn_text = "홀드신청";

                        if($lesson['calendar_status'] == "홀드신청중"){
                          $hold_btn_text = "신청취소";
                        }else if($lesson['calendar_status'] == "홀드"){
                          $hold_btn_text = "홀드취소";
                        }else if($lesson['calendar_status'] == "홀드취소요청중"){
                          $hold_btn_text = "취소요청중";
                        }else if($lesson['calendar_status'] == "출석" || $lesson['calendar_status'] == "결석"){
                          $hold_btn_text = "홀드불가";
                        }

                        if(
                          $lesson['calendar_status'] == "예정" ||
                          $lesson['calendar_status'] == "홀드신청중" ||
                          $lesson['calendar_status'] == "홀드" ||
                          $lesson['calendar_status'] == "홀드취소요청중"
                        ){
                      ?>

                      <?php
                        if($lesson['can_hold_request'] == true){
                      ?>
                        <button
                          type="button"
                          class="lesson_hold_btn"
                          onclick="location.href='./hold_request.php?lesson_no=<?php echo h($lesson['lesson_no']); ?>';"
                        >
                          <?php echo h($hold_btn_text); ?>
                        </button>
                      <?php
                        }else{
                      ?>
                        <button
                          type="button"
                          class="lesson_hold_btn hold_disabled_btn"
                          disabled
                        >
                          <?php echo h($hold_btn_text); ?>
                        </button>
                      <?php
                        }
                      ?>

                      <?php
                        }else{
                      ?>

                        <button
                          type="button"
                          onclick="alert('출석 또는 결석 처리된 수업은 홀드 신청/취소를 할 수 없습니다.');"
                        >
                          <?php echo h($hold_btn_text); ?>
                        </button>

                      <?php
                        }
                      ?>

                </div>

            </div>

          <?php
            }
          ?>

          <!-- 예정수업 페이지네이션 -->
          <div class="lesson_pagination">

            <?php
              if($has_prev_lesson_page){
            ?>
              <a href="<?php echo h($prev_lesson_url); ?>">이전</a>
            <?php
              }else{
            ?>
              <span class="page_disabled">이전</span>
            <?php
              }
            ?>

            <span class="page_info">
              <?php echo h($lesson_page); ?> / <?php echo h($total_pages); ?>
            </span>

            <?php
              if($has_next_lesson_page){
            ?>
              <a href="<?php echo h($next_lesson_url); ?>">다음</a>
            <?php
              }else{
            ?>
              <span class="page_disabled">다음</span>
            <?php
              }
            ?>

          </div>

        </div>

        <!-- 공휴일 안내 : 나중에 공공 API 연결 후 다시 표시 예정 -->
        <div class="holiday_notice" style="display:none;">
          <h4>한국 공휴일 안내</h4>
          <p>
            공휴일은 수업이 자동으로 skip될 수 있으니 반드시 수업 날짜를 확인해주세요.
          </p>

          <?php
            if($api_message != ""){
          ?>
            <p class="api_message"><?php echo h($api_message); ?></p>
          <?php
            }
          ?>
        </div>

        <!-- 달력 상단 -->
        <div class="calendar_header">
          <a href="./schedule.php?year=<?php echo $prev_year; ?>&month=<?php echo $prev_month; ?>&lesson_page=<?php echo $lesson_page; ?>" class="calendar_move">이전 달</a>

          <h4><?php echo $year; ?>년 <?php echo $month_text; ?>월</h4>

          <a href="./schedule.php?year=<?php echo $next_year; ?>&month=<?php echo $next_month; ?>&lesson_page=<?php echo $lesson_page; ?>" class="calendar_move">다음 달</a>
        </div>

        <!-- 달력 -->
        <table class="hk_calendar">
          <thead>
            <tr>
              <th class="sun">일</th>
              <th>월</th>
              <th>화</th>
              <th>수</th>
              <th>목</th>
              <th>금</th>
              <th class="sat">토</th>
            </tr>
          </thead>

          <tbody>
            <tr>
              <?php
                for($i = 0; $i < $first_day; $i++){
                  echo "<td class='empty'></td>";
                }

                for($day = 1; $day <= $last_day; $day++){
                  $date_key = sprintf("%04d%02d%02d", $year, $month, $day);

                  $class_name = "calendar_day";

                  if($date_key == $today_key){
                    $class_name .= " today";
                  }

                  if(isset($holidays[$date_key])){
                    $class_name .= " holiday";
                  }

                  echo "<td class='" . $class_name . "'>";
                  echo "<span class='day_num'>" . $day . "</span>";

                  // 공휴일 표시
                  if(isset($holidays[$date_key])){
                    echo "<span class='holiday_name'>" . h($holidays[$date_key]) . "</span>";
                  }

                  // 수업 일정 표시
                  if(isset($calendar_lessons[$date_key])){
                    foreach($calendar_lessons[$date_key] as $calendar_lesson){

                      echo "<div class='calendar_lesson_item'>";

                      echo "  <div class='calendar_student_name'>";
                      echo      h($calendar_lesson['student']);
                      echo "  </div>";

                      if($calendar_lesson['lesson_type'] == "홀드 보강수업"){
                      echo "  <div style='display:inline-block; margin-top:3px; padding:2px 6px; background-color:#6c63ff; color:#fff; border-radius:12px; font-size:11px;'>";
                      echo "    보강수업";
                      echo "  </div>";
                    }

                      echo "  <div class='calendar_teacher_name'>";
                      echo      h($calendar_lesson['teacher']) . " 강사님";
                      echo "  </div>";

                      echo "  <div class='calendar_time_status'>";
                      echo      h($calendar_lesson['time']);

                      echo "    <span class='calendar_status_badge " . h($calendar_lesson['status_class']) . "'>";
                      echo        h($calendar_lesson['calendar_status']);
                      echo "    </span>";

                      echo "  </div>";

                      echo "</div>";
                  }
                }

                  echo "</td>";

                  if(($first_day + $day) % 7 == 0 && $day != $last_day){
                    echo "</tr><tr>";
                  }
                }

                $last_cell = ($first_day + $last_day) % 7;

                if($last_cell != 0){
                  for($i = $last_cell; $i < 7; $i++){
                    echo "<td class='empty'></td>";
                  }
                }
              ?>
            </tr>
          </tbody>
        </table>

        <!-- 이번 달 공휴일 목록 -->
        <div class="holiday_list">
          <h4>이번 달 한국 공휴일</h4>

          <?php
            if(count($holidays) > 0){
          ?>
            <ul>
              <?php
                foreach($holidays as $date => $name){
                  $date_text = substr($date, 0, 4) . "-" . substr($date, 4, 2) . "-" . substr($date, 6, 2);
              ?>
                <li><?php echo h($date_text); ?> : <?php echo h($name); ?></li>
              <?php
                }
              ?>
            </ul>
          <?php
            }else{
          ?>
            <p>이번 달에 표시할 한국 공휴일이 없습니다.</p>
          <?php
            }
          ?>
        </div>

      </div>

    </div>
  </section>

</main>

<footer>
  <div class="container">
    <p>© Global Link Co., Ltd. All rights reserved.</p>
  </div>
</footer>
<script>
  // 수업스케줄 페이지 이동 후 스크롤 위치 유지
  window.addEventListener('load', function(){
    let savedScroll = sessionStorage.getItem('schedule_scroll_y');

    if(savedScroll !== null){
      window.scrollTo(0, parseInt(savedScroll));
      sessionStorage.removeItem('schedule_scroll_y');
    }
  });

  // 달력 이전/다음, 수업 목록 이전/다음 클릭 시 현재 스크롤 위치 저장
  document.addEventListener('click', function(e){
    let target = e.target;

    // a 태그 안의 span 등을 눌러도 a 태그를 찾기
    while(target && target.tagName !== 'A'){
      target = target.parentElement;
    }

    if(!target){
      return;
    }

    let href = target.getAttribute('href');

    if(!href){
      return;
    }

    // schedule.php 안에서 페이지나 달력 이동하는 링크일 때만 저장
    if(
      href.indexOf('schedule.php') !== -1 &&
      (
        href.indexOf('lesson_page=') !== -1 ||
        href.indexOf('lesson_start=') !== -1 ||
        href.indexOf('calendar_year=') !== -1 ||
        href.indexOf('calendar_month=') !== -1 ||
        href.indexOf('year=') !== -1 ||
        href.indexOf('month=') !== -1
      )
    ){
      sessionStorage.setItem('schedule_scroll_y', window.scrollY);
    }
  });
</script>
</body>
</html>