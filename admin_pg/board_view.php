<?php
  // 관리자 로그인 체크
  // 관리자 로그인이 안 되어 있으면 admin_login.php로 이동
  include "./admin_check.php";

  // 한글 깨짐 방지
  header("Content-Type:text/html; charset=utf-8");

  // DB 연결 파일 불러오기
  include "../common/db.php";

  // 주소창에서 문의글 번호 받기
  // 예: board_view.php?no=3
  $no = $_GET['no'] ?? '';

  // 번호가 없으면 관리자 페이지로 돌려보냄
  if($no == ''){
    echo "
      <script>
        alert('잘못된 접근입니다.');
        location.href='./admin.php';
      </script>
    ";
    exit;
  }

  // 숫자로 변환해서 안전하게 사용
  $no = (int)$no;

  // 해당 번호의 문의글 1개 가져오기
  $sql = "SELECT * FROM hk_board WHERE no = $no";

  // SQL 실행
  $result = mysqli_query($db, $sql);

  // 문의글이 있는지 확인
  if($result && mysqli_num_rows($result) > 0){
    $row = mysqli_fetch_array($result, MYSQLI_ASSOC);
  }else{
    echo "
      <script>
        alert('존재하지 않는 문의글입니다.');
        location.href='./admin.php';
      </script>
    ";
    exit;
  }

  // 화면 출력 시 특수문자를 안전하게 처리하는 함수
  function h($str){
    return htmlspecialchars((string)$str, ENT_QUOTES, "UTF-8");
  }
?>

<!DOCTYPE html>
<html lang="ko">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <title>Hello Korean Admin - 문의글 상세보기</title>

  <!-- 관리자 전용 CSS -->
  <link rel="stylesheet" href="./admin.css">
</head>

<body>

<header class="admin_header">
  <div class="admin_container">

    <!-- 관리자 페이지 제목 -->
    <h1>Hello Korean Admin</h1>

    <!-- 관리자 상단 버튼 -->
    <div class="admin_top_btn">
      <span><?php echo h($_SESSION['admin_id']); ?> 관리자님</span>
      <a href="../main_pg/01main_pg.php">사이트로 이동</a>
      <a href="./admin_logout.php">관리자 로그아웃</a>
    </div>

  </div>
</header>

<nav class="admin_nav">
  <div class="admin_container">
    <ul>
      <!-- 관리자 메뉴 -->
      <li><a href="./admin.php" class="admin_tab_link">회원관리</a></li>
      <li><a href="./admin.php" class="admin_tab_link active">문의글관리</a></li>
    </ul>
  </div>
</nav>

<main class="admin_main">
  <div class="admin_container">

    <section class="admin_title">
      <h2>문의글 상세보기</h2>
      <p>학생이 남긴 문의글을 확인하고 답변을 작성합니다.</p>
    </section>

    <section class="admin_table_area">

      <table class="admin_table">
        <caption>문의글 상세보기</caption>

        <tbody>
          <tr>
            <th>번호</th>
            <td class="text_left"><?php echo h($row['no']); ?></td>
          </tr>

          <tr>
            <th>작성자</th>
            <td class="text_left"><?php echo h($row['writer']); ?></td>
          </tr>

          <tr>
            <th>상담유형</th>
            <td class="text_left"><?php echo h($row['category']); ?></td>
          </tr>

          <tr>
            <th>제목</th>
            <td class="text_left"><?php echo h($row['title']); ?></td>
          </tr>

          <tr>
            <th>문의 내용</th>
            <td class="text_left">
              <!-- nl2br은 줄바꿈을 화면에서도 줄바꿈으로 보여줌 -->
              <?php echo nl2br(h($row['message'])); ?>
            </td>
          </tr>

          <tr>
            <th>상태</th>
            <td class="text_left"><?php echo h($row['status']); ?></td>
          </tr>

          <tr>
            <th>작성일</th>
            <td class="text_left"><?php echo h($row['date']); ?></td>
          </tr>
        </tbody>
      </table>

    </section>

    <section class="admin_answer_area">

      <h3>관리자 답변</h3>

      <!-- 답변 저장 form -->
      <form action="./board_answer_ok.php" method="post">

        <!-- 어떤 문의글에 답변하는지 알기 위해 글 번호를 hidden으로 보냄 -->
        <input type="hidden" name="no" value="<?php echo h($row['no']); ?>">

        <textarea name="answer" placeholder="답변 내용을 입력하세요."><?php echo h($row['answer']); ?></textarea>

        <div class="admin_answer_btn">
          <button type="submit">답변 저장</button>
          <!-- 문의글 상세보기에서 목록으로 가면 문의글관리 탭이 열린 상태로 이동 -->
          <a href="./admin.php?tab=board">목록으로</a>
        </div>

      </form>

    </section>

  </div>
</main>

</body>
</html>

<?php
  // DB 연결 종료
  mysqli_close($db);
?>