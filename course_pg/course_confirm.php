<?php
  session_start();

  function h($str){
    return htmlspecialchars((string)$str, ENT_QUOTES, "UTF-8");
  }

  // 로그인하지 않은 사용자는 접근 불가
  if(!isset($_SESSION['user_no'])){
    echo "
      <script>
        alert('로그인 후 수강신청이 가능합니다.');
        location.href='../member_pg/login.php';
      </script>
    ";
    exit;
  }

  // POST로 넘어온 값 받기
  $course_name = $_POST['course_name'] ?? '';
  $course_period = $_POST['course_period'] ?? '';
  $lesson_count = $_POST['lesson_count'] ?? '';
  $start_date = $_POST['start_date'] ?? '';
  $first_time = $_POST['first_time'] ?? '';
  $second_time = $_POST['second_time'] ?? '';
  $referrer = $_POST['referrer'] ?? '';

  // 값이 비어 있으면 다시 수강신청 페이지로 이동
  if($course_name == '' || $course_period == '' || $lesson_count == '' || $start_date == '' || $first_time == '' || $second_time == ''){
    echo "
      <script>
        alert('수강신청 정보를 다시 선택해주세요.');
        location.href='./course_register.php';
      </script>
    ";
    exit;
  }

    // 수업기간 + 수업횟수별 가격표
    $price_table = array(
      "1개월" => array(
        "주 2회" => array("total" => 250000, "monthly" => "월 250,000원"),
        "주 3회" => array("total" => 337000, "monthly" => "월 337,000원"),
        "주 5회" => array("total" => 490000, "monthly" => "월 490,000원")
      ),

      "3개월" => array(
        "주 2회" => array("total" => 646000, "monthly" => "월 215,600원"),
        "주 3회" => array("total" => 865000, "monthly" => "월 288,640원"),
        "주 5회" => array("total" => 1309000, "monthly" => "월 436,480원")
      ),

      "6개월" => array(
        "주 2회" => array("total" => 1122000, "monthly" => "월 187,000원"),
        "주 3회" => array("total" => 1563000, "monthly" => "월 260,525원"),
        "주 5회" => array("total" => 2279000, "monthly" => "월 379,950원")
      )
    );

    // 선택한 기간/횟수에 맞는 가격 찾기
    $payment_amount = "0원";
    $payment_monthly = "";

    if(isset($price_table[$course_period][$lesson_count])){
      $payment_amount = number_format($price_table[$course_period][$lesson_count]["total"]) . "원";
      $payment_monthly = $price_table[$course_period][$lesson_count]["monthly"];
    }

  // 희망 수업시간
  $hope_time = $first_time . " / " . $second_time;
?>

