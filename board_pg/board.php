<?php
  // 세션 시작
  // 로그인 상태를 확인하려면 반드시 session_start()가 필요함
  session_start();

  // 한글 깨짐 방지
  header("Content-Type:text/html; charset=utf-8");

  // DB 연결 파일 불러오기
  // common/db.php 안에서 $db 변수로 MySQL에 연결됨
  include "../common/db.php";

  // 검색 유형 받기
  // 검색창 select의 name="search_type"에서 넘어오는 값
  $search_type = $_GET['search_type'] ?? 'title';

  // 검색어 받기
  // 검색창 input의 name="keyword"에서 넘어오는 값
  $keyword = $_GET['keyword'] ?? '';

  // 검색 유형에 따라 실제 DB 컬럼 이름을 정함
  // 사용자가 선택한 값을 그대로 SQL에 넣지 않고, 우리가 허용한 값만 사용
  if($search_type == 'writer'){
    $search_column = 'writer';
  }else if($search_type == 'content'){
    $search_column = 'message';
  }else{
    $search_column = 'title';
  }

  // 현재 페이지 번호 받기
  // 주소에 page 값이 없으면 기본값은 1페이지
  $page = $_GET['page'] ?? 1;

  // 페이지 번호는 숫자로만 사용하기 위해 정수로 변환
  $page = (int)$page;

  // page 값이 1보다 작으면 1로 고정
  if($page < 1){
    $page = 1;
  }

  // 한 페이지에 보여줄 문의글 개수
  $list_num = 10;

  // 현재 페이지에서 몇 번째 글부터 가져올지 계산
  // 1페이지: 0번부터, 2페이지: 10번부터, 3페이지: 20번부터
  $start = ($page - 1) * $list_num;

  // 검색 조건 SQL을 담을 변수
  $where_sql = "";

  // 검색어가 있을 때만 WHERE 조건을 만듦
  if($keyword != ''){

    // 검색어를 SQL에 안전하게 넣기 위해 처리
    $safe_keyword = mysqli_real_escape_string($db, $keyword);

    // 선택한 컬럼에서 검색어가 포함된 글만 찾음
    $where_sql = "WHERE $search_column LIKE '%$safe_keyword%'";
  }

  // 전체 문의글 개수 구하기
  // 페이지 수를 계산하기 위해 필요함
  $count_sql = "SELECT COUNT(*) AS total FROM hk_board $where_sql";
  $count_result = mysqli_query($db, $count_sql);
  $count_row = mysqli_fetch_array($count_result, MYSQLI_ASSOC);

  // 전체 문의글 개수
  $total_count = $count_row['total'];

  // 전체 페이지 수 계산
  // 예: 글 23개 / 10개씩 보기 = 3페이지
  $total_page = ceil($total_count / $list_num);

  // 문의글이 하나도 없을 때도 최소 1페이지로 처리
  if($total_page < 1){
    $total_page = 1;
  }

  // 현재 페이지에 보여줄 문의글만 가져오기
  // LIMIT 시작번호, 가져올개수
      $sql = "
        SELECT 
          b.*,
          m.user_id AS board_user_id
        FROM hk_board b
        LEFT JOIN hk_members m
        ON b.member_no = m.no
        $where_sql
        ORDER BY b.no DESC
        LIMIT $start, $list_num
      ";

  // SQL 실행
  $result = mysqli_query($db, $sql);

  // 화면에 출력할 때 특수문자를 안전하게 바꿔주는 함수
  // 사용자가 입력한 제목이나 이름에 HTML 태그가 있어도 화면이 깨지지 않게 함
  function h($str){
    return htmlspecialchars((string)$str, ENT_QUOTES, "UTF-8");
  }

  // 작성자 이름 가리기
      // 예: 현상욱 -> 현*욱
      function hide_name($name){

        $name = trim($name);
        $len = mb_strlen($name, "UTF-8");

        // 이름이 없으면 빈칸
        if($len == 0){
          return "";
        }

        // 한 글자 이름이면 *
        if($len == 1){
          return "*";
        }

        // 두 글자 이름이면 마지막 글자만 *
        if($len == 2){
          return mb_substr($name, 0, 1, "UTF-8") . "*";
        }

        // 세 글자 이상이면 첫 글자 + * + 마지막 글자
        $first = mb_substr($name, 0, 1, "UTF-8");
        $last = mb_substr($name, $len - 1, 1, "UTF-8");

        return $first . "*" . $last;
        }

                  // 아이디 가리기
          // 예: test123 -> te***
          function hide_user_id($user_id){

            $user_id = trim($user_id);
            $len = mb_strlen($user_id, "UTF-8");

            if($len == 0){
              return "";
            }

            if($len == 1){
              return mb_substr($user_id, 0, 1, "UTF-8") . "***";
            }

            $front = mb_substr($user_id, 0, 2, "UTF-8");

            return $front . "***";
          }

      // ==============================
    // 게시판 상단 필독 공지사항 가져오기
    // ==============================
    $notice_sql = "SELECT * FROM hk_board_notice ORDER BY no ASC LIMIT 1";
    $notice_result = mysqli_query($db, $notice_sql);
    $board_notice = mysqli_fetch_array($notice_result, MYSQLI_ASSOC);

    // 혹시 공지사항이 없을 때 기본값
    if(!$board_notice){
      $board_notice = [
        'title' => '문의글 작성시 필독',
        'updated_at' => ''
      ];
    }

  // 상담유형을 화면에 보기 좋게 정리하는 함수
