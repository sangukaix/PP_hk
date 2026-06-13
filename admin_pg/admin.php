<!DOCTYPE html>
<html lang="ko">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <title>Hello Korean - Admin</title>

  <link rel="stylesheet" href="./admin.css">
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

<main>

  <section id="admin_visual">
    <div class="container">
      <h2>Admin Page</h2>
      <p>Hello Korean 관리자 페이지입니다.</p>
    </div>
  </section>

  <section id="admin_content">
    <div class="container">

      <div class="admin_menu">

        <div class="admin_card">
          <h3>회원 관리</h3>
          <p>회원가입한 학생들의 이름, 아이디, 전화번호, 이메일 정보를 확인합니다.</p>
          <a href="./member_list.php">회원 목록 보기</a>
        </div>

        <div class="admin_card">
          <h3>문의글 관리</h3>
          <p>학생들이 Board에 남긴 문의글을 확인하고 관리합니다.</p>
          <a href="./board_list.php">문의글 보기</a>
        </div>

      </div>

      <div class="admin_notice">
        <h4>관리자 페이지 안내</h4>
        <p>
          이 페이지는 나중에 관리자 계정만 접근할 수 있도록 PHP 로그인 권한 처리를 추가할 예정입니다.
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