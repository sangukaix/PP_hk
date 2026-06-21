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

  // GET으로 결제번호 받기
  $payment_no = $_GET['payment_no'] ?? '';
  $payment_no = (int)$payment_no;

  if($payment_no < 1){
    echo "
      <script>
        alert('잘못된 접근입니다.');
        location.href='./admin.php?tab=lesson';
      </script>
    ";
    exit;
  }

  // 수강생 기본정보 가져오기
  $info_sql = "
    SELECT
      p.*,
      m.user_id,
      m.user_name,
      m.phone,
      m.email,
      m.date AS join_date
    FROM hk_payments p
    LEFT JOIN hk_members m
    ON p.member_no = m.no
    WHERE p.no = '$payment_no'
  ";

  $info_result = mysqli_query($db, $info_sql);
  $info = mysqli_fetch_array($info_result, MYSQLI_ASSOC);

  if(!$info){
    echo "
      <script>
        alert('수강생 정보를 찾을 수 없습니다.');
        location.href='./admin.php?tab=lesson';
      </script>
    ";
    exit;
  }

 // 수업일정 목록 가져오기
    $schedule_sql = "
      SELECT
        s.*,
        origin.lesson_date AS makeup_origin_date
      FROM hk_lesson_schedule s
      LEFT JOIN hk_lesson_schedule origin
      ON s.makeup_for_lesson_no = origin.no
      WHERE s.payment_no = '$payment_no'
      ORDER BY s.lesson_date ASC, s.no ASC
    ";
  $schedule_result = mysqli_query($db, $schedule_sql);

  // 수업 요약정보 가져오기
  $summary_sql = "
    SELECT
      MIN(lesson_date) AS lesson_start_date,
      MAX(lesson_date) AS lesson_end_date,
      GROUP_CONCAT(DISTINCT lesson_day ORDER BY FIELD(lesson_day, '월', '화', '수', '목', '금', '토', '일') SEPARATOR ', ') AS lesson_days,
      GROUP_CONCAT(DISTINCT lesson_time ORDER BY lesson_time SEPARATOR ', ') AS lesson_times,
      MAX(teacher_name) AS teacher_name,
      COUNT(no) AS total_count,
      SUM(CASE WHEN attendance_status = '출석' THEN 1 ELSE 0 END) AS attendance_count,
      SUM(CASE WHEN attendance_status = '결석' THEN 1 ELSE 0 END) AS absence_count,
      SUM(CASE WHEN attendance_status = '홀드' THEN 1 ELSE 0 END) AS hold_count
    FROM hk_lesson_schedule
    WHERE payment_no = '$payment_no'
  ";

  $summary_result = mysqli_query($db, $summary_sql);
  $summary = mysqli_fetch_array($summary_result, MYSQLI_ASSOC);

  $total_count = (int)$summary['total_count'];
  $attendance_count = (int)$summary['attendance_count'];
  $absence_count = (int)$summary['absence_count'];
  $hold_count = (int)$summary['hold_count'];

  // 남은횟수 계산
  // 출석/결석은 수업을 사용한 것으로 보고 차감
  // 홀드는 아직 수업을 사용하지 않은 것으로 보고 차감하지 않음
  $remain_count = $total_count - $attendance_count - $absence_count;

  if($remain_count < 0){
    $remain_count = 0;
  }
?>

<!DOCTYPE html>
<html lang="ko">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <title>Hello Korean - 수강생 상세관리</title>

  <link rel="stylesheet" href="./admin.css">
</head>

<body>

<header class="admin_header">
  <div class="admin_container">

    <h1>Hello Korean Admin</h1>

    <div class="admin_top_btn">
      <span><?php echo h($_SESSION['admin_id']); ?> 관리자님</span>
      <a href="./admin_logout.php">관리자 로그아웃</a>
    </div>

  </div>
</header>

<main class="admin_main">
  <div class="admin_container">

    <section class="admin_title">

      <div style="margin-bottom:12px;">
        <a
          href="./admin.php?tab=lesson"
          class="admin_back_btn"
          style="padding:8px 14px; font-size:13px;"
        >
          ← 수강생관리로 돌아가기
        </a>
      </div>

      <h2>수강생 상세관리</h2>
      <p>수업 일정별 출석, 결석, 홀드 상태를 관리합니다.</p>

    </section>


