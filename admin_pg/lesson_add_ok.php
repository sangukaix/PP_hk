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
  function getHolidayInfo($year, $month, $holiday_service_key){

    $holiday_list = [];
    $message = "";

    if($holiday_service_key == ""){
      return [$holiday_list, "공공데이터포털 인증키가 없습니다."];
    }

    // 공공데이터포털 공휴일 정보 조회 API 주소
    $api_url = "http://apis.data.go.kr/B090041/openapi/service/SpcdeInfoService/getRestDeInfo";

    $query = http_build_query([
      "serviceKey" => $holiday_service_key,
      "solYear" => $year,
      "solMonth" => $month,
      "numOfRows" => 50
    ]);

    $request_url = $api_url . "?" . $query;

    if(!function_exists("curl_init")){
      return [$holiday_list, "서버에서 curl 기능을 사용할 수 없습니다."];
    }

    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $request_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);

    $response = curl_exec($ch);
    $curl_error = curl_error($ch);

    curl_close($ch);

    if($response === false || $response == ""){
      return [$holiday_list, "공휴일 API 호출에 실패했습니다. " . $curl_error];
    }

    $xml = simplexml_load_string($response);

    if($xml === false){
      return [$holiday_list, "공휴일 API 응답을 읽을 수 없습니다."];
    }

    if(isset($xml->body->items->item)){
      foreach($xml->body->items->item as $item){

        $dateName = (string)$item->dateName;
        $locdate = (string)$item->locdate;
        $isHoliday = (string)$item->isHoliday;

        if($isHoliday == "Y"){
          $holiday_list[$locdate] = $dateName;
        }
      }
    }

    return [$holiday_list, $message];
  }

  // POST 값 받기
  $payment_no = $_POST['payment_no'] ?? '';
  $add_type = $_POST['add_type'] ?? 'auto';
  $custom_date = $_POST['custom_date'] ?? '';
  $custom_time = $_POST['custom_time'] ?? '';

  $payment_no = (int)$payment_no;

  if($payment_no < 1){
    echo "
      <script>
        alert('잘못된 접근입니다.');
        location.href='./admin.php?tab=lesson';
      </script>
    ";
    exit;
  }

  // 결제/회원 정보 가져오기
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
        alert('수강생 정보를 찾을 수 없습니다.');
        location.href='./admin.php?tab=lesson';
      </script>
    ";
    exit;
  }

  // 기존 수업 중 가장 마지막 수업 가져오기
  $last_sql = "
    SELECT *
    FROM hk_lesson_schedule
    WHERE payment_no = '$payment_no'
    ORDER BY lesson_date DESC, no DESC
    LIMIT 1
  ";

  $last_result = mysqli_query($db, $last_sql);
  $last = mysqli_fetch_array($last_result, MYSQLI_ASSOC);

  if(!$last){
    echo "
      <script>
        alert('기존 수업일정이 없습니다. 먼저 수강등록을 진행해주세요.');
        location.href='./admin.php?tab=lesson';
      </script>
    ";
    exit;
  }

  // 기존 수업 요일 패턴 가져오기
  $day_sql = "
    SELECT DISTINCT lesson_day
    FROM hk_lesson_schedule
    WHERE payment_no = '$payment_no'
    ORDER BY FIELD(lesson_day, '월', '화', '수', '목', '금', '토', '일')
  ";

  $day_result = mysqli_query($db, $day_sql);

  $lesson_days = [];

  while($day_row = mysqli_fetch_array($day_result, MYSQLI_ASSOC)){
    $lesson_days[] = $day_row['lesson_day'];
  }

  if(count($lesson_days) < 1){
    echo "
      <script>
        alert('수업 요일 정보를 찾을 수 없습니다.');
        history.back();
      </script>
    ";
    exit;
  }

  // 요일 배열
  $day_names = ['일', '월', '화', '수', '목', '금', '토'];

  // 기본값
  $member_no = (int)$payment['member_no'];
  $teacher_name = $last['teacher_name'];
  $lesson_time = $last['lesson_time'];
  $lesson_date = '';
  $lesson_day = '';

  // 자동 추가
  // 마지막 수업 다음날부터 시작해서 기존 요일 패턴에 맞는 첫 날짜를 찾음
  // 단, 공휴일이면 건너뛰고 다음 수업일을 찾음
  if($add_type == 'auto'){

    $current_time = strtotime("+1 day", strtotime($last['lesson_date']));
    $loop_count = 0;

    // 월별 공휴일 저장 배열
    $holiday_cache = [];

    while($loop_count < 90){

      $day_num = date('w', $current_time);
      $day_text = $day_names[$day_num];

      if(in_array($day_text, $lesson_days)){

        $check_date = date('Y-m-d', $current_time);
        $check_date_key = date('Ymd', $current_time);

        $holiday_year = date('Y', $current_time);
        $holiday_month = date('m', $current_time);
        $holiday_key = $holiday_year . $holiday_month;

        // 해당 월 공휴일 정보를 아직 안 가져왔으면 API로 가져오기
        if(!isset($holiday_cache[$holiday_key])){
          list($month_holidays, $holiday_message) = getHolidayInfo($holiday_year, $holiday_month, $holiday_service_key);
          $holiday_cache[$holiday_key] = $month_holidays;
        }

        // 공휴일이면 수업을 추가하지 않고 다음 날짜로 넘어감
        if(!isset($holiday_cache[$holiday_key][$check_date_key])){
          $lesson_date = $check_date;
          $lesson_day = $day_text;
          break;
        }
      }

      $current_time = strtotime("+1 day", $current_time);
      $loop_count++;
    }

    if($lesson_date == ''){
      echo "
        <script>
          alert('자동 추가할 수업일을 찾지 못했습니다.');
          history.back();
        </script>
      ";
      exit;
    }
  }

  // 직접 추가
  // 관리자가 직접 선택한 날짜는 공휴일이어도 강제로 추가 가능
  else if($add_type == 'custom'){

    if($custom_date == '' || $custom_time == ''){
      echo "
        <script>
          alert('직접 추가할 날짜와 시간을 선택해주세요.');
          history.back();
        </script>
      ";
      exit;
    }

    $date_time = strtotime($custom_date);

    if($date_time === false){
      echo "
        <script>
          alert('날짜 형식이 올바르지 않습니다.');
          history.back();
        </script>
      ";
      exit;
    }

    $lesson_date = date('Y-m-d', $date_time);
    $lesson_day = $day_names[date('w', $date_time)];
    $lesson_time = $custom_time;
  }

  else{
    echo "
      <script>
        alert('잘못된 추가 방식입니다.');
        history.back();
      </script>
    ";
    exit;
  }

  // SQL 특수문자 처리
  $teacher_name = mysqli_real_escape_string($db, $teacher_name);
  $lesson_date = mysqli_real_escape_string($db, $lesson_date);
  $lesson_day = mysqli_real_escape_string($db, $lesson_day);
  $lesson_time = mysqli_real_escape_string($db, $lesson_time);

  // 같은 날짜/시간에 이미 등록된 수업이 있는지 확인
  $duplicate_sql = "
    SELECT COUNT(*) AS total
    FROM hk_lesson_schedule
    WHERE payment_no = '$payment_no'
    AND lesson_date = '$lesson_date'
    AND lesson_time = '$lesson_time'
  ";

  $duplicate_result = mysqli_query($db, $duplicate_sql);
  $duplicate_row = mysqli_fetch_array($duplicate_result, MYSQLI_ASSOC);

  if($duplicate_row['total'] > 0){
    echo "
      <script>
        alert('이미 같은 날짜와 시간에 등록된 수업이 있습니다.');
        history.back();
      </script>
    ";
    exit;
  }

  // 수업 1회 추가
  $insert_sql = "
    INSERT INTO hk_lesson_schedule
    (
      payment_no,
      member_no,
      teacher_name,
      lesson_date,
      lesson_day,
      lesson_time,
      attendance_status
    )
    VALUES
    (
      '$payment_no',
      '$member_no',
      '$teacher_name',
      '$lesson_date',
      '$lesson_day',
      '$lesson_time',
      '예정'
    )
  ";

  $insert_result = mysqli_query($db, $insert_sql);

  if($insert_result){
    echo "
      <script>
        alert('수업이 1회 추가되었습니다.');
        location.href='./lesson_view.php?payment_no=$payment_no';
      </script>
    ";
  }else{
    echo "
      <script>
        alert('수업 추가에 실패했습니다.');
        history.back();
      </script>
    ";
  }

  mysqli_close($db);
?>