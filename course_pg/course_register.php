<?php
  session_start();

  function h($str){
    return htmlspecialchars((string)$str, ENT_QUOTES, "UTF-8");
  }

  $is_login = false;

  if(isset($_SESSION['user_no'])){
    $is_login = true;
  }

    // 희망 수업 시작일은 오늘 기준 3일 뒤부터 선택 가능
  $min_start_date = date('Y-m-d', strtotime('+3 days'));
?>

<!DOCTYPE html>
<html lang="ko">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <title>Hello Korean - Course Register</title>

  <link rel="stylesheet" href="../common/common.css">
  <link rel="stylesheet" href="./course.css">

  <script>
    // JS에서 로그인 여부를 확인하기 위한 값
    let isLogin = <?php echo $is_login ? 'true' : 'false'; ?>;
  </script>

  <script src="./course_register.js" defer></script>
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
              if(isset($_SESSION['user_id'])){
            ?>
              <li><a href="../member_pg/logout.php" class="login_btn">로그아웃</a></li>
              <li><a href="#" class="join_btn"><?php echo h($_SESSION['user_id']); ?>님</a></li>
            <?php
              }else{
            ?>
              <li><a href="../member_pg/login.php" class="login_btn">로그인</a></li>
              <li><a href="../member_pg/join.php" class="join_btn">회원가입</a></li>
            <?php
              }
            ?>
      </ul>
    </nav>

  </div>
</header>

<main>

  <!-- 수강신청 상단 -->
  <section id="course_visual">
    <div class="container">
      <h2>수강신청하기</h2>
      <p>원하는 과정과 수업 시간을 선택해주세요.</p>
    </div>
  </section>

  <!-- 수강신청 내용 -->
  <section id="course_content">
    <div class="container course_layout">

      <!-- 왼쪽 선택 영역 -->
      <div class="course_select_area">

        <form id="course_form" action="./course_confirm.php" method="post">

          <!-- 실제로 다음 단계에서 넘길 값들 -->
          <input type="hidden" name="course_name" id="course_name">
          <input type="hidden" name="course_period" id="course_period">
          <input type="hidden" name="lesson_count" id="lesson_count">
          <input type="hidden" name="first_time" id="first_time">
          <input type="hidden" name="second_time" id="second_time">

          <!-- 과정선택 -->
          <div class="course_section">
            <div class="section_title">
              <h3>과정선택</h3>
            </div>

            <div class="option_grid course_grid">
              <button type="button" class="select_btn" data-group="course" data-value="General Conversation">
                General Conversation
                <span>일상 회화 중심</span>
              </button>

              <button type="button" class="select_btn" data-group="course" data-value="TOPIK Prep">
                TOPIK Prep
                <span>TOPIK 시험 준비</span>
              </button>

              <button type="button" class="select_btn" data-group="course" data-value="Job Interview">
                Job Interview
                <span>취업 면접 준비</span>
              </button>
            </div>
          </div>

          <!-- 수업기간 -->
          <div class="course_section">
            <div class="section_title">
              <h3>수업기간</h3>
            </div>

            <div class="option_grid period_grid">
              <button type="button" class="select_btn" data-group="period" data-value="1개월">
                1개월
              </button>

              <button type="button" class="select_btn" data-group="period" data-value="3개월">
                3개월
              </button>

              <button type="button" class="select_btn" data-group="period" data-value="6개월">
                6개월
              </button>
            </div>
          </div>

          <!-- 수업횟수 -->
          <div class="course_section">
            <div class="section_title">
              <h3>수업횟수</h3>
              <p>회당 수업은 50분입니다.</p>
            </div>

            <div class="option_grid count_grid">
              <button type="button" class="select_btn" data-group="count" data-value="주 2회">
                주 2회
              </button>

              <button type="button" class="select_btn" data-group="count" data-value="주 3회">
                주 3회
              </button>

              <button type="button" class="select_btn" data-group="count" data-value="주 5회">
                주 5회
              </button>
            </div>
          </div>
          
          <!-- 희망 수업 시작일 -->
          <div class="course_section">
            <div class="section_title">
              <h3>희망 수업 시작일</h3>
              <p>실제 시작일은 추후 조정될 수 있습니다.</p>
            </div>

            <input 
              type="date" 
              name="start_date" 
              id="start_date" 
              class="start_date_input"
              min="<?php echo $min_start_date; ?>"
              value="<?php echo $min_start_date; ?>"
            >
          </div>

          <!-- 희망 수업시간 -->
          <div class="course_section">
            <div class="section_title">
              <h3>희망 수업시간</h3>
              <p>희망 수업시간은 총 2개 선택해주세요.</p>
            </div>

            <div class="time_grid">
              <button type="button" class="time_btn" data-time="06:00">06:00</button>
              <button type="button" class="time_btn" data-time="07:00">07:00</button>
              <button type="button" class="time_btn" data-time="08:00">08:00</button>
              <button type="button" class="time_btn" data-time="09:00">09:00</button>
              <button type="button" class="time_btn" data-time="10:00">10:00</button>
              <button type="button" class="time_btn" data-time="11:00">11:00</button>
              <button type="button" class="time_btn" data-time="12:00">12:00</button>
              <button type="button" class="time_btn" data-time="13:00">13:00</button>
              <button type="button" class="time_btn" data-time="14:00">14:00</button>
              <button type="button" class="time_btn" data-time="15:00">15:00</button>
              <button type="button" class="time_btn" data-time="16:00">16:00</button>
              <button type="button" class="time_btn" data-time="17:00">17:00</button>
              <button type="button" class="time_btn" data-time="18:00">18:00</button>
              <button type="button" class="time_btn" data-time="19:00">19:00</button>
              <button type="button" class="time_btn" data-time="20:00">20:00</button>
              <button type="button" class="time_btn" data-time="21:00">21:00</button>
              <button type="button" class="time_btn" data-time="22:00">22:00</button>
              <button type="button" class="time_btn" data-time="23:00">23:00</button>
            </div>
          </div>

          <!-- 어떤 사이트를 보고 오셨나요? -->
          <div class="course_section">
            <div class="section_title">
              <h3>Hello Korean을 어떻게 알고 오셨나요?</h3>
            </div>

            <input type="text" name="referrer" class="referrer_input" placeholder="예) 페이스북, 네이버블로그, 구글검색 등">
          </div>

        </form>

      </div>

        <aside class="apply_island">

        <div class="apply_card">
            <h3>신청 내역</h3>

            <!-- 신청내역 줄 단위 -->
            <div class="summary_list">

            <div class="summary_item">
                <p class="summary_label">코스명</p>
                <p class="summary_value" id="summary_course">선택 전</p>
            </div>

            <div class="summary_item">
                <p class="summary_label">수업기간 / 수업횟수</p>
                <p class="summary_value" id="summary_period_count">선택 전</p>
            </div>

            <div class="summary_item">
                <p class="summary_label">희망 수업시간</p>
                <p class="summary_value" id="summary_times">선택 전</p>
            </div>

            <div class="summary_item total_price_item">
                <p class="summary_label final_price_label">최종 결제금액</p>

                <p class="summary_price" id="summary_price">
                  <span id="price_total">선택 전</span>
                  <span id="price_monthly"></span>
                </p>
            </div>

            </div>

            <div class="apply_notice_box">
            <p>※ 실제 수업 시간은 조정될 수 있습니다.</p>
            </div>

            <button type="button" class="apply_btn" id="apply_btn">
            수강신청하기
            </button>
        </div>

        </aside>

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