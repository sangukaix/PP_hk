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

  $schedule_no = (int)$schedule_no;
  $payment_no = (int)$payment_no;

  if($schedule_no < 1 || $payment_no < 1){
    echo "
      <script>
        alert('잘못된 접근입니다.');
        location.href='./admin.php?tab=lesson';
      </script>
    ";
    exit;
  }

  // 수업일정 삭제
  $delete_sql = "
    DELETE FROM hk_lesson_schedule
    WHERE no = '$schedule_no'
    AND payment_no = '$payment_no'
  ";

  $delete_result = mysqli_query($db, $delete_sql);

  if(!$delete_result){
    echo "
      <script>
        alert('수업일정 삭제에 실패했습니다.');
        history.back();
      </script>
    ";
    exit;
  }

  // 삭제 후 남은 수업일정이 있는지 확인
  $count_sql = "
    SELECT COUNT(*) AS total
    FROM hk_lesson_schedule
    WHERE payment_no = '$payment_no'
  ";

  $count_result = mysqli_query($db, $count_sql);
  $count_row = mysqli_fetch_array($count_result, MYSQLI_ASSOC);

  // 수업일정이 하나도 없으면 결제회원 상태를 다시 등록필요로 변경
  if($count_row['total'] < 1){
    $update_sql = "
      UPDATE hk_payments
      SET lesson_status = '등록필요'
      WHERE no = '$payment_no'
    ";

    mysqli_query($db, $update_sql);

    echo "
      <script>
        alert('수업일정이 모두 삭제되어 등록필요 상태로 변경되었습니다.');
        location.href='./admin.php?tab=payment';
      </script>
    ";
    exit;
  }

  echo "
    <script>
      alert('수업일정이 삭제되었습니다.');
      location.href='./lesson_view.php?payment_no=$payment_no';
    </script>
  ";

  mysqli_close($db);
?>