// 예전 DB 값에 [신규], [기존] 같은 글자가 남아 있어도 화면에서는 깔끔하게 보여줌
function show_category($category){

  // category 값에 '신규'라는 글자가 들어 있으면 신규 문의로 표시
  if(strpos($category, '신규') !== false){
    return '신규 문의';
  }

  // category 값에 '기존'이라는 글자가 들어 있으면 수강중으로 표시
  if(strpos($category, '기존') !== false){
    return '수강중';
  }

  // 이미 수강중 또는 신규 문의로 저장된 경우 그대로 표시
  if($category == '수강중' || $category == '신규 문의'){
    return $category;
  }

  // 그 외 값은 그대로 표시
  return $category;
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
        <li><a href="#">코스</a></li>
        <li><a href="#">강사소개</a></li>
        <li><a href="../course_pg/course_register.php" class="course_register_btn">수강신청</a></li>

        <!-- Contact도 로그인 상태를 확인할 수 있는 PHP 페이지로 이동 -->
        <li><a href="../contact_pg/contact.php">고객센터</a></li>

        <!-- Board도 로그인 상태를 확인할 수 있는 PHP 페이지로 이동 -->
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
  <section id="board_visual" class="sub_visual">
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

        <!-- 검색 form -->
        <!-- method="get"을 사용하면 검색어가 주소창에 표시됨 -->
        <form action="./board.php" method="get" class="board_search">

          <select name="search_type">

            <!-- 제목 검색 선택 유지 -->
            <option value="title" <?php if($search_type == 'title'){ echo 'selected'; } ?>>
              제목
            </option>

            <!-- 작성자 검색 선택 유지 -->
            <option value="writer" <?php if($search_type == 'writer'){ echo 'selected'; } ?>>
              작성자
            </option>

            <!-- 내용 검색 선택 유지 -->
            <option value="content" <?php if($search_type == 'content'){ echo 'selected'; } ?>>
              내용
            </option>

          </select>

          <!-- 검색 후에도 입력한 검색어가 그대로 보이게 value에 넣어줌 -->
          <input type="text" name="keyword" placeholder="검색어를 입력하세요" value="<?php echo h($keyword); ?>">

          <button type="submit">검색</button>
        </form>

      </div>

      <div class="table_wrap">
        <table class="board_table">
          <caption>Q&A 게시판 목록</caption>

          <thead>
            <tr>
              <th>번호</th>
              <th>상담유형</th>
              <th>제목</th>
              <th>작성자</th>
              <th>작성일</th>
              <th>상태</th>
            </tr>
          </thead>

          <tbody>
              <!-- 공지사항은 DB에서 가져와서 항상 맨 위에 고정 -->
                <tr class="notice">
                  <td>-</td>
                  <td>공지</td>
                  <td>
                    <a href="./board_notice.php">
                      ■<?php echo h($board_notice['title']); ?>■
                    </a>
                  </td>
                  <td class="notice_writer">관리자</td>
                  <td>
                    <?php
                      if($board_notice['updated_at'] != ''){
                        echo h(substr($board_notice['updated_at'], 0, 10));
                      }else{
                        echo '-';
                      }
                    ?>
                  </td>
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
                <!-- 상담유형 -->
                <td>
                  <?php echo h(show_category($row['category'])); ?>
                </td>

                <!-- 제목 -->
                <td>
                  <a href="./board_view.php?no=<?php echo h($row['no']); ?>">
                    <?php echo h($row['title']); ?>
                  </a>
                </td>

                <!-- 작성자 -->
                <td class="board_writer">
                  <?php echo h(hide_name($row['writer'])); ?>
                  <?php
                    if(isset($row['board_user_id']) && $row['board_user_id'] != ''){
                      echo "(" . h(hide_user_id($row['board_user_id'])) . ")";
                    }
                  ?>
                </td>

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
                <td colspan="6">
                  <?php
                    // 검색어가 있을 때는 검색 결과 없음으로 표시
                    if($keyword != ''){
                      echo "검색 결과가 없습니다.";
                    }else{
                      echo "등록된 문의글이 없습니다.";
                    }
                  ?>
                </td>
              </tr>

            <?php
              }
            ?>

          </tbody>
        </table>
      </div>

      <div class="board_bottom">

        <div class="pagination">

          <?php
            // 검색어를 페이지 링크에 넣기 위해 URL 형식으로 변환
            $url_keyword = urlencode($keyword);
          ?>

          <?php
            // 현재 페이지가 1보다 크면 이전 버튼 활성화
            if($page > 1){
          ?>
            <a href="./board.php?page=<?php echo $page - 1; ?>&search_type=<?php echo h($search_type); ?>&keyword=<?php echo $url_keyword; ?>">이전</a>
          <?php
            }else{
          ?>
            <!-- 1페이지에서는 이전 버튼 비활성화 -->
            <a href="#" class="disabled">이전</a>
          <?php
            }
          ?>

          <?php
            // 1페이지부터 마지막 페이지까지 번호 출력
            for($i = 1; $i <= $total_page; $i++){
          ?>

            <a href="./board.php?page=<?php echo $i; ?>&search_type=<?php echo h($search_type); ?>&keyword=<?php echo $url_keyword; ?>"
              class="<?php if($i == $page){ echo 'active'; } ?>">
              <?php echo $i; ?>
            </a>

          <?php
            }
          ?>

          <?php
            // 현재 페이지가 마지막 페이지보다 작으면 다음 버튼 활성화
            if($page < $total_page){
          ?>
            <a href="./board.php?page=<?php echo $page + 1; ?>&search_type=<?php echo h($search_type); ?>&keyword=<?php echo $url_keyword; ?>">다음</a>
          <?php
            }else{
          ?>
            <!-- 마지막 페이지에서는 다음 버튼 비활성화 -->
            <a href="#" class="disabled">다음</a>
          <?php
            }
          ?>

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