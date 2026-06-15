<?php
  // 관리자 로그인 페이지에서도 세션을 사용함
  session_start();

  // 이미 관리자 로그인이 되어 있으면 관리자 홈으로 이동
  if(isset($_SESSION['admin_login']) && $_SESSION['admin_login'] === true){
    echo "
      <script>
        location.href='./admin.php';
      </script>
    ";
    exit;
  }
?>

<!DOCTYPE html>
<html lang="ko">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <title>Hello Korean Admin Login</title>

  <!-- 관리자 전용 CSS 연결 -->
  <link rel="stylesheet" href="./admin.css">
</head>

<body>

  <main class="admin_login_wrap">

    <section class="admin_login_box">

      <h1>Hello Korean Admin</h1>
      <p>관리자 로그인 페이지.</p>

      <!-- 관리자 로그인 form -->
      <form action="./admin_login_ok.php" method="post">

        <div class="admin_login_row">
          <label for="admin_id">관리자 아이디</label>
          <input type="text" id="admin_id" name="admin_id" placeholder="관리자 아이디">
        </div>

        <div class="admin_login_row">
          <label for="admin_pw">관리자 비밀번호</label>
          <input type="password" id="admin_pw" name="admin_pw" placeholder="관리자 비밀번호">
        </div>

        <button type="submit" class="admin_login_btn">관리자 로그인</button>

      </form>

      <a href="../main_pg/01main_pg.php" class="admin_site_link">사이트로 돌아가기</a>

    </section>

  </main>

</body>
</html>