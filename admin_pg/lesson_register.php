<?php
  // 관리자 로그인 체크
  include "./admin_check.php";

  // 한글 깨짐 방지
  header("Content-Type:text/html; charset=utf-8");

  // DB 연결
  include "../common/db.php";

  // 화면 출력용 특수문자 처리 함수
  function h($str){
    return htmlspecialchars((string)$str, ENT_QUOTES, "UTF-8");
  }

  // 총 수업횟수 계산 함수
  // 예: 1개월 / 주 2회 => 1 * 4 * 2 = 8회
  function get_total_lessons($total_period){
    $month = 1;
    $week_count = 2;

    if(preg_match('/(\d+)개월/', $total_period, $match_month)){
      $month = (int)$match_month[1];
    }

    if(preg_match('/주\s*(\d+)회/', $total_period, $match_week)){
      $week_count = (int)$match_week[1];
    }

    return $month * 4 * $week_count;
  }

  // 주 몇 회 수업인지 계산하는 함수
  function get_weekly_count($total_period){
    $week_count = 2;

    if(preg_match('/주\s*(\d+)회/', $total_period, $match_week)){
      $week_count = (int)$match_week[1];
    }

    return $week_count;
  }

  // GET으로 결제번호 받기
  $payment_no = $_GET['payment_no'] ?? '';
  $payment_no = (int)$payment_no;

  if($payment_no < 1){
    echo "
      <script>
        alert('잘못된 접근입니다.');
        location.href='./admin.php?tab=payment';
      </script>
    ";
    exit;
  }

  // 결제정보 + 회원정보 가져오기
  $payment_sql = "
    SELECT
      p.*,
      m.user_id,
      m.user_name,
      m.phone,
      m.email
    FROM hk_payments p
    LEFT JOIN hk_members m
    ON p.member_no = m.no
    WHERE p.no = '$payment_no'
  ";

  $payment_result = mysqli_query($db, $payment_sql);
  $payment = mysqli_fetch_array($payment_result, MYSQLI_ASSOC);

  if(!$payment){
    echo "
      <script>
        alert('수강신청 정보를 찾을 수 없습니다.');
        location.href='./admin.php?tab=payment';
      </script>
    ";
    exit;
  }

  // 총 수업횟수와 주 수업횟수 계산
  $total_lessons = get_total_lessons($payment['total_period']);
  $weekly_count = get_weekly_count($payment['total_period']);

  // 지금은 강사관리 백엔드가 없으므로 임시 강사 1명 사용
  $teacher_name = "이연";

        // 이연 강사의 현재 진행중인 수업 내역 가져오기
        // 같은 신청건은 한 줄로 묶어서 보여줌
        $mapping_sql = "
        SELECT
            s.payment_no,
            s.member_no,
            s.teacher_name,
            m.user_name,
            m.user_id,
            p.course_name,
            GROUP_CONCAT(DISTINCT s.lesson_day ORDER BY FIELD(s.lesson_day, '월', '화', '수', '목', '금', '토', '일') SEPARATOR ', ') AS lesson_days,
            GROUP_CONCAT(DISTINCT s.lesson_time ORDER BY s.lesson_time SEPARATOR ', ') AS lesson_times,
            MIN(s.lesson_date) AS lesson_start_date,
            MAX(s.lesson_date) AS lesson_end_date
        FROM hk_lesson_schedule s
        LEFT JOIN hk_members m
        ON s.member_no = m.no
        LEFT JOIN hk_payments p
        ON s.payment_no = p.no
        WHERE s.teacher_name = '$teacher_name'
        AND p.lesson_status = '수강중'
        GROUP BY
            s.payment_no,
            s.member_no,
            s.teacher_name,
            m.user_name,
            m.user_id,
            p.course_name
        ORDER BY lesson_end_date DESC
        LIMIT 10
        ";

$mapping_result = mysqli_query($db, $mapping_sql);
?>

