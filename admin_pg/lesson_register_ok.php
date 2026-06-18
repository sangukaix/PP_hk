<?php
  // 관리자 로그인 체크
  include "./admin_check.php";

  // 한글 깨짐 방지
  header("Content-Type:text/html; charset=utf-8");

  // DB 연결
  include "../common/db.php";

  // 총 수업횟수 계산 함수
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

  // POST 값 받기
  $payment_no = $_POST['payment_no'] ?? '';
  $lesson_days = $_POST['lesson_days'] ?? [];
  $lesson_time = $_POST['lesson_time'] ?? '';
  $teacher_name = $_POST['teacher_name'] ?? '';

  $payment_no = (int)$payment_no;

  if($payment_no < 1 || count($lesson_days) < 1 || $lesson_time == '' || $teacher_name == ''){
    echo "
      <script>
        alert('필수 정보가 누락되었습니다.');
        history.back();
      </script>
    ";
    exit;
  }

  // 결제정보 가져오기
  $payment_sql = "
    SELECT *
    FROM hk_payments
    WHERE no = '$payment_no'
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

  // 이미 수강등록된 건인지 확인
  $check_sql = "
    SELECT COUNT(*) AS total
    FROM hk_lesson_schedule
    WHERE payment_no = '$payment_no'
  ";

  $check_result = mysqli_query($db, $check_sql);
  $check_row = mysqli_fetch_array($check_result, MYSQLI_ASSOC);

  if($check_row['total'] > 0){
    echo "
      <script>
        alert('이미 수강등록된 신청건입니다.');
        location.href='./admin.php?tab=lesson';
      </script>
    ";
    exit;
  }

  // 총 수업횟수와 주 수업횟수 계산
  $total_lessons = get_total_lessons($payment['total_period']);
  $weekly_count = get_weekly_count($payment['total_period']);

  // 선택한 요일 개수가 주 수업횟수와 맞는지 확인
  if(count($lesson_days) != $weekly_count){
    echo "
      <script>
        alert('수업요일 개수가 맞지 않습니다.');
        history.back();
      </script>
    ";
    exit;
  }

  // SQL에 넣기 전 특수문자 처리
  $teacher_name = mysqli_real_escape_string($db, $teacher_name);
  $lesson_time = mysqli_real_escape_string($db, $lesson_time);

  // 요일 배열
  $day_names = ['일', '월', '화', '수', '목', '금', '토'];

  // 시작일
  $start_date = $payment['start_date'];
  $current_time = strtotime($start_date);

  // 회원번호
  $member_no = (int)$payment['member_no'];

  // 생성된 수업횟수
  $insert_count = 0;

  // 반복 안전장치
  $loop_count = 0;

  // 총 수업횟수만큼 일정 생성
  while($insert_count < $total_lessons && $loop_count < 500){

    // 현재 날짜의 요일 번호
    // 0: 일, 1: 월, 2: 화 ...
    $day_num = date('w', $current_time);

    // 현재 날짜의 한글 요일
    $lesson_day = $day_names[$day_num];

    // 선택한 요일에 포함되어 있으면 수업일정 저장
    if(in_array($lesson_day, $lesson_days)){

      $lesson_date = date('Y-m-d', $current_time);

      $lesson_day_db = mysqli_real_escape_string($db, $lesson_day);
      $lesson_date_db = mysqli_real_escape_string($db, $lesson_date);

      $insert_sql = "
        INSERT INTO hk_lesson_schedule
        (
          payment_no,
          member_no,
          teacher_name,
          lesson_date,
          lesson_day,
          lesson_time
        )
        VALUES
        (
          '$payment_no',
          '$member_no',
          '$teacher_name',
          '$lesson_date_db',
          '$lesson_day_db',
          '$lesson_time'
        )
      ";

      $insert_result = mysqli_query($db, $insert_sql);

      if(!$insert_result){
        echo "
          <script>
            alert('수업일정 저장 중 오류가 발생했습니다.');
            history.back();
          </script>
        ";
        exit;
      }

      $insert_count++;
    }

    // 다음 날짜로 이동
    $current_time = strtotime("+1 day", $current_time);
    $loop_count++;
  }

  // 수업일정이 제대로 생성되지 않았을 때
  if($insert_count < $total_lessons){
    echo "
      <script>
        alert('수업일정 생성에 실패했습니다.');
        history.back();
      </script>
    ";
    exit;
  }

  // 결제회원의 수강상태를 수강중으로 변경
  $update_sql = "
    UPDATE hk_payments
    SET lesson_status = '수강중'
    WHERE no = '$payment_no'
  ";

  $update_result = mysqli_query($db, $update_sql);

  if($update_result){
    echo "
      <script>
        alert('수강등록이 완료되었습니다.');
        location.href='./admin.php?tab=lesson';
      </script>
    ";
  }else{
    echo "
      <script>
        alert('수강상태 변경 중 오류가 발생했습니다.');
        history.back();
      </script>
    ";
  }

  mysqli_close($db);
?>