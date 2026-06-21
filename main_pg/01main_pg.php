<?php
  // 세션 시작
  // 로그인 상태를 확인하려면 PHP 페이지 맨 위에서 session_start()를 실행해야 함
  session_start();

  // 화면에 출력할 때 특수문자를 안전하게 바꿔주는 함수
  function h($str){
    return htmlspecialchars((string)$str, ENT_QUOTES, "UTF-8");
  }
?>

<!DOCTYPE html>
<html lang="ko">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Hello Korean</title>

  <!-- 외부 CSS 연결 -->
  <link rel="stylesheet" href="./01main.css">
<link rel="stylesheet" href="../common/common.css?v=260621_1">
</head>

<body>

<header>
  <div class="container">

    <!-- 로고를 누르면 로그인 상태를 확인할 수 있는 PHP 메인 페이지로 이동 -->
    <a href="../main_pg/01main_pg.php">
      <img src="../main_pg/image/logo.png" alt="main_logo" class="logo">
    </a>

<nav>
  <ul>
    <!-- Home은 로그인 상태를 확인할 수 있는 PHP 메인 페이지로 이동 -->
    <li><a href="../main_pg/01main_pg.php">Home</a></li>

    <!-- 아직 만들지 않은 메뉴들은 임시로 # 처리 -->
    <li><a href="#">코스</a></li>
    <li><a href="#">강사소개</a></li>
    <li><a href="../course_pg/course_register.php" class="course_register_btn">수강신청</a></li>

    <!-- Contact도 로그인 상태를 확인해야 하므로 contact.php로 이동 -->
    <li><a href="../contact_pg/contact.php">고객센터</a></li>

    <!-- Board도 로그인 상태를 확인해야 하므로 board.php로 이동 -->
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

    <?php
      // 로그인한 상태인지 확인
      // 로그인 성공 시 login_ok.php에서 $_SESSION['user_id']를 저장했음
      if(isset($_SESSION['user_id'])){
    ?>

      <!-- 로그인한 상태일 때: 로그아웃 표시 -->
      <li>
        <a href="../member_pg/logout.php" class="login_btn">로그아웃</a>
      </li>

      <!-- 로그인한 상태일 때: 사용자 아이디 표시 -->
      <li>
        <a href="#" class="join_btn"><?php echo h($_SESSION['user_id']); ?>님</a>
      </li>

    <?php
      }else{
    ?>

      <!-- 로그인하지 않은 상태일 때: 로그인 / 회원가입 표시 -->
      <li>
        <a href="../member_pg/login.php" class="login_btn">로그인</a>
      </li>

      <li>
        <a href="../member_pg/join.php" class="join_btn">회원가입</a>
      </li>

    <?php
      }
    ?>
  </ul>
</nav>

  </div>
</header>

<main>

  <section id="main_visual">

    <div class="container">

      <h2>Online Education Solution</h2>

      <p>
        꿈꾸던 커리어와 실력을 만드는 온라인 언어 학습
      </p>

    </div>

  </section>

  <section id="intro">

    <div class="container">

      <article>
        <h3>영미권 원어민 강사</h3>
        <p>TESOL수료 명문대 출신 원어민<br> 튜터와 1:1 화상영어</p>
      </article>

      <article>
        <h3>맞춤 튜터 추천</h3>
        <p>원하는 스타일과 목적에 딱<br>
          맞는 튜터를 추천해드려요</p>
      </article>

      <article>
        <h3>무제한 AI 연습</h3>
        <p>선생님과 수업 후 AI와 24시간 무한반복 연습</p>
      </article>

    </div>

  </section>

</main>

<footer>

  <div class="container">

    <p>
      © Global Link Co., Ltd. All rights reserved.
    </p>

  </div>

</footer>

</body>
</html>