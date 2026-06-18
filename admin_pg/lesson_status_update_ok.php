<?php
  // 관리자 로그인 체크
  include "./admin_check.php";

  // 한글 깨짐 방지
  header("Content-Type:text/html; charset=utf-8");

  // DB 연결
  include "../common/db.php";

  // POST 값 받기
  $schedule_no = $_POST['schedule_no'] ?? '';
  $payment_no = $_POST['payment_no'] ?? '';
  $attendance_status = $_POST['attendance_status'] ?? '';

  $schedule_no = (int)$schedule_no;
  $payment_no = (int)$payment_no;

  // 허용할 수업상태 목록
  $status_list = ['예정', '출석', '결석', '홀드'];

  if($schedule_no < 1 || $payment_no < 1 || !in_array($attendance_status, $status_list)){
    echo "
      <script>
        alert('잘못된 접근입니다.');
        location.href='./admin.php?tab=lesson';
      </script>
    ";
    exit;
  }

  // SQL 특수문자 처리
  $attendance_status = mysqli_real_escape_string($db, $attendance_status);

  // 수업상태 수정
  $update_sql = "
    UPDATE hk_lesson_schedule
    SET attendance_status = '$attendance_status'
    WHERE no = '$schedule_no'
    AND payment_no = '$payment_no'
  ";

  $update_result = mysqli_query($db, $update_sql);

  if($update_result){
    echo "
      <script>
        alert('수업상태가 저장되었습니다.');
        location.href='./lesson_view.php?payment_no=$payment_no';
      </script>
    ";
  }else{
    echo "
      <script>
        alert('수업상태 저장에 실패했습니다.');
        history.back();
      </script>
    ";
  }

  mysqli_close($db);
?>