<?php
  // 관리자 로그인 체크
  include "./admin_check.php";

  // 한글 깨짐 방지
  header("Content-Type:text/html; charset=utf-8");

  // DB 연결
  include "../common/db.php";

  // 공휴일 API 인증키 불러오기
    $holiday_service_key = "";

    if(file_exists("../common/holiday_key.php")){
      include "../common/holiday_key.php";
    }

    // 공휴일 API 호출 함수
    function getHolidayInfo($year, $month_text, $service_key){
      $holiday_list = [];
      $message = "";

      if($service_key == ""){
        return [$holiday_list, "공공데이터포털 인증키가 없습니다."];
      }

      // 공공데이터포털 공휴일 정보 조회 API 주소
      $api_url = "http://apis.data.go.kr/B090041/openapi/service/SpcdeInfoService/getRestDeInfo";

      $request_url = $api_url
        . "?ServiceKey=" . $service_key
        . "&solYear=" . $year
        . "&solMonth=" . $month_text;

      if(!function_exists("curl_init")){
        return [$holiday_list, "서버에서 curl 기능을 사용할 수 없습니다."];
      }

      $ch = curl_init();

      curl_setopt($ch, CURLOPT_URL, $request_url);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($ch, CURLOPT_TIMEOUT, 10);

      $xml_data = curl_exec($ch);
      $curl_error = curl_error($ch);

      curl_close($ch);

      if($xml_data === false || $xml_data == ""){
        return [$holiday_list, "공휴일 API 호출에 실패했습니다. " . $curl_error];
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
  // 학생 아이디 가져오기
  $student_id = "";

  $member_sql = "
    SELECT user_id
    FROM hk_members
    WHERE no = '$member_no'
    LIMIT 1
  ";

  $member_result = mysqli_query($db, $member_sql);

  if($member_result && mysqli_num_rows($member_result) > 0){
    $member_row = mysqli_fetch_array($member_result, MYSQLI_ASSOC);
    $student_id = mysqli_real_escape_string($db, $member_row['user_id']);
  }

    // 생성된 수업횟수
    $insert_count = 0;

    // 월별 공휴일 저장 배열
    $holiday_cache = [];

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

      // 공휴일 확인용 날짜값
      $holiday_year = date('Y', $current_time);
      $holiday_month = date('m', $current_time);
      $holiday_key = $holiday_year . $holiday_month;
      $lesson_date_key = date('Ymd', $current_time);

      // 해당 월 공휴일 정보를 아직 안 가져왔으면 API로 가져오기
      if(!isset($holiday_cache[$holiday_key])){
        list($month_holidays, $holiday_message) = getHolidayInfo($holiday_year, $holiday_month, $holiday_service_key);
        $holiday_cache[$holiday_key] = $month_holidays;
      }

      // 공휴일이면 수업을 만들지 않고 건너뛰기
      if(isset($holiday_cache[$holiday_key][$lesson_date_key])){
        $current_time = strtotime("+1 day", $current_time);
        $loop_count++;
        continue;
      }         

      $lesson_day_db = mysqli_real_escape_string($db, $lesson_day);
      $lesson_date_db = mysqli_real_escape_string($db, $lesson_date);

      $insert_sql = "
        INSERT INTO hk_lesson_schedule
        (
          payment_no,
          member_no,
          student_id,
          teacher_name,
          lesson_date,
          lesson_day,
          lesson_time
        )
        VALUES
        (
          '$payment_no',
          '$member_no',
          '$student_id',
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

  // 수강기간/횟수에 따라 홀드 기본 횟수 계산
  function get_hold_default_count($total_period){

    // 1개월
    if(strpos($total_period, "1개월") !== false){
      if(strpos($total_period, "주5회") !== false || strpos($total_period, "주 5회") !== false || strpos($total_period, "20회") !== false){
        return 2;
      }else{
        return 1;
      }
    }

    // 3개월
    if(strpos($total_period, "3개월") !== false){
      if(strpos($total_period, "주5회") !== false || strpos($total_period, "주 5회") !== false || strpos($total_period, "60회") !== false){
        return 7;
      }else if(strpos($total_period, "주3회") !== false || strpos($total_period, "주 3회") !== false || strpos($total_period, "36회") !== false){
        return 4;
      }else if(strpos($total_period, "주2회") !== false || strpos($total_period, "주 2회") !== false || strpos($total_period, "24회") !== false){
        return 3;
      }
    }

    // 6개월
    if(strpos($total_period, "6개월") !== false){
      if(strpos($total_period, "주5회") !== false || strpos($total_period, "주 5회") !== false || strpos($total_period, "120회") !== false){
        return 15;
      }else if(strpos($total_period, "주3회") !== false || strpos($total_period, "주 3회") !== false || strpos($total_period, "72회") !== false){
        return 9;
      }else if(strpos($total_period, "주2회") !== false || strpos($total_period, "주 2회") !== false || strpos($total_period, "48회") !== false){
        return 6;
      }
    }

    // 혹시 위 조건에 안 맞으면 기본 1회
    return 1;
  }


  // 현재 결제정보의 total_period 가져오기
  $hold_sql = "
    SELECT total_period
    FROM hk_payments
    WHERE no='$payment_no'
  ";

  $hold_result = mysqli_query($db, $hold_sql);

  if($hold_result && mysqli_num_rows($hold_result) > 0){

    $hold_row = mysqli_fetch_array($hold_result);
    $default_hold_count = get_hold_default_count($hold_row['total_period']);

    // 홀드/홀드취소 기본 횟수 저장
    $hold_update_sql = "
      UPDATE hk_payments
      SET
        hold_limit='$default_hold_count',
        hold_cancel_limit='$default_hold_count'
      WHERE no='$payment_no'
    ";

    mysqli_query($db, $hold_update_sql);
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