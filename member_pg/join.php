<!DOCTYPE html>
<html lang="ko">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Hello Korean - Join</title>

  <link rel="stylesheet" href="./member.css">
<link rel="stylesheet" href="../common/common.css">
</head>

<body>

<header>
  <div class="container">

    <a href="../main_pg/01main_pg.html">
      <img src="../main_pg/image/logo.png" alt="main_logo" class="logo">
    </a>

    <nav>
      <ul>
        <li><a href="../main_pg/01main_pg.html">Home</a></li>
        <li><a href="#">Course</a></li>
        <li><a href="#">Teacher</a></li>
        <li><a href="#">Price</a></li>
        <li><a href="../contact_pg/contact.html">Contact</a></li>
        <li><a href="../board_pg/board.html" class="board">Board</a></li>
        <li><a href="../member_pg/login.php" class="login_btn">로그인</a></li>
        <li><a href="../member_pg/join.php" class="join_btn">회원가입</a></li>
      </ul>
    </nav>

  </div>
</header>

  </div>
</header>

<main>

  <section id="member_visual">
    <div class="container">
      <h2>Join Us</h2>
      <p>Hello Korean 회원가입 페이지입니다.</p>
    </div>
  </section>

  <section id="member_content">
    <div class="container">

      <div class="member_box">
        <h3>회원가입</h3>

        <form action="./join_ok.php" method="post" class="member_form">

          <p>
            <input type="text" name="user_id" placeholder="아이디">
          </p>

          <p>
            <input type="password" name="user_pw" placeholder="비밀번호">
          </p>

          <p>
            <input type="text" name="user_name" placeholder="이름">
          </p>

          <p>
            <input type="text" name="phone" placeholder="전화번호">
          </p>

          <p>
            <input type="text" name="email" placeholder="이메일">
          </p>

          <div class="member_btn">
            <input type="submit" value="회원가입">
            <input type="reset" value="초기화">
          </div>

        </form>

        <p class="member_link">
          이미 회원이신가요?
          <a href="./login.php">로그인</a>
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