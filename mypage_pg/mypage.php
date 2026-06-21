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
  $user_no = (int)$_SESSION['user_no'];

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

    // 로그인한 학생이 수강신청은 했지만 아직 수업등록 전인지 확인
      $apply_sql = "
        SELECT *
        FROM hk_payments
        WHERE member_no = '$user_no'
        AND lesson_status = '등록필요'
        ORDER BY no DESC
        LIMIT 1
      ";

      $apply_result = mysqli_query($db, $apply_sql);

      $has_apply = false;
      $apply = null;

      if($apply_result && mysqli_num_rows($apply_result) > 0){
        $apply = mysqli_fetch_array($apply_result, MYSQLI_ASSOC);
        $has_apply = true;
      } 


  // 로그인한 학생의 수강중 강의 정보 가져오기
  $course_sql = "
    SELECT
      s.payment_no,

      p.course_name,
      p.total_period,
      p.lesson_status,
      p.hold_limit,
      p.hold_cancel_limit,

      MIN(s.lesson_date) AS lesson_start_date,
      MAX(s.lesson_date) AS lesson_end_date,

      GROUP_CONCAT(DISTINCT s.lesson_day ORDER BY FIELD(s.lesson_day, '월', '화', '수', '목', '금', '토', '일') SEPARATOR ', ') AS lesson_days,
      GROUP_CONCAT(DISTINCT s.lesson_time ORDER BY s.lesson_time SEPARATOR ', ') AS lesson_times,
      GROUP_CONCAT(DISTINCT s.teacher_name SEPARATOR ', ') AS teacher_names,

      COUNT(s.no) AS total_count,

      SUM(CASE WHEN s.attendance_status = '출석' THEN 1 ELSE 0 END) AS attendance_count,
      SUM(CASE WHEN s.attendance_status = '결석' THEN 1 ELSE 0 END) AS absence_count,

      (
        SELECT COUNT(*)
        FROM hk_hold_requests hr
        WHERE hr.payment_no = s.payment_no
        AND hr.member_no = '$user_no'
        AND hr.request_type = '홀드신청'
        AND hr.request_status = '승인'
      ) AS hold_count,

      (
        SELECT COUNT(*)
        FROM hk_hold_requests hr
        WHERE hr.payment_no = s.payment_no
        AND hr.member_no = '$user_no'
        AND hr.request_type = '홀드취소'
        AND hr.request_status = '승인'
      ) AS hold_cancel_count

    FROM hk_lesson_schedule s

    LEFT JOIN hk_payments p
    ON s.payment_no = p.no

    WHERE s.member_no = '$user_no'
    AND p.lesson_status = '수강중'

    GROUP BY
      s.payment_no,
      p.course_name,
      p.total_period,
      p.lesson_status,
      p.hold_limit,
      p.hold_cancel_limit

    ORDER BY lesson_start_date DESC
    LIMIT 1
  ";

  $course_result = mysqli_query($db, $course_sql);

  // 기본값
  $has_course = false;
  $course = null;

  $total_count = 0;
  $attendance_count = 0;
  $absence_count = 0;
  $hold_count = 0;
  $hold_cancel_count = 0;

  $used_count = 0;
  $remain_count = 0;

  $hold_limit = 1;
  $hold_cancel_limit = 1;

  $hold_remain = 0;
  $hold_cancel_remain = 0;

  $progress_percent = 0;
  $attendance_percent = 0;


  if($course_result && mysqli_num_rows($course_result) > 0){

    $course = mysqli_fetch_array($course_result, MYSQLI_ASSOC);
    $has_course = true;

    // 총 수업횟수
    $total_count = (int)$course['total_count'];

    // 출석 / 결석 / 홀드 횟수
    $attendance_count = (int)$course['attendance_count'];
    $absence_count = (int)$course['absence_count'];
    $hold_count = (int)$course['hold_count'];
    $hold_cancel_count = (int)$course['hold_cancel_count'];

    // DB에 저장된 홀드/홀드취소 가능 횟수
    if($course['hold_limit'] !== null && $course['hold_limit'] !== ""){
      $hold_limit = (int)$course['hold_limit'];
    }

    if($course['hold_cancel_limit'] !== null && $course['hold_cancel_limit'] !== ""){
      $hold_cancel_limit = (int)$course['hold_cancel_limit'];
    }

    // 출석과 결석은 수업을 사용한 것으로 계산
    $used_count = $attendance_count + $absence_count;

    // 남은 수업횟수
    $remain_count = $total_count - $used_count;

    if($remain_count < 0){
      $remain_count = 0;
    }

    // 진척도: 사용한 수업 / 전체 수업
    if($total_count > 0){
      $progress_percent = round(($used_count / $total_count) * 100);
    }

    // 출석률: 출석 / 출석+결석
    if($used_count > 0){
      $attendance_percent = round(($attendance_count / $used_count) * 100);
    }

    // 홀드 남은 횟수
    $hold_remain = $hold_limit - $hold_count;

    if($hold_remain < 0){
      $hold_remain = 0;
    }

    // 홀드취소 남은 횟수
    $hold_cancel_remain = $hold_cancel_limit - $hold_cancel_count;

    if($hold_cancel_remain < 0){
      $hold_cancel_remain = 0;
    }
  }
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
          <li><a href="./mypage.php" class="active">수강신청현황</a></li>
          <li><a href="./schedule.php">수업스케줄</a></li>
          <li><a href="#" class="disabled" onclick="return false;">쿠폰관리</a></li>
          <li><a href="#" class="disabled" onclick="return false;">결제내역</a></li>
          <li><a href="./profile.php">개인정보수정</a></li>
        </ul>
      </div>

      <!-- 수강신청현황 박스 -->
      <div class="mypage_box profile_box">

        <?php
          // 수강 중인 강의가 있을 때
          if($has_course == true){
        ?>

          <!-- 과정명 -->
          <h4 class="course_name"><?php echo h($course['course_name']); ?></h4>

          <!-- 수강 정보 영역 -->
          <div class="course_status_inner">

            <!-- 왼쪽 수업 정보 -->
            <div class="course_detail">

              <dl>
                <dt>기간</dt>
                <dd>
                  <?php echo h($course['lesson_start_date']); ?>
                  ~
                  <?php echo h($course['lesson_end_date']); ?>
                </dd>

                <dt>시간</dt>
                <dd><?php echo h($course['lesson_times']); ?></dd>

                <dt>스케줄</dt>
                <dd>
                  <?php echo h($course['lesson_days']); ?>요일 /
                  <?php echo h($course['total_period']); ?>
                </dd>

                <dt>강사</dt>
                <dd><?php echo h($course['teacher_names']); ?></dd>
              </dl>

              <!-- 진척도 -->
              <div class="progress_row">
                <span>진척도</span>

                <div class="progress_bar">
                  <div class="progress_fill" style="width:<?php echo h($progress_percent); ?>%;"></div>
                </div>

                <strong>
                  <?php echo h($progress_percent); ?>%
                  (<?php echo h($used_count); ?>/<?php echo h($total_count); ?>)
                </strong>
              </div>

              <!-- 출석률 -->
              <div class="progress_row">
                <span>출석률</span>

                <div class="progress_bar">
                  <div class="progress_fill" style="width:<?php echo h($attendance_percent); ?>%;"></div>
                </div>

                <strong>
                  <?php echo h($attendance_percent); ?>%
                  (<?php echo h($attendance_count); ?>/<?php echo h($used_count); ?>)
                </strong>
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
              <td><?php echo h($hold_limit); ?>회</td>
              <td><?php echo h($hold_count); ?>회</td>
              <td><span class="remain_count"><?php echo h($hold_remain); ?>회</span></td>
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
              <td><?php echo h($hold_cancel_limit); ?>회</td>
              <td><?php echo h($hold_cancel_count); ?>회</td>
              <td><span class="remain_count"><?php echo h($hold_cancel_remain); ?>회</span></td>
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

            <?php
              if($has_apply == true){
            ?>

              <h4>수강신청이 접수되었습니다.</h4>

              <p>
                업무일 기준 24시간내에 강의 정보를 확인하실 수 있습니다.
              </p>

            <?php
              }else{
            ?>

              <h4>현재 수강중인 강의가 없습니다.</h4>

              <p>
                수강신청 후 나의 수업 일정과 학습 정보를 확인할 수 있습니다.
              </p>

              <a href="../course_pg/course_register.php" class="course_apply_btn">
                수강신청하기
              </a>

            <?php
              }
            ?>

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