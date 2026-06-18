<?php
  session_start();

  // DB 연결
  include "../common/db.php";

  function h($str){
    return htmlspecialchars((string)$str, ENT_QUOTES, "UTF-8");
  }

  // 로그인하지 않은 사용자가 직접 마이페이지 주소로 들어오면 로그인 페이지로 이동
  if(!isset($_SESSION['user_id'])){
    echo "
      <script>
        alert('로그인 후 접속해주세요.');
        location.href='../member_pg/login.php';
      </script>
    ";
    exit;
  }

    // 로그인한 회원 번호
  $user_no = $_SESSION['user_no'];

  // 로그인한 회원 정보 가져오기
  $sql = "SELECT * FROM hk_members WHERE no = '$user_no'";
  $result = mysqli_query($db, $sql);

  if($result && mysqli_num_rows($result) > 0){
    $member = mysqli_fetch_array($result, MYSQLI_ASSOC);
  }else{
    echo "
      <script>
        alert('회원 정보를 찾을 수 없습니다.');
        location.href='../main_pg/01main_pg.php';
      </script>
    ";
    exit;
  }

  // 프로필 이미지 파일명
  $profile_img = "";

  if(isset($member['profile_img'])){
    $profile_img = $member['profile_img'];
  }

  // 프로필 이미지 경로
  $profile_path = "../upload/profile/" . $profile_img;

  // 현재는 DB가 없으므로 임시로 수강 여부를 설정
  // true  : 수강중인 강의 있음
  // false : 수강중인 강의 없음
  $has_course = true;
?>

<!DOCTYPE html>
<html lang="ko">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <title>Hello Korean - My page</title>

  <!-- 공통 header/nav/footer CSS -->
  <link rel="stylesheet" href="../common/common.css">

  <!-- 마이페이지 전용 CSS -->
  <link rel="stylesheet" href="./mypage.css">
</head>

<body>

<header>
  <div class="container">

    <!-- 로고 -->
    <a href="../main_pg/01main_pg.php">
      <img src="../main_pg/image/logo.png" alt="main_logo" class="logo">
    </a>

    <nav>
      <ul>
        <!-- Home -->
        <li><a href="../main_pg/01main_pg.php">Home</a></li>

        <!-- 아직 만들지 않은 메뉴 -->
        <li><a href="#">코스</a></li>
        <li><a href="#">강사소개</a></li>
        <li><a href="../course_pg/course_register.php" class="course_register_btn">수강신청</a></li>

        <!-- Contact -->
        <li><a href="../contact_pg/contact.php">고객센터</a></li>

        <!-- Board -->
        <li><a href="../board_pg/board.php" class="board">게시판</a></li>

        <!-- My page -->
        <li>
          <a href="../mypage_pg/mypage.php" class="mypage_btn">My page</a>
        </li>

        <!-- 로그인 상태: 로그아웃 -->
        <li>
          <a href="../member_pg/logout.php" class="login_btn">로그아웃</a>
        </li>

        <!-- 로그인 상태: 아이디 표시 -->
        <li>
          <a href="#" class="join_btn"><?php echo h($_SESSION['user_id']); ?>님</a>
        </li>
      </ul>
    </nav>

  </div>
</header>

<main>

  <!-- 마이페이지 상단 영역 -->
  <section id="mypage_visual">
    <div class="container">
      <h2>My page</h2>
    </div>
  </section>

  <!-- 마이페이지 내용 영역 -->
  <section id="mypage_content">
    <div class="container">

      <!-- 마이페이지 전용 네비게이션 -->
      <div class="mypage_nav">
        <ul>
          <!-- 현재 페이지이므로 active -->
          <li><a href="../course_pg/course_register.php" class="course_register_btn">수강신청</a></li>

          <!-- 수업스케줄 페이지 -->
          <li><a href="./schedule.php">수업스케줄</a></li>

          <!-- 아직 만들지 않을 메뉴는 비활성화 -->
          <li><a href="#" class="disabled" onclick="return false;">쿠폰관리</a></li>
          <li><a href="#" class="disabled" onclick="return false;">결제내역</a></li>

          <!-- 개인정보수정은 나중에 profile.php로 만들 예정 -->
          <li><a href="./profile.php">개인정보수정</a></li>
        </ul>
      </div>

      <!-- 수강신청현황 박스 -->
      <div class="mypage_box">

        <?php
          // 수강 중인 강의가 있을 때
          if($has_course == true){
        ?>

          <!-- 과정명 -->
          <h4 class="course_name">화상 한국어 - 일반과정</h4>

          <!-- 수강 정보 영역 -->
          <div class="course_status_inner">

            <!-- 왼쪽 수업 정보 -->
            <div class="course_detail">

              <dl>
                <dt>기간</dt>
                <dd>2026-06-19 ~ 2026-07-19</dd>

                <dt>시간</dt>
                <dd>09:00 ~ 09:50</dd>

                <dt>스케줄</dt>
                <dd>주 2회 10분</dd>

                <dt>강사</dt>
                <dd>배정 예정</dd>

                <dt>교재</dt>
                <dd>Hello Korean Basic</dd>
              </dl>

              <!-- 진척도 -->
              <div class="progress_row">
                <span>진척도</span>

                <div class="progress_bar">
                  <div class="progress_fill" style="width:0%;"></div>
                </div>

                <strong>0% (0/8)</strong>
              </div>

              <!-- 출석률 -->
              <div class="progress_row">
                <span>출석률</span>

                <div class="progress_bar">
                  <div class="progress_fill" style="width:0%;"></div>
                </div>

                <strong>0% (0/8)</strong>
              </div>

            </div>

            <!-- 오른쪽 프로필 영역 -->
            <div class="course_profile">

            <div class="profile_circle big">

              <?php
                if($profile_img != "" && file_exists($profile_path)){
              ?>

                <img src="<?php echo h($profile_path); ?>" alt="profile image">

              <?php
                }else{
              ?>

                <span>사진없음</span>

              <?php
                }
              ?>

            </div>

            <p><?php echo h($_SESSION['user_id']); ?>님 반갑습니다!</p>

            </div>

          </div>

          <!-- 홀드 신청 표 -->
          <table class="hold_table">
            <tr>
              <th>홀드 신청</th>
              <th>신청 가능 횟수</th>
              <th>사용 횟수</th>
              <th>남은 횟수</th>
            </tr>

            <tr>
              <td></td>
              <td>1회</td>
              <td>0회</td>
              <td><span class="remain_count">1회</span></td>
            </tr>
          </table>

          <!-- 홀드 취소 표 -->
          <table class="hold_table">
            <tr>
              <th>홀드 취소</th>
              <th>취소 가능 횟수</th>
              <th>사용 횟수</th>
              <th>남은 횟수</th>
            </tr>

            <tr>
              <td></td>
              <td>1회</td>
              <td>0회</td>
              <td><span class="remain_count">1회</span></td>
            </tr>
          </table>

        <?php
          // 수강 중인 강의가 없을 때
          }else{
        ?>

          <div class="empty_course">

            <div class="empty_icon">
              HK
            </div>

            <h4>현재 수강중인 강의가 없습니다.</h4>

            <p>
              수강신청 후 나의 수업 일정과 학습 정보를 확인할 수 있습니다.
            </p>

            <a href="#" class="course_apply_btn" onclick="alert('수강신청 페이지는 추후 연결 예정입니다.'); return false;">
              수강신청하기
            </a>

          </div>

        <?php
          }
        ?>

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