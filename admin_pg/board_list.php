<?php
  // 관리자 로그인 체크 파일 불러오기
  // 관리자 로그인이 안 되어 있으면 admin_login.php로 이동시킴
  include "./admin_check.php";
  
  // 한글 깨짐 방지
  header("Content-Type:text/html; charset=utf-8");

  // PHP 오류를 화면에 보여주기
  // 개발 중에는 백지 화면 원인을 찾기 위해 켜두면 좋음
  error_reporting(E_ALL);
  ini_set("display_errors", 1);

  // DB 연결 파일 불러오기
  // ../ 는 현재 admin_pg 폴더에서 한 단계 위로 올라간다는 뜻
  include "../common/db.php";

  // hk_board 테이블의 문의글을 최신 글부터 가져오기
  $sql = "SELECT * FROM hk_board ORDER BY no DESC";

  // SQL 실행
  $result = mysqli_query($db, $sql);

  // HTML 출력 시 특수문자를 안전하게 바꿔주는 함수
  // 사용자가 입력한 글에 태그가 있어도 화면이 깨지지 않게 함
  function h($str){
    return htmlspecialchars($str, ENT_QUOTES, "UTF-8");
  }

  // 긴 문의 내용을 짧게 줄여서 보여주는 함수
  function short_text($str){
    // HTML 태그 제거
    $str = strip_tags($str);

    // mb_strimwidth 함수가 있으면 한글도 안전하게 자름
    if(function_exists("mb_strimwidth")){
      return mb_strimwidth($str, 0, 80, "...", "UTF-8");
    }

    // 혹시 mb_strimwidth가 없을 경우 기본 substr 사용
    return substr($str, 0, 80) . "...";
  }
?>

<!DOCTYPE html>
<html lang="ko">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <title>Hello Korean Admin - 문의글 관리</title>

  <!-- 관리자 페이지 전용 CSS -->
  <link rel="stylesheet" href="./admin.css">
</head>

<body>

<header class="admin_header">
  <div class="admin_container">

    <!-- 관리자 페이지 제목 -->
    <h1>Hello Korean Admin</h1>

    <!-- 관리자 상단 버튼 -->
    <div class="admin_top_btn">
      <a href="./admin.php">관리자 홈</a>
      <a href="../main_pg/01main_pg.php">사이트로 이동</a>
    </div>

  </div>
</header>

<nav class="admin_nav">
  <div class="admin_container">
    <ul>
      <!-- 관리자 홈 -->
      <li><a href="./admin.php">관리자 홈</a></li>

      <!-- 회원 관리 -->
      <li><a href="./member_list.php">회원 관리</a></li>

      <!-- 현재 페이지: 문의글 관리 -->
      <li><a href="./board_list.php" class="active">문의글 관리</a></li>
    </ul>
  </div>
</nav>

<main class="admin_main">
  <div class="admin_container">

    <section class="admin_title">
      <h2>문의글 관리</h2>
      <p>사용자가 작성한 문의글을 확인하는 페이지입니다.</p>
    </section>

    <section class="admin_table_area">

      <table class="admin_table">
        <caption>문의글 목록</caption>

        <thead>
          <tr>
            <th>번호</th>
            <th>작성자</th>
            <th>상담유형</th>
            <th>제목</th>
            <th>내용</th>
            <th>상태</th>
            <th>작성일</th>
          </tr>
        </thead>

        <tbody>

        <?php
          // SQL 실행 결과가 있고, 가져온 글이 1개 이상 있을 때
          if($result && mysqli_num_rows($result) > 0){

            // 문의글을 한 줄씩 꺼내서 화면에 출력
            while($row = mysqli_fetch_array($result, MYSQLI_ASSOC)){
        ?>

          <tr>
            <!-- 문의글 번호 -->
            <td><?php echo h($row['no']); ?></td>

            <!-- 작성자 -->
            <td><?php echo h($row['writer']); ?></td>

            <!-- 상담 유형 -->
            <td><?php echo h($row['category']); ?></td>

            <!-- 제목 -->
            <td class="text_left"><?php echo h($row['title']); ?></td>

            <!-- 문의 내용은 너무 길 수 있으므로 짧게 잘라서 표시 -->
            <td class="text_left"><?php echo h(short_text($row['message'])); ?></td>

            <!-- 답변 상태 -->
            <td><?php echo h($row['status']); ?></td>

            <!-- 작성일 -->
            <td><?php echo h($row['date']); ?></td>
          </tr>

        <?php
            }
          }else{
        ?>

          <!-- 등록된 문의글이 없을 때 표시 -->
          <tr>
            <td colspan="7">등록된 문의글이 없습니다.</td>
          </tr>

        <?php
          }
        ?>

        </tbody>
      </table>

    </section>

  </div>
</main>

</body>
</html>

<?php
  // DB 연결 종료
  mysqli_close($db);
?>