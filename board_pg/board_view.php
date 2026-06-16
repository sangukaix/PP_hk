<?php
  // 세션 시작
  // 로그인 상태에 따라 상단 메뉴를 다르게 보여주기 위해 필요함
  session_start();

  // 한글 깨짐 방지
  header("Content-Type:text/html; charset=utf-8");

  // DB 연결 파일 불러오기
  // common/db.php 안에서 $db 변수로 MySQL에 연결됨
  include "../common/db.php";

  // 주소창에서 넘어온 글 번호 받기
  // 예: board_view.php?no=3 이면 no 값은 3
  $no = $_GET['no'] ?? '';

  // 글 번호가 없으면 게시판으로 돌려보냄
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
  // SQL에 이상한 값이 들어가는 것을 막기 위함
  $no = (int)$no;

  // hk_board 테이블에서 해당 번호의 문의글 1개만 가져오기
  $sql = "SELECT * FROM hk_board WHERE no = $no";

  // SQL 실행
  $result = mysqli_query($db, $sql);

  // 글이 있는지 확인
  if($result && mysqli_num_rows($result) > 0){
    // 문의글 정보 가져오기
    $row = mysqli_fetch_array($result, MYSQLI_ASSOC);
  }else{
    // 해당 번호의 글이 없으면 게시판으로 이동
    echo "
      <script>
        alert('존재하지 않는 글입니다.');
        location.href='./board.php';
      </script>
    ";
    exit;
  }

  // 화면 출력 시 특수문자를 안전하게 바꿔주는 함수
  function h($str){
    return htmlspecialchars((string)$str, ENT_QUOTES, "UTF-8");
  }

  // 상태값이 비어 있으면 기본값으로 답변대기 표시
  $status = $row['status'] ?? '답변대기';

  // 상태에 따라 CSS class 지정
  if($status == '답변완료'){
    $status_class = 'done';
  }else{
    $status_class = 'wait';
  }
?>

<!DOCTYPE html>
<html lang="ko">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <title>Hello Korean - Board View</title>

  <!-- 게시판 CSS 연결 -->
  <link rel="stylesheet" href="./board.css">

  <!-- 공통 header/nav/footer CSS 연결 -->
  <link rel="stylesheet" href="../common/common.css">
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
        <!-- Home -->
        <li><a href="../main_pg/01main_pg.php">Home</a></li>

        <!-- 아직 만들지 않은 메뉴들은 임시 링크 -->
        <li><a href="#">Course</a></li>
        <li><a href="#">Teacher</a></li>
        <li><a href="#">Price</a></li>

        <!-- Contact -->
        <li><a href="../contact_pg/contact.php">Contact</a></li>

        <!-- Board -->
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
          if(isset($_SESSION['user_id'])){
        ?>

          <!-- 로그인 상태: 로그아웃 표시 -->
          <li>
            <a href="../member_pg/logout.php" class="login_btn">로그아웃</a>
          </li>

          <!-- 로그인 상태: 아이디 표시 -->
          <li>
            <a href="#" class="join_btn"><?php echo h($_SESSION['user_id']); ?>님</a>
          </li>

        <?php
          }else{
        ?>

          <!-- 로그아웃 상태: 로그인 / 회원가입 표시 -->
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
      <p>문의글 상세 내용입니다.</p>
    </div>
  </section>

  <!-- 상세보기 영역 -->
  <section id="board_content">
    <div class="container">

      <div class="board_header">
        <div class="board_title">
          <h3>문의글 상세보기</h3>
          <p>작성한 문의 내용과 관리자 답변을 확인할 수 있습니다.</p>
        </div>
      </div>

      <div class="table_wrap">
        <table class="board_table">
          <caption>문의글 상세보기</caption>

          <tbody>
            <tr>
              <th>제목</th>
              <td class="text_left">
                <?php echo h($row['category']); ?>
                <?php echo h($row['title']); ?>
              </td>
            </tr>

            <tr>
              <th>작성자</th>
              <td class="text_left"><?php echo h($row['writer']); ?></td>
            </tr>

            <tr>
              <th>작성일</th>
              <td class="text_left"><?php echo h($row['date']); ?></td>
            </tr>

            <tr>
              <th>상태</th>
              <td class="text_left">
                <span class="status <?php echo $status_class; ?>">
                  <?php echo h($status); ?>
                </span>
              </td>
            </tr>

            <tr>
              <th>문의 내용</th>
              <td class="text_left">
                <!-- nl2br은 줄바꿈을 화면에서도 줄바꿈으로 보여주는 함수 -->
                <?php echo nl2br(h($row['message'])); ?>
              </td>
            </tr>

            <tr>
              <th>관리자 답변</th>
              <td class="text_left">
                <?php
                  // 관리자 답변이 있을 때
                  if(isset($row['answer']) && $row['answer'] != ''){
                    echo nl2br(h($row['answer']));

                    // 답변 날짜가 있으면 같이 표시
                    if(isset($row['answer_date']) && $row['answer_date'] != ''){
                      echo "<br><br>";
                      echo "<small>답변일: " . h($row['answer_date']) . "</small>";
                    }
                  }else{
                    // 아직 답변이 없을 때
                    echo "아직 등록된 답변이 없습니다.";
                  }
                ?>
              </td>
            </tr>
          </tbody>
        </table>
          <div class="board_bottom">

          <!-- 버튼 영역 -->
          <div class="write_btn">

            <!-- 상세보기 하단 버튼 영역 -->
            <div class="view_btn_area">

              <!-- 목록으로 이동 -->
              <a href="./board.php" class="list_btn">목록으로</a>

              <?php
                // 로그인한 사람이고,
                // 현재 로그인한 회원 번호와 글 작성자의 회원 번호가 같고,
                // 아직 답변완료 상태가 아니라면 수정/삭제 버튼을 보여준다.
                if(
                  isset($_SESSION['user_no']) &&
                  $_SESSION['user_no'] == $row['member_no'] &&
                  $status != '답변완료'
                ){
              ?>

                <!-- 수정하기 이동 -->
                <a href="./board_edit.php?no=<?php echo h($row['no']); ?>" class="edit_btn">수정하기</a>

                <!-- 삭제하기 -->
                <form action="./board_delete.php" method="post" class="delete_form" onsubmit="return confirm('정말 이 글을 삭제하시겠습니까?');">

                  <!-- 삭제할 글 번호 -->
                  <input type="hidden" name="no" value="<?php echo h($row['no']); ?>">

                  <!-- 삭제 버튼 -->
                  <button type="submit" class="user_delete_btn">삭제</button>
                </form>

              <?php
                }
              ?>

            </div>

            <!-- 안내 문구 -->
            <p class="btn_notice">※ 수정 및 삭제는 본인이 작성한 게시물만 가능합니다.</p>

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
  // DB 연결 종료
  mysqli_close($db);
?>