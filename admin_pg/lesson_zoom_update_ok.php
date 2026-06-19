<?php
  // 관리자 로그인 체크
  include "./admin_check.php";

  // 한글 깨짐 방지
  header("Content-Type:text/html; charset=utf-8");

  // DB 연결
  include "../common/db.php";

  // POST 값 받기
  $payment_no = $_POST['payment_no'] ?? '';
  $zoom_link = $_POST['zoom_link'] ?? '';

  // 숫자 처리
  $payment_no = (int)$payment_no;

  // 앞뒤 공백 제거
  $zoom_link = trim($zoom_link);

  // 잘못된 접근 체크
  if($payment_no < 1){
    echo "
      <script>
        alert('잘못된 접근입니다.');
        location.href='./admin.php?tab=lesson';
      </script>
    ";
    exit;
  }

  // SQL 특수문자 처리
  $zoom_link = mysqli_real_escape_string($db, $zoom_link);

  // 수업 입장 링크 저장
  $sql = "
    UPDATE hk_payments
    SET zoom_link = '$zoom_link'
    WHERE no = '$payment_no'
  ";

  $result = mysqli_query($db, $sql);

  if($result){
    echo "
      <script>
        alert('수업 입장 링크가 저장되었습니다.');
        location.href='./lesson_view.php?payment_no=$payment_no';
      </script>
    ";
  }else{
    echo "
      <script>
        alert('수업 입장 링크 저장에 실패했습니다.');
        history.back();
      </script>
    ";
  }

  mysqli_close($db);
?>