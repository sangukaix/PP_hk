<?php
  // 세션 시작
  // 현재 로그인 정보를 삭제하려면 session_start()가 먼저 필요함
  session_start();

  // 세션에 저장된 모든 값 삭제
  // 예: user_id, user_name, role 등
  session_unset();

  // 세션 자체를 완전히 종료
  session_destroy();

  // 로그아웃 후 메인 PHP 페이지로 이동
  // 01main_pg.php는 로그인 상태를 확인할 수 있는 페이지임
  echo "
    <script>
      alert('로그아웃 되었습니다.');
      location.href='../main_pg/01main_pg.php';
    </script>
  ";
?>