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

<!-- AOS 외부 CSS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css">

<!-- Bootstrap Icons -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

<!-- Pretendard 웹폰트 -->
<link rel="stylesheet" as="style" crossorigin href="https://cdn.jsdelivr.net/gh/orioncactus/pretendard/dist/web/static/pretendard.css">

<!-- 내 CSS -->
<link rel="stylesheet" href="../common/common.css?v=260621_1">
<link rel="stylesheet" href="./01main.css?v=260622_1">
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

      <div class="visual_text">
        <h2>Online Education Solution</h2>

        <p class="main_title">
          꿈꾸던 커리어와 실력을 만드는 온라인 언어 학습
        </p>

        <p class="main_desc">
          검증된 튜터, 맞춤형 학습, 무제한 연습으로<br>
          언제 어디서나 성장을 지원합니다.
        </p>

        <div class="main_btn_wrap">
          <a href="../board_pg/board.php" class="main_btn main_btn_fill">무료 상담 신청</a>
          <a href="../course_pg/course_register.php" class="main_btn main_btn_line">수강안내 보기</a>
        </div>
      </div>

    </div>

  </section>

  <section id="intro">

    <div class="container">

      <article data-aos="fade-up">
        <div class="intro_card_top">
          <div class="intro_icon">
            <i class="bi bi-headset"></i>
          </div>

          <div class="intro_text">
            <h3>영미권 원어민 강사</h3>
            <p>
              검증된 강사진과 실시간 1:1<br>수업으로
              자연스러운 표현을 익힙니다.
            </p>
          </div>
        </div>

        <div class="intro_line">
          <span>→</span>
        </div>
      </article>

      <article data-aos="fade-up" data-aos-delay="100">
        <div class="intro_card_top">
          <div class="intro_icon">
            <i class="bi bi-people"></i>
          </div>

          <div class="intro_text">
            <h3>맞춤 튜터 추천</h3>
            <p>
              학습 목표와 수준에 맞는 튜터를 <br>매칭하여
              효과적인 학습을 제공합니다.
            </p>
          </div>
        </div>

        <div class="intro_line">
          <span>→</span>
        </div>
      </article>

      <article data-aos="fade-up" data-aos-delay="200">
        <div class="intro_card_top">
          <div class="intro_icon">
            <i class="bi bi-mic"></i>
          </div>

          <div class="intro_text">
            <h3>무제한 AI 연습</h3>
            <p>
              수업 후에도 AI와 자유롭게 말하기<br> 연습을
              하며 실력을 향상시킬 수 있습니다.
            </p>
          </div>
        </div>

        <div class="intro_line">
          <span>→</span>
        </div>
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

<!-- AOS 외부 JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>

<script>
  // AOS 실행
  AOS.init();
</script>

</body>
</html>