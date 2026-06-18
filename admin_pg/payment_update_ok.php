<?php
  // 관리자 로그인 체크
  include "./admin_check.php";

  // 한글 깨짐 방지
  header("Content-Type:text/html; charset=utf-8");

  // DB 연결
  include "../common/db.php";

  // POST로 넘어온 값 받기
  $no = $_POST['no'] ?? '';
  $payment_status = $_POST['payment_status'] ?? '';
  $payment_amount = $_POST['payment_amount'] ?? '0';

  // 번호는 숫자로 변환
  $no = (int)$no;

  // 결제액에 콤마가 들어와도 저장되도록 콤마 제거
  $payment_amount = str_replace(',', '', $payment_amount);
  $payment_amount = (int)$payment_amount;

  // 결제액이 음수로 들어오면 0으로 처리
  if($payment_amount < 0){
    $payment_amount = 0;
  }

  // 결제상태는 정해진 값만 허용
  $status_list = ['입금대기', '입금완료', '취소'];

  if($no < 1 || !in_array($payment_status, $status_list)){
    echo "
      <script>
        alert('잘못된 접근입니다.');
        location.href='./admin.php?tab=payment';
      </script>
    ";
    exit;
  }

  // SQL 특수문자 처리
  $payment_status = mysqli_real_escape_string($db, $payment_status);

    // 이미 입금완료된 결제건은 다시 수정하지 못하게 막기
    $check_sql = "
      SELECT payment_status
      FROM hk_payments
      WHERE no = '$no'
    ";

    $check_result = mysqli_query($db, $check_sql);
    $check = mysqli_fetch_array($check_result, MYSQLI_ASSOC);

    if($check && $check['payment_status'] == '입금완료'){
      echo "
        <script>
          alert('이미 입금완료된 결제건은 수정할 수 없습니다.');
          location.href='./admin.php?tab=payment';
        </script>
      ";
      exit;
    }

  // 결제상태와 결제액 수정
  $sql = "
    UPDATE hk_payments
    SET
      payment_status = '$payment_status',
      payment_amount = '$payment_amount'
    WHERE no = '$no'
  ";

  $result = mysqli_query($db, $sql);

  if($result){
    echo "
      <script>
        alert('결제정보가 저장되었습니다.');
        location.href='./admin.php?tab=payment';
      </script>
    ";
  }else{
    $error = mysqli_error($db);

    echo "
      <script>
        alert('결제정보 저장에 실패했습니다. DB 오류: " . addslashes($error) . "');
        history.back();
      </script>
    ";
  }

  mysqli_close($db);
?>