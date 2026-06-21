<?php
  // 세션 시작
  session_start();

  // 한글 깨짐 방지
  header("Content-Type:text/html; charset=utf-8");

  // DB 연결
  include "../common/db.php";

  // 화면 출력용 특수문자 처리 함수
  function h($str){
    return htmlspecialchars((string)$str, ENT_QUOTES, "UTF-8");
  }

  // 공지사항 1개 가져오기
  $notice_sql = "SELECT * FROM hk_board_notice ORDER BY no ASC LIMIT 1";
  $notice_result = mysqli_query($db, $notice_sql);
  $notice = mysqli_fetch_array($notice_result, MYSQLI_ASSOC);

  // 혹시 DB에 공지사항이 없을 때 기본값
  if(!$notice){
    $notice = [
      'title' => '문의글 작성시 필독',
      'content' => '등록된 공지사항이 없습니다.',
      'updated_at' => ''
    ];
  }
?>

<!DOCTYPE html>
<html lang="ko">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <title>Hello Korean - Board Notice</title>

  <!-- 게시판 CSS -->
  <link rel="stylesheet" href="./board.css">

  <!-- 공통 CSS -->
  <link rel="stylesheet" href="../common/common.css?v=260621_2">
</head>

<body>

<header>
  <div class="container">

    <!-- 로고 -->
    <a href="../main_pg/01main_pg.php">
      <img src="../main_pg/image/logo.png" alt="main_logo" class="logo">
    </a>

    <nav>
      <ul>
        <li><a href="../main_pg/01main_pg.php">Home</a></li>
        <li><a href="#">코스</a></li>
        <li><a href="#">강사소개</a></li>
        <li><a href="../course_pg/course_register.php" class="course_register_btn">수강신청</a></li>
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

        <?php
          if(isset($_SESSION['user_id'])){
        ?>
          <li>
            <a href="../member_pg/logout.php" class="login_btn">로그아웃</a>
          </li>

          <li>
            <a href="#" class="join_btn"><?php echo h($_SESSION['user_id']); ?>님</a>
          </li>
        <?php
          }else{
        ?>
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

  <!-- Board 상단 영역 -->
  <section id="board_visual">
    <div class="container">
      <h2>Q&A Board</h2>
      <p>Hello Korean 수업에 대해 궁금한 점을 남겨주세요.</p>
    </div>
  </section>

  <!-- 공지사항 내용 -->
<section id="board_content">
  <div class="container">

    <div class="board_header">
      <div class="board_title">
        <h3>문의글 작성시 필독</h3>
        <p>문의글 작성 전 아래 내용을 확인해주세요.</p>
      </div>
    </div>

    <div class="table_wrap">
      <table class="board_table">
        <caption>문의글 작성시 필독</caption>

        <tbody>
          <tr>
            <th>제목</th>
            <td class="text_left">
              <?php echo h($notice['title']); ?>
            </td>
          </tr>

          <tr>
            <th>내용</th>
            <td class="text_left">
              <?php echo nl2br(h($notice['content'])); ?>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div class="board_bottom">
      <div class="write_btn">
        <div class="view_btn_area">
          <a href="./board.php" class="list_btn">목록으로</a>
        </div>
      </div>
    </div>

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

<?php
  mysqli_close($db);
?>