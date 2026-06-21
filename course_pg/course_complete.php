<?php
session_start();

// 한국 시간 기준
date_default_timezone_set('Asia/Seoul');

function h($str){
    return htmlspecialchars((string)$str, ENT_QUOTES, "UTF-8");
  }

  if(!isset($_SESSION['user_no'])){
    echo "
      <script>
        alert('로그인 후 이용해주세요.');
        location.href='../member_pg/login.php';
      </script>
    ";
    exit;
  }

  // course_apply_ok.php에서 세션으로 저장한 값 받기
  $course_name = $_SESSION['apply_course_name'] ?? '';
  $start_date = $_SESSION['apply_start_date'] ?? '';
  $lesson_time = $_SESSION['apply_lesson_time'] ?? '';

  // 무통장입금 안내 값
  $depositor_name = $_SESSION['apply_depositor_name'] ?? '';
  $deposit_deadline = $_SESSION['apply_deposit_deadline'] ?? '';

  // 혹시 세션값이 비어 있으면 기본값으로 표시
  if($depositor_name == ''){
    $depositor_name = $_SESSION['user_id'] ?? '';
  }

  if($deposit_deadline == ''){
    $deposit_deadline = date('Y년 m월 d일 H:i', strtotime('+48 hours'));
  }

  // 값이 없으면 다시 수강신청 페이지로 이동
  if($course_name == '' || $start_date == '' || $lesson_time == ''){
    echo "
      <script>
        alert('수강신청 정보가 없습니다.');
        location.href='./course_register.php';
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

  <title>Hello Korean - Course Complete</title>

  <link rel="stylesheet" href="../common/common.css">
  <link rel="stylesheet" href="./course.css">
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
        <li><a href="./course_register.php" class="course_register_btn">수강신청</a></li>
        <li><a href="../contact_pg/contact.php">고객센터</a></li>
        <li><a href="../board_pg/board.php" class="board">게시판</a></li>

        <li><a href="../mypage_pg/mypage.php" class="mypage_btn">My page</a></li>
        <li><a href="../member_pg/logout.php" class="login_btn">로그아웃</a></li>
        <li><a href="#" class="join_btn"><?php echo h($_SESSION['user_id']); ?>님</a></li>
      </ul>
    </nav>

  </div>
</header>

<main>

  <section id="complete_content">
    <div class="container">

      <h2>수강신청이 <span>완료</span>되었습니다.</h2>

      <div class="complete_table">

        <div class="complete_row">
          <span>수업과정</span>
          <strong><?php echo h($course_name); ?></strong>
        </div>

        <div class="complete_row">
          <span>희망 수업 시작일</span>
          <strong><?php echo h($start_date); ?></strong>
        </div>

        <div class="complete_row">
          <span>희망 수업 시간</span>
          <strong><?php echo h($lesson_time); ?></strong>
        </div>

    <div class="complete_row bank_complete_row">
      <span>입금 안내</span>

      <div class="bank_complete_info">

        <p class="bank_deadline">
          <?php echo h($deposit_deadline); ?>까지 입금해주셔야 수강신청이 완료됩니다.
        </p>

        <div class="bank_info_box">

          <div class="bank_info_row">
            <em>계좌번호</em>
            <strong>1005-004-848255</strong>
          </div>

          <div class="bank_info_row">
            <em>은행명</em>
            <strong>우리은행</strong>
          </div>

          <div class="bank_info_row">
            <em>예금주</em>
            <strong>주식회사 글로벌링크</strong>
          </div>

          <div class="bank_info_row">
            <em>입금자명</em>
            <strong><?php echo h($depositor_name); ?></strong>
          </div>

        </div>

      </div>
    </div>

      </div>

      <div class="complete_notice">
        <p>Hello Korean을 신청해 주셔서 감사합니다.</p>
        <p>입금 확인 후 담당 매니저를 통해 곧 연락드리겠습니다.</p>
        <p>(월~금 AM 9:00 ~ PM 6:00)</p>
        <br>
        <p>※ 주말 및 공휴일의 경우 순차적으로 평일에 연락드리겠습니다.</p>
      </div>

      <a href="../main_pg/01main_pg.php" class="home_btn">홈으로 가기</a>

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