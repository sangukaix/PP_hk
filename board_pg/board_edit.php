<?php
  // 세션 시작
  // 로그인한 사용자인지 확인하기 위해 필요함
  session_start();

  // 한글 깨짐 방지
  header("Content-Type:text/html; charset=utf-8");

  // DB 연결 파일 불러오기
  include "../common/db.php";

  // 로그인 여부 확인
  // 로그인하지 않은 사람은 수정 페이지에 들어올 수 없음
  if(!isset($_SESSION['user_id'])){
    echo "
      <script>
        alert('로그인 후 이용해주세요.');
        location.href='../member_pg/login.php';
      </script>
    ";
    exit;
  }

  // 주소창에서 수정할 글 번호 받기
  // 예: board_edit.php?no=3
  $no = $_GET['no'] ?? '';

  // 글 번호가 없으면 잘못된 접근
  if($no == ''){
    echo "
      <script>
        alert('잘못된 접근입니다.');
        location.href='./board.php';
      </script>
    ";
    exit;
  }

  // 글 번호는 숫자로만 사용하기 위해 정수로 변환
  $no = (int)$no;

  // DB에서 해당 글 정보 가져오기
  $sql = "SELECT * FROM hk_board WHERE no = $no";
  $result = mysqli_query($db, $sql);

  // 글이 존재하는지 확인
  if($result && mysqli_num_rows($result) > 0){
    $row = mysqli_fetch_array($result, MYSQLI_ASSOC);
  }else{
    echo "
      <script>
        alert('존재하지 않는 글입니다.');
        location.href='./board.php';
      </script>
    ";
    exit;
  }

  // 본인이 작성한 글인지 확인
  // hk_board의 member_no와 현재 로그인한 회원 번호가 같아야 수정 가능
  if($row['member_no'] != $_SESSION['user_no']){
    echo "
      <script>
        alert('다른사람이 쓴 글은 수정할 수 없습니다.');
        history.back();
      </script>
    ";
    exit;
  }

  // 답변완료된 글인지 확인
  // 관리자가 이미 답변한 글은 수정 불가
  if($row['status'] == '답변완료'){
    echo "
      <script>
        alert('답변완료된 글은 수정할 수 없습니다.');
        history.back();
      </script>
    ";
    exit;
  }

  // 화면 출력 시 특수문자를 안전하게 바꿔주는 함수
  function h($str){
    return htmlspecialchars((string)$str, ENT_QUOTES, "UTF-8");
  }
?>

<!DOCTYPE html>
<html lang="ko">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <title>Hello Korean - Edit</title>

  <!-- 게시판 CSS 연결 -->
  <link rel="stylesheet" href="./board.css">

  <!-- 공통 header/nav/footer CSS 연결 -->
  <link rel="stylesheet" href="../common/common.css">

  <!-- 글쓰기 페이지에서 사용하던 입력 확인 JS를 같이 사용 -->
  <script src="./board_write.js" defer></script>
</head>

<body>

<header>
  <div class="container">

    <!-- 로고를 누르면 메인 PHP 페이지로 이동 -->
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

        <?php
          // 로그인한 상태이면 로그아웃 / 아이디 표시
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
      <p>작성한 문의글을 수정합니다.</p>
    </div>
  </section>

  <!-- 수정 영역 -->
  <section id="write_content">
    <div class="container">

      <div class="write_header">
        <h3>문의글 수정</h3>
        <p>답변이 등록되기 전까지 작성한 문의글을 수정할 수 있습니다.</p>
      </div>

      <!-- 수정 form 시작 -->
      <form action="./board_edit_ok.php" method="post" class="write_form" onsubmit="return submitCheck()">

        <!-- 어떤 글을 수정하는지 알기 위해 글 번호를 hidden으로 보냄 -->
        <input type="hidden" name="no" value="<?php echo h($row['no']); ?>">

        <div class="form_row">
          <label for="writer">작성자</label>

          <!-- 작성자는 수정할 수 없게 readonly 처리 -->
          <input type="text" id="writer" name="writer" value="<?php echo h($row['writer']); ?>" readonly class="readonly_input">
        </div>

        <div class="form_row">
          <label for="title">제목</label>

          <!-- 기존 제목을 value에 넣어두고 수정 가능하게 함 -->
          <input type="text" id="title" name="title" value="<?php echo h($row['title']); ?>">
        </div>

        <div class="form_row">
          <label for="category">상담 유형</label>

        <select id="category" name="category">
        <!-- 상담 유형 기본 선택값 -->
        <option value="">선택하기</option>

        <!-- 
            신규 문의 선택
            새로 저장된 값이 '신규 문의'인 경우 선택됨
            예전에 저장된 값에 '신규'라는 글자가 들어 있어도 선택되게 처리
        -->
        <option value="신규 문의" 
            <?php 
            if($row['category'] == '신규 문의' || strpos($row['category'], '신규') !== false){ 
                echo 'selected'; 
            } 
            ?>
        >
            신규 문의
        </option>

        <!-- 
            수강중 선택
            새로 저장된 값이 '수강중'인 경우 선택됨
            예전에 저장된 값에 '기존'이라는 글자가 들어 있어도 선택되게 처리
        -->
        <option value="수강중" 
            <?php 
            if($row['category'] == '수강중' || strpos($row['category'], '기존') !== false){ 
                echo 'selected'; 
            } 
            ?>
        >
            수강중
        </option>
        </select>
        </div>

        <div class="form_row content_row">
          <label for="content">내용</label>

          <!-- textarea는 value가 아니라 태그 사이에 기존 내용을 넣음 -->
          <textarea id="content" name="content"><?php echo h($row['message']); ?></textarea>
        </div>

        <div class="form_btn">
          <button type="submit">저장하기</button>

          <!-- 취소하기는 바로 이전 페이지로 돌아감 -->
          <button type="button" onclick="history.back()">취소하기</button>
        </div>

      </form>
      <!-- 수정 form 끝 -->

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
  // DB 연결 종료
  mysqli_close($db);
?>