<?php
  session_start();
?>

<!DOCTYPE html>
<html lang="ko">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Hello Korean - Login</title>

  <link rel="stylesheet" href="../common/common.css">
  <link rel="stylesheet" href="./member.css?v=11">

</head>

<body>

<header>
  <div class="container">

    <a href="../main_pg/01main_pg.php">
      <img src="../main_pg/image/logo.png" alt="main_logo" class="logo">
    </a>

    <nav>
      <ul>
        <li><a href="../main_pg/01main_pg.php">Home</a></li>
        <li><a href="#">코스</a></li>
        <li><a href="#">강사소개</a></li>
        <li><a href="#">수강신청</a></li>
        <li><a href="../contact_pg/contact.php">고객센터</a></li>
        <li><a href="../board_pg/board.php" class="board">게시판</a></li>
        <!-- My page -->
        <li>
          <?php
            if(isset($_SESSION['user_id'])){
          ?>
            <a href="../mypage_pg/mypage.php" class="mypage_btn">My page</a>
          <?php
            }else{
          ?>
            <a href="#" class="mypage_btn" onclick="alert('로그인 후 접속해주세요.'); return false;">My page</a>
          <?php
            }
          ?>
        </li>
        <li><a href="../member_pg/login.php" class="login_btn">로그인</a></li>
        <li><a href="../member_pg/join.php" class="join_btn">회원가입</a></li>
      </ul>
    </nav>

  </div>
</header>

<main>

  <section id="member_visual">
    <div class="container">
      <h2>Login</h2>
      <p>Hello Korean 로그인 페이지입니다.</p>
    </div>
  </section>

  <section id="member_content">
    <div class="container">

      <div class="member_box">
        <h3>로그인</h3>

        <form action="./login_ok.php" method="post" class="member_form">

          <p>
            <input type="text" name="user_id" placeholder="아이디">
          </p>

          <p>
            <input type="password" name="user_pw" placeholder="비밀번호">
          </p>

          <div class="member_btn">
            <input type="submit" value="로그인">
          </div>

        </form>

        <p class="member_link">
          아직 회원이 아니신가요?
          <a href="./join.php">회원가입</a>
        </p>
      </div>

    </div>
  </section>

</main>

<footer>
  <div class="container">
    <p>Copyright © Hello Korean. All rights reserved.</p>
  </div>
</footer>

</body>
</html>