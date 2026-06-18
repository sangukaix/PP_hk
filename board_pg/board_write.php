<?php
// 로그인한 사람만 글쓰기 페이지에 들어올 수 있게 한다.
// 로그인 안 했으면 login.php로 보낸다.
    session_start();

  if(!isset($_SESSION['user_id'])){
    echo "
      <script>
        alert('로그인 후 글을 작성할 수 있습니다.');
        location.href='../member_pg/login.php';
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
  <title>Hello Korean - Write</title>

  <!-- CSS 연결 -->
  <link rel="stylesheet" href="./board.css">
  <link rel="stylesheet" href="../common/common.css">

  <!-- JS 연결 : defer는 HTML을 먼저 읽은 뒤 JS를 실행하게 해줌 -->
  <script src="./board_write.js" defer></script>
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
        <?php
        // 로그인 상태라면 로그아웃 / 아이디 표시
        if(isset($_SESSION['user_id'])){
        ?>
            <li>
            <a href="../member_pg/logout.php" class="login_btn">로그아웃</a>
            </li>

            <li>
            <a href="#" class="join_btn"><?php echo $_SESSION['user_id']; ?>님</a>
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
      <p>문의 내용을 작성해주세요.</p>
    </div>
  </section>

  <!-- 글쓰기 영역 -->
  <section id="write_content">
    <div class="container">

      <div class="write_header">
        <h3>문의 작성</h3>
        <p>문의 내용을 남겨주시면 확인 후 답변드리겠습니다.</p>
      </div>

      <!-- 글쓰기 form 시작 -->
      <form action="./board_write_ok.php" method="post" class="write_form" onsubmit="return submitCheck()">

        <div class="form_row">
          <label for="writer">작성자</label>
          <input type="text" id="writer" name="writer" value="<?php echo $_SESSION['user_name']; ?>" readonly>
        </div>

        <div class="form_row">
          <label for="title">제목</label>
          <input type="text" id="title" name="title" placeholder="제목을 입력하세요">
        </div>

        <div class="form_row">
          <label for="category">상담 유형</label>

          <select id="category" name="category">
            <option value="">선택하기</option>
            <option value="신규 문의">신규 문의</option>
            <option value="수강중">수강중</option>
          </select>
        </div>

        <div class="form_row content_row">
          <label for="content">내용</label>
          <textarea id="content" name="content" placeholder="문의 내용을 입력하세요"></textarea>
        </div>

        <div class="form_btn">
          <button type="button" onclick="showPreview()">미리보기</button>
          <button type="submit">SUBMIT</button>
          <a href="./board.php">목록으로</a>
        </div>

      </form>
      <!-- 글쓰기 form 끝 -->


      <!-- 미리보기 영역 시작 -->
      <div class="preview_box" id="preview_box">
        <h3>미리보기</h3>

        <div class="preview_content">
          <p class="empty_text">미리보기 버튼을 누르면 작성한 내용이 여기에 표시됩니다.</p>
        </div>
      </div>
      <!-- 미리보기 영역 끝 -->

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