<!DOCTYPE html>
<html lang="ko">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <title>Hello Korean - 수강등록</title>

  <link rel="stylesheet" href="./admin.css">
</head>

<body>

<header class="admin_header">
  <div class="admin_container">

    <h1>Hello Korean Admin</h1>

    <div class="admin_top_btn">
      <span><?php echo h($_SESSION['admin_id']); ?> 관리자님</span>
      <a href="./admin.php?tab=payment">결제회원으로 돌아가기</a>
      <a href="./admin_logout.php">관리자 로그아웃</a>
    </div>

  </div>
</header>

<main class="admin_main">
  <div class="admin_container">

    <section class="admin_title">
      <h2>수강등록</h2>
      <p>결제회원의 신청 정보를 확인한 뒤, 수업요일 / 수업시간 / 강사를 선택합니다.</p>
    </section>

    <!-- 신청 회원 정보 -->
    <section class="lesson_register_box">

      <div class="lesson_register_title">
        <h3>신청 회원 정보</h3>
      </div>

      <div class="lesson_info_grid">
        <div>
          <strong>아이디</strong>
          <span><?php echo h($payment['user_id']); ?></span>
        </div>

        <div>
          <strong>이름</strong>
          <span><?php echo h($payment['user_name']); ?></span>
        </div>

        <div>
          <strong>Course</strong>
          <span><?php echo h($payment['course_name']); ?></span>
        </div>

        <div>
          <strong>희망 시작일</strong>
          <span id="start_date_text"><?php echo h($payment['start_date']); ?></span>
        </div>

        <div>
          <strong>희망 수업시간</strong>
          <span><?php echo h($payment['lesson_time']); ?></span>
        </div>

        <div>
          <strong>총 기간</strong>
          <span><?php echo h($payment['total_period']); ?></span>
        </div>

        <div>
          <strong>총 수업횟수</strong>
          <span><?php echo h($total_lessons); ?>회</span>
        </div>

        <div>
          <strong>결제상태</strong>
          <span><?php echo h($payment['payment_status']); ?></span>
        </div>
      </div>

    </section>

    <!-- 수강등록 form -->
    <form action="./lesson_register_ok.php" method="post" onsubmit="return lessonRegisterCheck();">

      <input type="hidden" name="payment_no" value="<?php echo h($payment['no']); ?>">
      <input type="hidden" name="total_lessons" id="total_lessons" value="<?php echo h($total_lessons); ?>">
      <input type="hidden" name="weekly_count" id="weekly_count" value="<?php echo h($weekly_count); ?>">
      <input type="hidden" name="start_date" id="start_date" value="<?php echo h($payment['start_date']); ?>">
      <input type="hidden" name="teacher_name" id="teacher_name" value="">

      <!-- 수업요일 / 수업시간 선택 -->
      <section class="lesson_register_box">

        <div class="lesson_register_title">
          <h3>수업요일 / 수업시간 선택</h3>
          <p>이 신청건은 주 <?php echo h($weekly_count); ?>회 수업입니다. 수업요일을 <?php echo h($weekly_count); ?>개 선택해주세요.</p>
        </div>

        <div class="lesson_form_row">
          <strong>수업요일</strong>

          <div class="lesson_day_group">
            <label><input type="checkbox" name="lesson_days[]" value="월"> 월</label>
            <label><input type="checkbox" name="lesson_days[]" value="화"> 화</label>
            <label><input type="checkbox" name="lesson_days[]" value="수"> 수</label>
            <label><input type="checkbox" name="lesson_days[]" value="목"> 목</label>
            <label><input type="checkbox" name="lesson_days[]" value="금"> 금</label>
            <label><input type="checkbox" name="lesson_days[]" value="토"> 토</label>
            <label><input type="checkbox" name="lesson_days[]" value="일"> 일</label>
          </div>
        </div>

        <div class="lesson_form_row">
          <strong>학습시간</strong>

          <select name="lesson_time" id="lesson_time" class="lesson_time_select">
            <option value="">수업시간 선택</option>

            <?php
              // 06:00부터 24:00까지 표시
              for($hour = 6; $hour <= 24; $hour++){

                if($hour < 10){
                  $time_text = "0" . $hour . ":00";
                }else{
                  $time_text = $hour . ":00";
                }

                // 회원이 희망한 시간에 포함되어 있으면 표시
                $hope_text = "";

                if(strpos($payment['lesson_time'], $time_text) !== false){
                  $hope_text = " (희망)";
                }
            ?>

              <option value="<?php echo h($time_text); ?>">
                <?php echo h($time_text . $hope_text); ?>
              </option>

            <?php
              }
            ?>
          </select>

          <span class="hope_time_text">희망 시간: <?php echo h($payment['lesson_time']); ?></span>
        </div>

        <div class="lesson_form_row">
          <strong>강사검색</strong>

          <button type="button" class="admin_small_btn" onclick="showTeacherArea();">
            강사검색
          </button>

          <span id="selected_teacher_text" class="selected_teacher_text">선택된 강사 없음</span>
        </div>

      </section>

      <!-- 강사 검색 결과 -->
      <section id="teacher_area" class="lesson_register_box teacher_area">

        <div class="lesson_register_title">
          <h3>강사 검색 결과</h3>
          <p>현재는 강사관리 백엔드가 없으므로 임시 강사 1명만 표시합니다.</p>
        </div>

        <div class="admin_table_area">
          <table class="admin_table teacher_table">
            <caption>강사 검색 결과</caption>

            <thead>
              <tr>
                <th>번호</th>
                <th>강사명</th>
                <th>최근 강의내역</th>
                <th>선택</th>
              </tr>
            </thead>

            <tbody>
              <tr>
                <td>1</td>

                <td>
                  <strong><?php echo h($teacher_name); ?></strong>
                </td>

                <td class="text_left">
                    <p>* 현재 진행중인 수업 내역입니다.</p>

                    <?php
                    if($mapping_result && mysqli_num_rows($mapping_result) > 0){
                        while($mapping = mysqli_fetch_array($mapping_result, MYSQLI_ASSOC)){
                    ?>

                    <p>
                        -
                        <strong><?php echo h($mapping['user_name']); ?></strong>
                        (<?php echo h($mapping['user_id']); ?>),
                        <?php echo h($mapping['lesson_days']); ?>,
                        <?php echo h($mapping['lesson_times']); ?>,
                        수업종료일:
                        <?php echo h($mapping['lesson_end_date']); ?>
                    </p>

                    <?php
                        }
                    }else{
                    ?>

                    <p>현재 진행중인 수업 내역이 없습니다.</p>

                    <?php
                    }
                    ?>
                </td>

                <td>
                  <button type="button" class="admin_small_btn" onclick="selectTeacher('<?php echo h($teacher_name); ?>');">
                    선택
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

      </section>

      <!-- 수업일정 미리보기 -->
      <section class="lesson_register_box">

        <div class="lesson_register_title">
          <h3>수업일정 미리보기</h3>
          <p>선택한 요일 기준으로 희망 시작일부터 총 <?php echo h($total_lessons); ?>회 수업일이 생성됩니다.</p>
        </div>

        <div class="lesson_preview_btn_area">
          <button type="button" class="admin_small_btn" onclick="makePreview();">
            수업일정 미리보기
          </button>
        </div>

        <div class="admin_table_area">
          <table class="admin_table preview_table">
            <caption>수업일정 미리보기</caption>

            <thead>
              <tr>
                <th>회차</th>
                <th>수업일</th>
                <th>요일</th>
                <th>수업시간</th>
              </tr>
            </thead>

            <tbody id="preview_body">
              <tr>
                <td colspan="4">요일과 시간을 선택한 뒤 미리보기를 눌러주세요.</td>
              </tr>
            </tbody>
          </table>
        </div>

      </section>

      <div class="lesson_submit_area">
        <a href="./admin.php?tab=payment" class="admin_back_btn">목록으로</a>
        <button type="submit" class="admin_submit_btn">수강등록 완료</button>
      </div>

    </form>

  </div>