<!DOCTYPE html>
<html lang="ko">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <title>Hello Korean - Course Confirm</title>

  <link rel="stylesheet" href="../common/common.css">
  <link rel="stylesheet" href="./course.css">

  <script src="./course_confirm.js" defer></script>
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

  <section id="course_visual">
    <div class="container">
      <h2>수강신청 내역보기</h2>
      <p>신청 정보를 확인한 후 결제수단을 선택해주세요.</p>
    </div>
  </section>

  <section id="confirm_content">
    <div class="container">

      <form action="./course_apply_ok.php" method="post" id="confirm_form">

        <!-- 다음 단계에서 DB 저장할 때 넘길 값들 -->
        <input type="hidden" name="course_name" value="<?php echo h($course_name); ?>">
        <input type="hidden" name="course_period" value="<?php echo h($course_period); ?>">
        <input type="hidden" name="lesson_count" value="<?php echo h($lesson_count); ?>">
        <input type="hidden" name="start_date" value="<?php echo h($start_date); ?>">
        <input type="hidden" name="first_time" value="<?php echo h($first_time); ?>">
        <input type="hidden" name="second_time" value="<?php echo h($second_time); ?>">
        <input type="hidden" name="referrer" value="<?php echo h($referrer); ?>">

        <!-- 수강신청 내역 -->
        <div class="confirm_box">

          <h3>수강신청 내역</h3>

          <div class="confirm_table">

            <div class="confirm_row">
              <span>수업과정</span>
              <strong><?php echo h($course_name); ?></strong>
            </div>

            <div class="confirm_row">
              <span>수업기간 / 수업횟수</span>
              <strong><?php echo h($course_period); ?> / <?php echo h($lesson_count); ?></strong>
            </div>

            <div class="confirm_row">
              <span>희망 수업 시작일</span>
              <strong><?php echo h($start_date); ?></strong>
            </div>

            <div class="confirm_row">
              <span>희망 수업시간</span>
              <strong><?php echo h($hope_time); ?></strong>
            </div>

          </div>

        </div>

        <!-- 결제금액 -->
        <div class="confirm_box">

          <h3>최종 결제 금액</h3>

          <div class="price_summary">
            <div>
              <span>수강 금액</span>

              <strong class="course_price_text">
                <?php echo h($payment_amount); ?>

                <?php
                  if($payment_monthly != ''){
                ?>
                  <em>(<?php echo h($payment_monthly); ?>)</em>
                <?php
                  }
                ?>
              </strong>
            </div>

            <div>
              <span>할인 금액</span>
              <strong>0원</strong>
            </div>

            <div class="final_price">
              <span>최종 결제 금액</span>
              <strong><?php echo h($payment_amount); ?></strong>
            </div>
          </div>

        </div>

        <!-- 약관 동의 -->
        <div class="confirm_box">

          <h3>약관 동의</h3>

          <div class="agree_area">
            <label>
              <input type="checkbox" id="agree_terms">
              [필수] 이용약관 동의
            </label>

            <label>
              <input type="checkbox" id="agree_privacy">
              [필수] 개인정보 수집 및 이용 동의
            </label>

            <label>
              <input type="checkbox" id="agree_marketing">
              [선택] 광고성 정보 수신동의
            </label>
          </div>

        </div>

        <!-- 결제수단 -->
        <div class="confirm_box">

          <h3>결제수단 선택</h3>

          <div class="payment_method_area">

            <label class="payment_method">
              <input type="radio" name="payment_method" value="card">
              <span>신용/체크카드</span>
            </label>

            <label class="payment_method">
              <input type="radio" name="payment_method" value="bank">
              <span>무통장입금</span>
            </label>

            <label class="payment_method">
              <input type="radio" name="payment_method" value="naverpay">
              <span>네이버페이</span>
            </label>

            <label class="payment_method">
              <input type="radio" name="payment_method" value="kakaopay">
              <span>카카오페이</span>
            </label>

          </div>

        </div>

        <!-- 버튼 -->
        <div class="confirm_btn_area">
          <a href="./course_register.php" class="confirm_back_btn">다시 선택하기</a>
          <button type="button" class="confirm_pay_btn" id="confirm_pay_btn">결제하기</button>
        </div>

        <!-- 무통장 입금 안내 팝업 -->
        <div class="bank_modal_bg" id="bank_modal_bg">

          <div class="bank_modal">

            <button type="button" class="bank_modal_close" id="bank_modal_close">×</button>

            <h3>무통장 입금 안내</h3>

            <div class="bank_info">
              <p class="bank_name">우리은행</p>
              <p>예금주: 주식회사 글로벌링크</p>
              <p>입금계좌: 1005-004-848255</p>
            </div>

            <div class="bank_input_area">
              <label for="depositor_name">입금자명</label>
              <input type="text" name="depositor_name" id="depositor_name" value="<?php echo h($_SESSION['user_id']); ?>">

              <label for="deposit_date">입금예정일</label>
              <input type="date" name="deposit_date" id="deposit_date" value="<?php echo date('Y-m-d'); ?>">
            </div>

            <button type="button" class="bank_submit_btn" id="bank_submit_btn">
              신청 완료하기
            </button>

          </div>

        </div>

      </form>

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