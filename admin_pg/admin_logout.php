<?php
  // 세션 시작
  // 관리자 로그인 정보를 삭제하기 위해 필요함
  session_start();

  // 관리자 로그인 관련 세션만 삭제
  // 일반 사용자 로그인 세션은 건드리지 않음
  unset($_SESSION['admin_login']);
  unset($_SESSION['admin_id']);

  echo "
    <script>
      alert('관리자 로그아웃 되었습니다.');
      location.href='./admin_login.php';
    </script>
  ";
?>