</main>

<script>
  // 강사검색 결과 영역 보여주기
  function showTeacherArea(){
    let teacherArea = document.querySelector('#teacher_area');
    teacherArea.style.display = 'block';
  }

  // 강사 선택
  function selectTeacher(name){
    document.querySelector('#teacher_name').value = name;
    document.querySelector('#selected_teacher_text').innerText = name + ' 강사 선택됨';

    makePreview();
  }

  // 선택한 수업요일 가져오기
  function getCheckedDays(){
    let checkedDays = [];
    let dayInputs = document.querySelectorAll('input[name="lesson_days[]"]:checked');

    dayInputs.forEach(function(input){
      checkedDays.push(input.value);
    });

    return checkedDays;
  }

  // 날짜를 YYYY-MM-DD 형식으로 바꾸기
  function formatDate(date){
    let year = date.getFullYear();
    let month = date.getMonth() + 1;
    let day = date.getDate();

    if(month < 10){
      month = '0' + month;
    }

    if(day < 10){
      day = '0' + day;
    }

    return year + '-' + month + '-' + day;
  }

  // 수업일정 미리보기 만들기
  function makePreview(){
    let startDateValue = document.querySelector('#start_date').value;
    let totalLessons = parseInt(document.querySelector('#total_lessons').value);
    let lessonTime = document.querySelector('#lesson_time').value;
    let checkedDays = getCheckedDays();

    let previewBody = document.querySelector('#preview_body');

    if(checkedDays.length < 1 || lessonTime == ''){
      previewBody.innerHTML = '<tr><td colspan="4">요일과 시간을 먼저 선택해주세요.</td></tr>';
      return;
    }

    let dayNames = ['일', '월', '화', '수', '목', '금', '토'];

    let startDate = new Date(startDateValue);
    let previewList = [];

    let count = 0;

    // 안전장치: 최대 500일까지만 반복
    while(previewList.length < totalLessons && count < 500){
      let dayName = dayNames[startDate.getDay()];

      if(checkedDays.indexOf(dayName) !== -1){
        previewList.push({
          date: formatDate(startDate),
          day: dayName,
          time: lessonTime
        });
      }

      startDate.setDate(startDate.getDate() + 1);
      count++;
    }

    let html = '';

    for(let i = 0; i < previewList.length; i++){
      html += '<tr>';
      html += '<td>' + (i + 1) + '회차</td>';
      html += '<td>' + previewList[i].date + '</td>';
      html += '<td>' + previewList[i].day + '</td>';
      html += '<td>' + previewList[i].time + '</td>';
      html += '</tr>';
    }

    previewBody.innerHTML = html;
  }

  // 수강등록 완료 버튼 누를 때 검사
  function lessonRegisterCheck(){
    let weeklyCount = parseInt(document.querySelector('#weekly_count').value);
    let checkedDays = getCheckedDays();
    let lessonTime = document.querySelector('#lesson_time').value;
    let teacherName = document.querySelector('#teacher_name').value;

    if(checkedDays.length != weeklyCount){
      alert('이 신청건은 주 ' + weeklyCount + '회 수업입니다. 수업요일을 ' + weeklyCount + '개 선택해주세요.');
      return false;
    }

    if(lessonTime == ''){
      alert('수업시간을 선택해주세요.');
      return false;
    }

    if(teacherName == ''){
      alert('강사를 선택해주세요.');
      return false;
    }

    return confirm('수강등록을 완료하시겠습니까?');
  }
</script>

</body>
</html>