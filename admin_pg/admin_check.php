<?php
  // 세션 시작
  // 관리자 로그인 여부를 확인하기 위해 필요함
  session_start();

  // 관리자 로그인이 되어 있지 않으면 로그인 페이지로 이동
  if(!isset($_SESSION['admin_login']) || $_SESSION['admin_login'] !== true){
    echo "
      <script>
        alert('관리자 로그인 후 이용할 수 있습니다.');
        location.href='./admin_login.php';
      </script>
    ";
    exit;
  }
?>