<!-- 수강생 기본정보 -->
<section class="lesson_compact_box">

      <div class="lesson_compact_header" style="display:flex; justify-content:space-between; align-items:center; gap:15px;">

        <div>
          <h3>수강생 정보</h3>
          <p>수강생 기본정보와 수업 현황을 간략하게 확인합니다.</p>
        </div>

        <a
          href="./student_page_login.php?payment_no=<?php echo h($payment_no); ?>"
          class="admin_small_btn"
          target="_blank"
        >
          학생페이지로 이동
        </a>

      </div>

  <div class="lesson_simple_grid">

    <div>
      <em>학생명</em>
      <span><?php echo h($info['user_name']); ?></span>
    </div>

    <div>
      <em>ID</em>
      <span><?php echo h($info['user_id']); ?></span>
    </div>

    <div>
      <em>전화번호</em>
      <span><?php echo h($info['phone']); ?></span>
    </div>

    <div>
      <em>이메일</em>
      <span><?php echo h($info['email']); ?></span>
    </div>

    <div>
      <em>Course</em>
      <span><?php echo h($info['course_name']); ?></span>
    </div>

    <div>
      <em>수강상태</em>
      <span><?php echo h($info['lesson_status']); ?></span>
    </div>

    <div>
      <em>강사명</em>
      <span><?php echo h($summary['teacher_name']); ?></span>
    </div>

    <div>
      <em>요일 / 시간</em>
      <span>
        <?php echo h($summary['lesson_days']); ?>
        /
        <?php echo h($summary['lesson_times']); ?>
      </span>
    </div>

    <div>
      <em>수업기간</em>
      <span>
        <?php echo h($summary['lesson_start_date']); ?>
        ~
        <?php echo h($summary['lesson_end_date']); ?>
      </span>
    </div>

    <div>
      <em>총횟수</em>
      <span><?php echo h($total_count); ?>회</span>
    </div>

    <div>
      <em>남은횟수</em>
      <span><?php echo h($remain_count); ?>회</span>
    </div>

    <div>
      <em>출결현황</em>
      <span>
        출석 <?php echo h($attendance_count); ?> /
        결석 <?php echo h($absence_count); ?> /
        홀드 <?php echo h($hold_count); ?>
      </span>
    </div>

  </div>

</section>

<!-- 수업 입장 링크 관리 -->
    <!-- 수업 입장 링크 / 홀드 횟수 설정 -->
    <div class="admin_info_box" style="margin-top:20px; padding:18px 20px;">

      <!-- 수업 입장 링크 -->
      <form 
        action="./lesson_zoom_update_ok.php" 
        method="post" 
        style="display:flex; align-items:center; gap:8px; margin-bottom:14px;"
      >
        <input type="hidden" name="payment_no" value="<?php echo h($payment_no); ?>">

        <strong style="font-size:18px; min-width:120px;">수업 입장 링크</strong>

        <input
          type="text"
          name="zoom_link"
          value="<?php echo h($info['zoom_link'] ?? ''); ?>"
          placeholder="예: https://zoom.us/j/..."
          style="width:260px; padding:5px 7px;"
        >

        <button type="submit" style="padding:5px 10px;">
          링크 저장
        </button>
      </form>


      <!-- 홀드 횟수 설정 -->
      <form 
        action="./lesson_hold_limit_update_ok.php" 
        method="post" 
        style="display:flex; align-items:center; gap:8px;"
      >
        <input type="hidden" name="payment_no" value="<?php echo h($payment_no); ?>">

        <strong style="font-size:18px; min-width:120px;">홀드 횟수 설정</strong>

        <span style="font-size:15px;">홀드 가능 횟수</span>

        <input 
          type="number" 
          name="hold_limit" 
          min="0"
          value="<?php echo h($info['hold_limit'] ?? 1); ?>"
          style="width:70px; padding:5px 7px;"
        >

        <span style="font-size:15px;">회</span>

        <span style="font-size:15px; margin-left:10px;">홀드취소 가능 횟수</span>

        <input 
          type="number" 
          name="hold_cancel_limit" 
          min="0"
          value="<?php echo h($info['hold_cancel_limit'] ?? 1); ?>"
          style="width:70px; padding:5px 7px;"
        >

        <span style="font-size:15px;">회</span>

        <button type="submit" style="padding:5px 10px; margin-left:10px;">
          홀드 횟수 저장
        </button>
      </form>

    </div>

