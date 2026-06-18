<?php
  session_start();

  include "../common/db.php";

  // 로그인 확인
  if(!isset($_SESSION['user_no'])){
    echo "
      <script>
        alert('로그인 후 이용해주세요.');
        location.href='../member_pg/login.php';
      </script>
    ";
    exit;
  }

  // 로그인한 회원 번호
  $member_no = (int)$_SESSION['user_no'];

  // course_confirm.php에서 넘어온 값 받기
  $course_name = $_POST['course_name'] ?? '';
  $course_period = $_POST['course_period'] ?? '';
  $lesson_count = $_POST['lesson_count'] ?? '';
  $start_date = $_POST['start_date'] ?? '';
  $first_time = $_POST['first_time'] ?? '';
  $second_time = $_POST['second_time'] ?? '';
  $referrer = $_POST['referrer'] ?? '';

  // 무통장입금 팝업에서 넘어온 값 받기
  $payment_method = $_POST['payment_method'] ?? '';
  $depositor_name = $_POST['depositor_name'] ?? '';
  $deposit_date = $_POST['deposit_date'] ?? '';

  // 필수값 확인
  if($course_name == '' || $course_period == '' || $lesson_count == '' || $start_date == '' || $first_time == '' || $second_time == ''){
    echo "
      <script>
        alert('수강신청 정보가 부족합니다.');
        location.href='./course_register.php';
      </script>
    ";
    exit;
  }

  // 현재는 무통장입금만 연결
  if($payment_method != 'bank'){
    echo "
      <script>
        alert('현재는 무통장입금만 신청 가능합니다.');
        history.back();
      </script>
    ";
    exit;
  }

  // 입금 정보 확인
  if($depositor_name == '' || $deposit_date == ''){
    echo "
      <script>
        alert('입금자명과 입금예정일을 입력해주세요.');
        history.back();
      </script>
    ";
    exit;
  }

  // DB에 저장할 값 정리
  $lesson_days = "상담 후 확정";
  $lesson_time = $first_time . " / " . $second_time;
  $total_period = $course_period . " / " . $lesson_count;

  // 아직 가격 계산 전이라 0원으로 저장
  $payment_amount = 0;

  // 상태값
  $payment_status = "입금대기";
  $lesson_status = "등록필요";
  $payment_method_name = "무통장입금";

  // SQL 특수문자 처리
  $course_name = mysqli_real_escape_string($db, $course_name);
  $start_date = mysqli_real_escape_string($db, $start_date);
  $lesson_days = mysqli_real_escape_string($db, $lesson_days);
  $lesson_time = mysqli_real_escape_string($db, $lesson_time);
  $total_period = mysqli_real_escape_string($db, $total_period);
  $payment_status = mysqli_real_escape_string($db, $payment_status);
  $lesson_status = mysqli_real_escape_string($db, $lesson_status);
  $payment_method_name = mysqli_real_escape_string($db, $payment_method_name);
  $depositor_name = mysqli_real_escape_string($db, $depositor_name);
  $deposit_date = mysqli_real_escape_string($db, $deposit_date);
  $referrer = mysqli_real_escape_string($db, $referrer);

  // 수강신청 정보 저장
  $sql = "
    INSERT INTO hk_payments (
      member_no,
      course_name,
      start_date,
      lesson_days,
      lesson_time,
      total_period,
      payment_status,
      payment_amount,
      lesson_status,
      payment_method,
      depositor_name,
      deposit_date,
      referrer
    ) VALUES (
      '$member_no',
      '$course_name',
      '$start_date',
      '$lesson_days',
      '$lesson_time',
      '$total_period',
      '$payment_status',
      '$payment_amount',
      '$lesson_status',
      '$payment_method_name',
      '$depositor_name',
      '$deposit_date',
      '$referrer'
    )
  ";

  $result = mysqli_query($db, $sql);

  if($result){

    // 완료 페이지에서 보여줄 값 저장
    $_SESSION['apply_course_name'] = $course_name;
    $_SESSION['apply_start_date'] = $start_date;
    $_SESSION['apply_lesson_time'] = $lesson_time;

    echo "
      <script>
        location.href='./course_complete.php';
      </script>
    ";

}else{

  $error = mysqli_error($db);

  echo "
    <script>
      alert('수강신청 저장에 실패했습니다. DB 오류: " . addslashes($error) . "');
      history.back();
    </script>
  ";

}

  mysqli_close($db);
?>