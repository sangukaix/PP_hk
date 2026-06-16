<?php
  // 세션 시작
  // 로그인 상태를 확인하려면 PHP 파일 맨 위에서 session_start()를 실행해야 함
  session_start();
?>

<!DOCTYPE html>
<html lang="ko">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Hello Korean - Contact</title>

  <!-- 외부 CSS 연결 -->
  <link rel="stylesheet" href="./contact.css">
  <link rel="stylesheet" href="../common/common.css">

  <!-- 카카오지도 API 연결 -->
  <!-- appkey 부분에는 본인 JavaScript 키를 넣어야 함 -->
  <script src="https://dapi.kakao.com/v2/maps/sdk.js?appkey=514d8e21c5cf93e54eb6db75e7f073c4"></script>

  <!-- 지도 실행 JS 연결 -->
  <script src="./contact_map.js" defer></script>
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
        <li><a href="#">Course</a></li>
        <li><a href="#">Teacher</a></li>
        <li><a href="#">Price</a></li>
        <li><a href="../contact_pg/contact.php">Contact</a></li>
        <li><a href="../board_pg/board.php" class="board">Board</a></li>

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
        // login_ok.php에서 로그인 성공 시 $_SESSION['user_id']를 저장했음
        if(isset($_SESSION['user_id'])){
        ?>

            <!-- 로그인한 상태일 때: 로그인 버튼 대신 로그아웃 표시 -->
            <li>
            <a href="../member_pg/logout.php" class="login_btn">로그아웃</a>
            </li>

            <!-- 로그인한 상태일 때: 회원가입 버튼 대신 사용자 아이디 표시 -->
            <li>
            <a href="#" class="join_btn"><?php echo $_SESSION['user_id']; ?>님</a>
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

  <section id="contact_visual">
    <div class="container">
      <h2>Contact Us</h2>
      <p>Hello Korean 한국어 수업 상담 대해 문의</p>
    </div>
  </section>

  <section id="contact_content">
    <div class="container">

      <div class="contact_info">
        <h3>HelloKorean 사무실 주소</h3>
        <p>Online Korean Language Education</p>
        <p>서울시 관악구 신림로 64길 46</p>
        <p>Email : hellokedu@naver.com</p>
        <p>Location : Seoul, Korea</p>
      </div>

      <!-- 지도가 그려질 영역 -->
      <!-- 카카오지도는 width, height 지정이 필수 -->
      <div id="map"></div>

    </div>
  </section>

<section id="my_location">
  <div class="container">

    <div class="current_info">
      <h3>현재 내 위치</h3>
      <p>브라우저에서 위치 정보 사용을 허용하면 현재 위치가 지도에 표시됩니다.</p>
      <p>Location : Your current location</p>
    </div>

    <!-- 현재 위치 지도가 들어갈 영역 -->
    <div id="current_map"></div>

  </div>
</section>

<section id="marker_control">
  <div class="container">

    <div class="marker_info">
      <h3>마커 테스트</h3>
      <p>지도를 클릭하면 클릭한 위치에 마커가 추가됩니다.</p>

      <button onclick="hideControlMarkers()">마커 감추기</button>
      <button onclick="showControlMarkers()">마커 보이기</button>

      <p id="marker_message">클릭한 위치에 마커가 표시됩니다!</p>
    </div>

    <!-- 여러 개 마커를 테스트할 지도 -->
    <div id="marker_map"></div>

  </div>
</section>


</main>

<footer>
  <div class="container">
    <p>© Global Link Co., Ltd. All rights reserved.</p>
  </div>
</footer>

</body>
</html>