<?php
  // 세션 시작
  // 로그인 상태를 확인하려면 반드시 session_start()가 필요함
  session_start();

  // 한글 깨짐 방지
  header("Content-Type:text/html; charset=utf-8");

  // DB 연결 파일 불러오기
  // common/db.php 안에서 $db 변수로 MySQL에 연결됨
  include "../common/db.php";

  // hk_board 테이블에서 문의글을 최신순으로 가져오기
  // no가 큰 글이 최근 글이므로 DESC 사용
  $sql = "SELECT * FROM hk_board ORDER BY no DESC";

  // SQL 실행
  $result = mysqli_query($db, $sql);

  // 화면에 출력할 때 특수문자를 안전하게 바꿔주는 함수
  // 사용자가 입력한 제목이나 이름에 HTML 태그가 있어도 화면이 깨지지 않게 함
  function h($str){
    return htmlspecialchars((string)$str, ENT_QUOTES, "UTF-8");
  }
?>

<!DOCTYPE html>
<html lang="ko">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Hello Korean - Board</title>

  <!-- 게시판 CSS 연결 -->
  <link rel="stylesheet" href="./board.css">

  <!-- 공통 header/nav/footer CSS 연결 -->
  <link rel="stylesheet" href="../common/common.css">
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
        <li><a href="#">Course</a></li>
        <li><a href="#">Teacher</a></li>
        <li><a href="#">Price</a></li>

        <!-- Contact도 로그인 상태를 확인할 수 있는 PHP 페이지로 이동 -->
        <li><a href="../contact_pg/contact.php">Contact</a></li>

        <!-- Board도 로그인 상태를 확인할 수 있는 PHP 페이지로 이동 -->
        <li><a href="../board_pg/board.php" class="board">Board</a></li>

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

  <!-- Board 상단 영역 -->
  <section id="board_visual">
    <div class="container">
      <h2>Q&A Board</h2>
      <p>Hello Korean 수업에 대해 궁금한 점을 남겨주세요.</p>
    </div>
  </section>

  <!-- Board 내용 영역 -->
  <section id="board_content">
    <div class="container">

      <div class="board_header">

        <div class="board_title">
          <h3>문의 게시판</h3>
          <p>수업 신청, 레벨 테스트, 수강료 등 궁금한 내용을 확인하세요.</p>
        </div>

        <!-- 검색 기능은 아직 실제로 연결하지 않음 -->
        <!-- 나중에 keyword 값을 받아서 SQL 검색 기능으로 확장 가능 -->
        <form action="#" method="get" class="board_search">
          <select name="search_type">
            <option value="title">제목</option>
            <option value="writer">작성자</option>
            <option value="content">내용</option>
          </select>

          <input type="text" name="keyword" placeholder="검색어를 입력하세요">
          <button type="submit">검색</button>
        </form>

      </div>

      <div class="table_wrap">
        <table class="board_table">
          <caption>Q&A 게시판 목록</caption>

          <thead>
            <tr>
              <th>번호</th>
              <th>제목</th>
              <th>작성자</th>
              <th>작성일</th>
              <th>상태</th>
            </tr>
          </thead>

          <tbody>

            <!-- 공지사항은 DB에서 가져오지 않고 항상 맨 위에 고정 -->
            <tr class="notice">
              <td>공지</td>
              <td><a href="#">수업 신청 전 꼭 확인해주세요.</a></td>
              <td>관리자</td>
              <td>2026-06-14</td>
              <td><span class="status notice_text">공지</span></td>
            </tr>

            <?php
              // DB에서 가져온 문의글이 1개 이상 있을 때
              if($result && mysqli_num_rows($result) > 0){

                // 문의글을 한 줄씩 꺼내서 화면에 출력
                while($row = mysqli_fetch_array($result, MYSQLI_ASSOC)){

                  // 상태값이 비어 있으면 기본값으로 답변대기 표시
                  $status = $row['status'] ?? '답변대기';

                  // 상태에 따라 CSS 클래스 지정
                  // 답변완료면 done, 그 외에는 wait
                  if($status == '답변완료'){
                    $status_class = 'done';
                  }else{
                    $status_class = 'wait';
                  }

                  // 작성일에서 날짜만 잘라서 표시
                  // 예: 2026-06-14 15:30:10 → 2026-06-14
                  $date = substr($row['date'], 0, 10);
            ?>

              <tr>
                <!-- 문의글 번호 -->
                <td><?php echo h($row['no']); ?></td>

                <!-- 제목 -->
                <!-- 상담 유형을 제목 앞에 같이 표시 -->
                <td>
                  <a href="#">
                    <?php echo h($row['category']); ?>
                    <?php echo h($row['title']); ?>
                  </a>
                </td>

                <!-- 작성자 -->
                <td><?php echo h($row['writer']); ?></td>

                <!-- 작성일 -->
                <td><?php echo h($date); ?></td>

                <!-- 답변 상태 -->
                <td>
                  <span class="status <?php echo $status_class; ?>">
                    <?php echo h($status); ?>
                  </span>
                </td>
              </tr>

            <?php
                }
              }else{
            ?>

              <!-- DB에 문의글이 없을 때 표시 -->
              <tr>
                <td colspan="5">등록된 문의글이 없습니다.</td>
              </tr>

            <?php
              }
            ?>

          </tbody>
        </table>
      </div>

      <div class="board_bottom">

        <!-- 페이지 번호는 아직 실제 기능 없음 -->
        <!-- 나중에 글이 많아지면 페이지네이션 기능을 추가할 수 있음 -->
        <div class="pagination">
          <a href="#">이전</a>
          <a href="#" class="active">1</a>
          <a href="#">다음</a>
        </div>

        <!-- 글쓰기 버튼 -->
        <!-- board_write.php로 이동해야 로그인 체크와 DB 저장 기능을 사용할 수 있음 -->
        <div class="write_btn">
          <a href="./board_write.php">글쓰기</a>
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