<!-- 수업일정 상세 -->
        <section class="lesson_register_box">

        <div class="lesson_register_title lesson_detail_title_area">
            <div>
            <h3>수업일정 상세</h3>
            <p>각 수업일의 상태를 예정 / 출석 / 결석 / 홀드 중에서 선택한 뒤 저장합니다.</p>
            </div>
        </div>

        <!-- 수업 1회 추가 영역 -->
        <div class="lesson_add_box">
            <form action="./lesson_add_ok.php" method="post" onsubmit="return confirm('수업을 1회 추가하시겠습니까?');">

            <input type="hidden" name="payment_no" value="<?php echo h($payment_no); ?>">

            <strong>수업 1회 추가</strong>

            <label>
                <input type="radio" name="add_type" value="auto" checked>
                자동 추가
            </label>

            <span class="lesson_add_help">
                마지막 수업 다음 패턴으로 1회 추가
            </span>

            <label>
                <input type="radio" name="add_type" value="custom">
                직접 추가
            </label>

            <input type="date" name="custom_date" class="lesson_add_date">

            <select name="custom_time" class="lesson_add_time">
                <option value="">시간 선택</option>

                <?php
                for($hour = 6; $hour <= 24; $hour++){

                    if($hour < 10){
                    $time_text = "0" . $hour . ":00";
                    }else{
                    $time_text = $hour . ":00";
                    }
                ?>

                <option value="<?php echo h($time_text); ?>">
                    <?php echo h($time_text); ?>
                </option>

                <?php
                }
                ?>
            </select>

            <button type="submit" class="lesson_add_btn">수업 추가</button>

            </form>
        </div>

        <div class="admin_table_area">
            <table class="admin_table lesson_detail_table">
            <caption>수업일정 상세</caption>

            <thead>
                <tr>
                  <th>회차</th>
                  <th>수업일</th>
                  <th>요일</th>
                  <th>수업시간</th>
                  <th>강사명</th>
                  <th>상태</th>
                  <th>관리</th>
                </tr>
            </thead>

            <tbody>
                <?php
                if($schedule_result && mysqli_num_rows($schedule_result) > 0){

                    $round = 1;

                    while($schedule = mysqli_fetch_array($schedule_result, MYSQLI_ASSOC)){
                ?>

                <tr>
                    <td><?php echo h($round); ?>회차</td>
                    <td>
                      <?php echo h($schedule['lesson_date']); ?>

                      <?php
                        if(($schedule['lesson_type'] ?? '') == "홀드 보강수업"){

                          $origin_date_text = "";

                          if(($schedule['makeup_origin_date'] ?? '') != ""){
                            $origin_date_text = date("Y.m.d", strtotime($schedule['makeup_origin_date']));
                          }
                      ?>
                        <div style="margin-top:5px;">
                          <span style="display:inline-block; padding:3px 8px; background-color:#6c63ff; color:#fff; border-radius:14px; font-size:11px;">
                            <?php echo h($origin_date_text); ?>의 보강수업
                          </span>
                        </div>
                      <?php
                        }
                      ?>
                    </td>
                    <td><?php echo h($schedule['lesson_day']); ?></td>
                    <td><?php echo h($schedule['lesson_time']); ?></td>
                    <td><?php echo h($schedule['teacher_name']); ?></td>

                    <td>
                    <form
                        id="status_form_<?php echo h($schedule['no']); ?>"
                        action="./lesson_status_update_ok.php"
                        method="post"
                        onsubmit="return confirm('수업상태를 저장하시겠습니까?');"
                    >
                        <input type="hidden" name="schedule_no" value="<?php echo h($schedule['no']); ?>">
                        <input type="hidden" name="payment_no" value="<?php echo h($payment_no); ?>">

                        <select name="attendance_status" class="admin_select">
                        <option value="예정" <?php if($schedule['attendance_status'] == '예정'){ echo 'selected'; } ?>>예정</option>
                        <option value="출석" <?php if($schedule['attendance_status'] == '출석'){ echo 'selected'; } ?>>출석</option>
                        <option value="결석" <?php if($schedule['attendance_status'] == '결석'){ echo 'selected'; } ?>>결석</option>
                        <option value="홀드" <?php if($schedule['attendance_status'] == '홀드'){ echo 'selected'; } ?>>홀드</option>
                        </select>
                    </form>
                    </td>

                      <td>
                      <button type="submit" class="admin_small_btn" form="status_form_<?php echo h($schedule['no']); ?>">
                          상태저장
                      </button>

                    <form
                        action="./lesson_delete_ok.php"
                        method="post"
                        class="lesson_delete_form"
                        onsubmit="return confirm('이 수업일정을 삭제하시겠습니까?');"
                    >
                        <input type="hidden" name="schedule_no" value="<?php echo h($schedule['no']); ?>">
                        <input type="hidden" name="payment_no" value="<?php echo h($payment_no); ?>">

                        <button type="submit" class="lesson_delete_btn">삭제</button>
                    </form>
                    </td>
                </tr>

                <?php
                    $round++;
                    }
                }else{
                ?>

                <tr>
                    <td colspan="7">등록된 수업일정이 없습니다.</td>
                </tr>

                <?php
                }
                ?>
            </tbody>
            </table>
        </div>

        </section>

    <div class="lesson_submit_area">
      <a href="./admin.php?tab=lesson" class="admin_back_btn">수강생관리로 돌아가기</a>
    </div>

  </div>
</main>

</body>
</html>