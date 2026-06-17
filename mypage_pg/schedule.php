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

  // 현재는 DB가 없으므로 임시 수업 데이터 사용
  // 나중에는 DB에서 로그인한 회원의 수업 일정만 가져오게 됨
  $lessons = [
    [
      "date" => "2026.06.19",
      "weekday" => "금요일",
      "course" => "화상 한국어 - 일반과정",
      "time" => "09:00 ~ 09:50",
      "teacher" => "배정 예정",
      "status" => "예정수업"
    ],
    [
      "date" => "2026.06.22",
      "weekday" => "월요일",
      "course" => "화상 한국어 - 일반과정",
      "time" => "09:00 ~ 09:50",
      "teacher" => "배정 예정",
      "status" => "예정수업"
    ],
    [
      "date" => "2026.06.24",
      "weekday" => "수요일",
      "course" => "화상 한국어 - 일반과정",
      "time" => "09:00 ~ 09:50",
      "teacher" => "배정 예정",
      "status" => "예정수업"
    ],
    [
      "date" => "2026.06.26",
      "weekday" => "금요일",
      "course" => "화상 한국어 - 일반과정",
      "time" => "09:00 ~ 09:50",
      "teacher" => "배정 예정",
      "status" => "예정수업"
    ],
    [
      "date" => "2026.06.29",
      "weekday" => "월요일",
      "course" => "화상 한국어 - 일반과정",
      "time" => "09:00 ~ 09:50",
      "teacher" => "배정 예정",
      "status" => "예정수업"
    ],
    [
      "date" => "2026.07.01",
      "weekday" => "수요일",
      "course" => "화상 한국어 - 일반과정",
      "time" => "09:00 ~ 09:50",
      "teacher" => "배정 예정",
      "status" => "예정수업"
    ]
  ];

  // 수업 목록 페이지네이션
  $lesson_page = $_GET['lesson_page'] ?? 1;
  $lesson_page = (int)$lesson_page;

  if($lesson_page < 1){
    $lesson_page = 1;
  }

  $per_page = 5;
  $total_lessons = count($lessons);
  $total_pages = ceil($total_lessons / $per_page);

  if($lesson_page > $total_pages){
    $lesson_page = $total_pages;
  }

  $start_index = ($lesson_page - 1) * $per_page;
  $current_lessons = array_slice($lessons, $start_index, $per_page);
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
        <li><a href="#">Course</a></li>
        <li><a href="#">Teacher</a></li>
        <li><a href="#">Price</a></li>
        <li><a href="../contact_pg/contact.php">Contact</a></li>
        <li><a href="../board_pg/board.php" class="board">Board</a></li>

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
              <div class="lesson_status_tab">
                <?php echo h($lesson['status']); ?>
              </div>

              <!-- 가운데 수업 정보 -->
              <div class="lesson_main_info">

                <div class="lesson_date_line">
                  <?php echo h($lesson['date']); ?> (<?php echo h($lesson['weekday']); ?>)
                </div>

                <div class="lesson_course_line">
                  <span><?php echo h($lesson['course']); ?></span>
                  <strong><?php echo h($lesson['time']); ?></strong>
                </div>

                <div class="lesson_teacher_line">
                  <span>강사</span>
                  <strong><?php echo h($lesson['teacher']); ?></strong>
                </div>

              </div>

              <!-- 오른쪽 수업 입장 -->
              <div class="lesson_enter_area">
                <button type="button" class="enter_circle_btn" onclick="alert('수업 5분전부터 입장이 가능합니다.');">
                  수업<br>입장
                </button>
              </div>

              <!-- 하단 버튼 -->
              <div class="lesson_bottom_buttons">
                <button type="button" disabled>교재보기</button>
                <button type="button" disabled>수업 홀드 신청</button>
                <button type="button" disabled>Teacher's 피드백</button>
              </div>

            </div>

          <?php
            }
          ?>

          <!-- 예정수업 페이지네이션 -->
          <div class="lesson_pagination">

            <?php
              if($lesson_page > 1){
            ?>
              <a href="./schedule.php?year=<?php echo $year; ?>&month=<?php echo $month; ?>&lesson_page=<?php echo $lesson_page - 1; ?>">이전</a>
            <?php
              }else{
            ?>
              <span class="page_disabled">이전</span>
            <?php
              }
            ?>

            <span class="page_info">
              <?php echo $lesson_page; ?> / <?php echo $total_pages; ?>
            </span>

            <?php
              if($lesson_page < $total_pages){
            ?>
              <a href="./schedule.php?year=<?php echo $year; ?>&month=<?php echo $month; ?>&lesson_page=<?php echo $lesson_page + 1; ?>">다음</a>
            <?php
              }else{
            ?>
              <span class="page_disabled">다음</span>
            <?php
              }
            ?>

          </div>

        </div>

        <!-- 공휴일 안내 -->
        <div class="holiday_notice">
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

                  if(isset($holidays[$date_key])){
                    echo "<span class='holiday_name'>" . h($holidays[$date_key]) . "</span>";
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

</body>
</html>