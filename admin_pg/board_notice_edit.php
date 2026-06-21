<?php
  // 관리자 로그인 체크
  include "./admin_check.php";

  // 한글 깨짐 방지
  header("Content-Type:text/html; charset=utf-8");

  // DB 연결
  include "../common/db.php";

  // 화면 출력용 특수문자 처리 함수
  function h($str){
    return htmlspecialchars((string)$str, ENT_QUOTES, "UTF-8");
  }

  // 문의글 필독 공지사항 가져오기
  $notice_sql = "SELECT * FROM hk_board_notice ORDER BY no ASC LIMIT 1";
  $notice_result = mysqli_query($db, $notice_sql);
  $notice = mysqli_fetch_array($notice_result, MYSQLI_ASSOC);

  // 혹시 공지사항이 없을 때 기본값
  if(!$notice){
    $notice = [
      'no' => 1,
      'title' => '문의글 작성시 필독',
      'content' => ''
    ];
  }
?>

<!DOCTYPE html>
<html lang="ko">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <title>Hello Korean - 공지사항 수정</title>

  <link rel="stylesheet" href="./admin.css">
</head>

<body>

<header class="admin_header">
  <div class="admin_container">

    <h1>Hello Korean Admin</h1>

    <div class="admin_top_btn">
      <span><?php echo h($_SESSION['admin_id']); ?> 관리자님</span>
      <a href="../main_pg/01main_pg.php">사이트로 이동</a>
      <a href="./admin_logout.php">관리자 로그아웃</a>
    </div>

  </div>
</header>

<main class="admin_main">
  <div class="admin_container">

    <div class="admin_panel active">

      <div class="admin_panel_title">
        <h3>문의글 작성시 필독 공지사항 수정</h3>
        <p>게시판 상단의 필독 공지사항 내용을 수정합니다.</p>
      </div>

      <div class="admin_answer_area">

        <form action="./board_notice_update_ok.php" method="post">

          <input type="hidden" name="no" value="<?php echo h($notice['no']); ?>">

          <div style="margin-bottom: 15px;">
            <label style="display:block; margin-bottom:8px; font-weight:bold;">
              제목
            </label>

            <input
              type="text"
              name="title"
              value="<?php echo h($notice['title']); ?>"
              style="width:100%; height:42px; padding:0 12px; box-sizing:border-box; border:1px solid #ccc; font-size:15px;"
            >
          </div>

          <div>
            <label style="display:block; margin-bottom:8px; font-weight:bold;">
              내용
            </label>

            <textarea name="content"><?php echo h($notice['content']); ?></textarea>
          </div>

          <div class="admin_answer_btn">
            <button type="submit">공지사항 저장</button>
            <a href="../board_pg/board_notice.php" target="_blank">사용자 화면 보기</a>
            <a href="./admin.php?tab=board">문의글관리로 돌아가기</a>
          </div>

        </form>

      </div>

    </div>

  </div>
</main>

</body>
</html>

<?php
  mysqli_close($db);
?>