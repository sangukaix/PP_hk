<?php
  // 관리자 로그인 체크
  include "./admin_check.php";

  // 한글 깨짐 방지
  header("Content-Type:text/html; charset=utf-8");

  // DB 연결
  include "../common/db.php";

  // GET으로 결제번호 받기
  $payment_no = $_GET['payment_no'] ?? '';
  $payment_no = (int)$payment_no;

  if($payment_no < 1){
    echo "
      <script>
        alert('잘못된 접근입니다.');
        history.back();
      </script>
    ";
    exit;
  }

  // 결제번호 기준으로 학생 회원정보 가져오기
  $sql = "
    SELECT
      m.no,
      m.user_id,
      m.user_name,
      m.role
    FROM hk_payments p
    LEFT JOIN hk_members m
    ON p.member_no = m.no
    WHERE p.no = '$payment_no'
  ";

  $result = mysqli_query($db, $sql);
  $member = mysqli_fetch_array($result, MYSQLI_ASSOC);

  if(!$member || $member['no'] == ''){
    echo "
      <script>
        alert('학생 정보를 찾을 수 없습니다.');
        history.back();
      </script>
    ";
    exit;
  }

  // 학생 로그인 세션 만들기
  // 관리자 로그인 세션은 그대로 두고, 학생 세션만 추가로 저장함
  $_SESSION['user_no'] = $member['no'];
  $_SESSION['user_id'] = $member['user_id'];
  $_SESSION['user_name'] = $member['user_name'];
  $_SESSION['role'] = $member['role'];

  // 관리자가 학생페이지를 보고 있다는 표시
  $_SESSION['admin_view_student'] = true;
  $_SESSION['admin_view_payment_no'] = $payment_no;

  mysqli_close($db);

  // 학생 마이페이지로 이동
  echo "
    <script>
      location.href='../mypage_pg/mypage.php';
    </script>
  ";
?>