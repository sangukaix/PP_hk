<?php
  // 관리자 로그인 체크 파일 불러오기
  // 관리자 로그인이 안 되어 있으면 admin_login.php로 이동
  include "./admin_check.php";

  // 한글 깨짐 방지
  header("Content-Type:text/html; charset=utf-8");

  // DB 연결 파일 불러오기
  // 회원 목록, 문의글 목록, 결제회원 목록을 admin.php 안에서 보여주기 위해 필요함
  include "../common/db.php";

  // ==============================
  // 회원관리 페이지네이션
  // ==============================

  // 현재 회원관리 페이지 번호 받기
  // 주소에 member_page 값이 없으면 기본값은 1페이지
  $member_page = $_GET['member_page'] ?? 1;

  // 페이지 번호는 숫자로 사용해야 하므로 정수로 변환
  $member_page = (int)$member_page;

  // 페이지 번호가 1보다 작으면 1로 고정
  if($member_page < 1){
    $member_page = 1;
  }

  // 한 페이지에 보여줄 회원 수
  $member_list_num = 10;

  // 현재 페이지에서 몇 번째 회원부터 가져올지 계산
  // 1페이지: 0번부터, 2페이지: 10번부터
  $member_start = ($member_page - 1) * $member_list_num;

  // 전체 회원 수 구하기
  $member_count_sql = "SELECT COUNT(*) AS total FROM hk_members";
  $member_count_result = mysqli_query($db, $member_count_sql);
  $member_count_row = mysqli_fetch_array($member_count_result, MYSQLI_ASSOC);

  // 전체 회원 수
  $member_total_count = (int)$member_count_row['total'];

  // 전체 회원 페이지 수 계산
  $member_total_page = ceil($member_total_count / $member_list_num);

  // 회원이 없어도 최소 1페이지로 처리
  if($member_total_page < 1){
    $member_total_page = 1;
  }

  // 현재 페이지에 보여줄 회원만 가져오기
  $member_sql = "SELECT * FROM hk_members
                ORDER BY no DESC
                LIMIT $member_start, $member_list_num";

  // SQL 실행
  $member_result = mysqli_query($db, $member_sql);


  // ==============================
  // 문의글관리 페이지네이션
  // ==============================

  // 현재 문의글관리 페이지 번호 받기
  // 주소에 board_page 값이 없으면 기본값은 1페이지
  $board_page = $_GET['board_page'] ?? 1;

  // 페이지 번호는 숫자로 사용해야 하므로 정수로 변환
  $board_page = (int)$board_page;

  // 페이지 번호가 1보다 작으면 1로 고정
  if($board_page < 1){
    $board_page = 1;
  }

  // 한 페이지에 보여줄 문의글 수
  $board_list_num = 10;

  // 현재 페이지에서 몇 번째 문의글부터 가져올지 계산
  // 1페이지: 0번부터, 2페이지: 10번부터
  $board_start = ($board_page - 1) * $board_list_num;

  // 전체 문의글 수 구하기
  $board_count_sql = "SELECT COUNT(*) AS total FROM hk_board";
  $board_count_result = mysqli_query($db, $board_count_sql);
  $board_count_row = mysqli_fetch_array($board_count_result, MYSQLI_ASSOC);

  // 전체 문의글 수
  $board_total_count = (int)$board_count_row['total'];

  // 전체 문의글 페이지 수 계산
  $board_total_page = ceil($board_total_count / $board_list_num);

  // 문의글이 없어도 최소 1페이지로 처리
  if($board_total_page < 1){
    $board_total_page = 1;
  }

  // 현재 페이지에 보여줄 문의글만 가져오기
  $board_sql = "SELECT * FROM hk_board
                ORDER BY no DESC
                LIMIT $board_start, $board_list_num";

  // SQL 실행
  $board_result = mysqli_query($db, $board_sql);


  // ==============================
  // 결제회원 목록
  // ==============================

  // 결제회원 목록 가져오기
  // hk_payments와 hk_members를 연결해서 회원 아이디와 이름도 같이 가져옴
  $payment_sql = "
    SELECT
      p.*,
      m.user_id,
      m.user_name
    FROM hk_payments p
    LEFT JOIN hk_members m
    ON p.member_no = m.no
    ORDER BY p.no DESC
  ";

  $payment_result = mysqli_query($db, $payment_sql);

      // ==============================
    // 수강생관리 목록
    // ==============================

    // 수강등록이 완료된 학생 목록 가져오기
    // hk_lesson_schedule에 저장된 수업일정을 payment_no 기준으로 묶어서 보여줌
    $lesson_sql = "
      SELECT
        s.payment_no,
        s.member_no,
        s.teacher_name,

        m.user_id,
        m.user_name,
        m.phone,
        m.email,
        m.date AS join_date,

        p.course_name,
        p.lesson_status,
        p.total_period,

        MIN(s.lesson_date) AS lesson_start_date,
        MAX(s.lesson_date) AS lesson_end_date,

        GROUP_CONCAT(DISTINCT s.lesson_day ORDER BY FIELD(s.lesson_day, '월', '화', '수', '목', '금', '토', '일') SEPARATOR ', ') AS lesson_days,
        GROUP_CONCAT(DISTINCT s.lesson_time ORDER BY s.lesson_time SEPARATOR ', ') AS lesson_times,

        COUNT(s.no) AS total_count,

        SUM(CASE WHEN s.attendance_status = '출석' THEN 1 ELSE 0 END) AS attendance_count,
        SUM(CASE WHEN s.attendance_status = '결석' THEN 1 ELSE 0 END) AS absence_count,
        SUM(CASE WHEN s.attendance_status = '홀드' THEN 1 ELSE 0 END) AS hold_count

      FROM hk_lesson_schedule s

      LEFT JOIN hk_members m
      ON s.member_no = m.no

      LEFT JOIN hk_payments p
      ON s.payment_no = p.no

      WHERE p.lesson_status IN ('수강중', '수강종료')

      GROUP BY
        s.payment_no,
        s.member_no,
        s.teacher_name,
        m.user_id,
        m.user_name,
        m.phone,
        m.email,
        m.date,
        p.course_name,
        p.lesson_status,
        p.total_period

      ORDER BY lesson_start_date DESC
    ";

    $lesson_result = mysqli_query($db, $lesson_sql);


  // 화면에 출력할 때 특수문자를 안전하게 바꿔주는 함수
  function h($str){
    return htmlspecialchars((string)$str, ENT_QUOTES, "UTF-8");
  }

  // 현재 어떤 탭을 보여줄지 정함
  $active_tab = $_GET['tab'] ?? 'member';

  // 사용할 관리자 탭 목록
  $tab_list = ['member', 'board', 'payment', 'lesson'];

  // 이상한 tab 값이 들어오면 회원관리로 이동
  if(!in_array($active_tab, $tab_list)){
    $active_tab = 'member';
  }
?>

<!DOCTYPE html>
<html lang="ko">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <title>Hello Korean - Admin</title>

  <link rel="stylesheet" href="./admin.css">
</head>

<body>

<header class="admin_header">
  <div class="admin_container">

    <!-- 관리자 페이지 제목 -->
    <h1>Hello Korean Admin</h1>

    <!-- 관리자 상단 버튼 영역 -->
    <div class="admin_top_btn">

      <!-- 현재 로그인한 관리자 아이디 표시 -->
      <span><?php echo h($_SESSION['admin_id']); ?> 관리자님</span>

      <!-- 사용자 사이트 메인으로 이동 -->
      <a href="../main_pg/01main_pg.php">사이트로 이동</a>

      <!-- 관리자 로그아웃 -->
      <a href="./admin_logout.php">관리자 로그아웃</a>
    </div>

  </div>
</header>

<nav class="admin_nav">
  <div class="admin_container">
    <ul>
      <!-- 회원관리 탭 -->
      <li>
        <button
          type="button"
          data-tab="member"
          class="admin_tab_btn <?php if($active_tab == 'member'){ echo 'active'; } ?>"
          onclick="showAdminTab('member')"
        >
          전체회원관리
        </button>
      </li>

      <!-- 문의글관리 탭 -->
      <li>
        <button
          type="button"
          data-tab="board"
          class="admin_tab_btn <?php if($active_tab == 'board'){ echo 'active'; } ?>"
          onclick="showAdminTab('board')"
        >
          문의글관리
        </button>
      </li>

      <!-- 결제회원 탭 -->
      <li>
        <button
          type="button"
          data-tab="payment"
          class="admin_tab_btn <?php if($active_tab == 'payment'){ echo 'active'; } ?>"
          onclick="showAdminTab('payment')"
        >
          결제회원
        </button>
      </li>

      <!-- 수강생관리 탭 -->
      <li>
        <button
          type="button"
          data-tab="lesson"
          class="admin_tab_btn <?php if($active_tab == 'lesson'){ echo 'active'; } ?>"
          onclick="showAdminTab('lesson')"
        >
          수강생관리
        </button>
      </li>
    </ul>
  </div>
</nav>

<main class="admin_main">
  <div class="admin_container">

    <!-- 관리자 화면 제목 -->
    <section class="admin_title">
      <h2>HelloKorean 관리자 화면</h2>
      <p>회원 정보와 문의글을 관리하는 페이지입니다.</p>
    </section>

    <!-- 회원관리 내용 영역 -->
    <!-- $active_tab 값이 member일 때만 active 클래스를 붙여서 화면에 보이게 함 -->
    <section id="member_panel" class="admin_panel <?php if($active_tab == 'member'){ echo 'active'; } ?>">

      <div class="admin_panel_title">
        <h3>회원관리</h3>
        <p>가입한 회원 목록을 확인합니다.</p>
      </div>

      <div class="admin_table_area">
        <table class="admin_table">
          <caption>회원 목록</caption>

          <thead>
            <tr>
              <th>번호</th>
              <th>아이디</th>
              <th>이름</th>
              <th>전화번호</th>
              <th>이메일</th>
              <th>권한</th>
              <th>가입일</th>
            </tr>
          </thead>

          <tbody>
            <?php
              // 회원 데이터가 있을 때
              if($member_result && mysqli_num_rows($member_result) > 0){

                // 회원을 한 명씩 꺼내서 출력
                while($member = mysqli_fetch_array($member_result, MYSQLI_ASSOC)){
            ?>

              <tr>
                <td><?php echo h($member['no']); ?></td>
                <td><?php echo h($member['user_id']); ?></td>
                <td><?php echo h($member['user_name']); ?></td>
                <td><?php echo h($member['phone']); ?></td>
                <td><?php echo h($member['email']); ?></td>
                <td><?php echo h($member['role']); ?></td>
                <td><?php echo h($member['date']); ?></td>
              </tr>

            <?php
                }
              }else{
            ?>

              <!-- 회원이 없을 때 -->
              <tr>
                <td colspan="7">등록된 회원이 없습니다.</td>
              </tr>

            <?php
              }
            ?>
          </tbody>
        </table>
      </div>

      <!-- 회원관리 페이지 번호 -->
      <div class="pagination">

        <?php
          // 이전 버튼
          if($member_page > 1){
        ?>
          <a href="./admin.php?tab=member&member_page=<?php echo $member_page - 1; ?>">이전</a>
        <?php
          }else{
        ?>
          <a href="#" class="disabled">이전</a>
        <?php
          }
        ?>

        <?php
          // 회원관리 페이지 번호 출력
          for($i = 1; $i <= $member_total_page; $i++){
        ?>
          <a href="./admin.php?tab=member&member_page=<?php echo $i; ?>"
            class="<?php if($i == $member_page){ echo 'active'; } ?>">
            <?php echo $i; ?>
          </a>
        <?php
          }
        ?>

        <?php
          // 다음 버튼
          if($member_page < $member_total_page){
        ?>
          <a href="./admin.php?tab=member&member_page=<?php echo $member_page + 1; ?>">다음</a>
        <?php
          }else{
        ?>
          <a href="#" class="disabled">다음</a>
        <?php
          }
        ?>

      </div>

    </section>

    <!-- 문의글관리 내용 영역 -->
    <!-- $active_tab 값이 board일 때만 active 클래스를 붙여서 화면에 보이게 함 -->
    <section id="board_panel" class="admin_panel <?php if($active_tab == 'board'){ echo 'active'; } ?>">

      <div class="admin_panel_title">
        <h3>문의글관리</h3>
        <p>학생들이 작성한 문의글을 확인합니다.</p>
      </div>

      <div class="admin_table_area">
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
              <th>관리</th>
            </tr>
          </thead>

          <tbody>
            <?php
              // 문의글 데이터가 있을 때
              if($board_result && mysqli_num_rows($board_result) > 0){

                // 문의글을 한 개씩 꺼내서 출력
                while($board = mysqli_fetch_array($board_result, MYSQLI_ASSOC)){
            ?>

              <tr>
                <td><?php echo h($board['no']); ?></td>
                <td><?php echo h($board['writer']); ?></td>
                <td><?php echo h($board['category']); ?></td>
                <td class="text_left">
                  <!-- 제목을 누르면 관리자용 문의글 상세보기 페이지로 이동 -->
                  <a href="./board_view.php?no=<?php echo h($board['no']); ?>">
                    <?php echo h($board['title']); ?>
                  </a>
                </td>
                <td class="text_left"><?php echo h(mb_strimwidth($board['message'], 0, 60, "...", "UTF-8")); ?></td>
                <td><?php echo h($board['status']); ?></td>
                <td><?php echo h($board['date']); ?></td>
                <td>
                  <!-- 삭제 버튼 form -->
                  <!-- POST 방식으로 글 번호를 board_delete.php에 보냄 -->
                  <form action="./board_delete.php" method="post" onsubmit="return confirm('정말 이 문의글을 삭제하시겠습니까?');">

                    <!-- 삭제할 문의글 번호를 hidden으로 보냄 -->
                    <input type="hidden" name="no" value="<?php echo h($board['no']); ?>">

                    <!-- 삭제 버튼 -->
                    <button type="submit" class="delete_btn">삭제</button>
                  </form>
                </td>
              </tr>

            <?php
                }
              }else{
            ?>

              <!-- 문의글이 없을 때 -->
              <tr>
                <td colspan="8">등록된 문의글이 없습니다.</td>
              </tr>

            <?php
              }
            ?>
          </tbody>
        </table>
      </div>

      <!-- 문의글관리 페이지 번호 -->
      <div class="pagination">

        <?php
          // 이전 버튼
          if($board_page > 1){
        ?>
          <a href="./admin.php?tab=board&board_page=<?php echo $board_page - 1; ?>">이전</a>
        <?php
          }else{
        ?>
          <a href="#" class="disabled">이전</a>
        <?php
          }
        ?>

        <?php
          // 문의글관리 페이지 번호 출력
          for($i = 1; $i <= $board_total_page; $i++){
        ?>
          <a href="./admin.php?tab=board&board_page=<?php echo $i; ?>"
            class="<?php if($i == $board_page){ echo 'active'; } ?>">
            <?php echo $i; ?>
          </a>
        <?php
          }
        ?>

        <?php
          // 다음 버튼
          if($board_page < $board_total_page){
        ?>
          <a href="./admin.php?tab=board&board_page=<?php echo $board_page + 1; ?>">다음</a>
        <?php
          }else{
        ?>
          <a href="#" class="disabled">다음</a>
        <?php
          }
        ?>

      </div>
    </section>

    <!-- 결제회원 내용 영역 -->
    <section id="payment_panel" class="admin_panel <?php if($active_tab == 'payment'){ echo 'active'; } ?>">

      <div class="admin_panel_title">
        <h3>결제회원</h3>
        <p>무통장입금 신청 내역을 확인하고 결제상태와 결제액을 관리합니다.</p>
      </div>

      <div class="admin_table_area wide_table_area">
        <table class="admin_table payment_table">
          <caption>결제회원 목록</caption>

          <thead>
            <tr>
              <th>번호</th>
              <th>아이디</th>
              <th>이름</th>
              <th>Course</th>
              <th>희망 시작일</th>
              <th>수업시간</th>
              <th>총 기간</th>
              <th>결제수단</th>
              <th>입금자명</th>
              <th>입금예정일</th>
              <th>결제상태</th>
              <th>결제액</th>
              <th>수강상태</th>
              <th>관리</th>
            </tr>
          </thead>

          <tbody>
            <?php
              // 결제회원 데이터가 있을 때
              if($payment_result && mysqli_num_rows($payment_result) > 0){

                while($payment = mysqli_fetch_array($payment_result, MYSQLI_ASSOC)){

                  // 결제회원 한 줄마다 사용할 form id
                  $payment_form_id = 'payment_form_' . $payment['no'];

                  // 결제상태가 입금완료이면 더 이상 수정하지 못하게 처리
                  $is_paid = ($payment['payment_status'] == '입금완료');
            ?>

              <tr>
                <td><?php echo h($payment['no']); ?></td>
                <td><?php echo h($payment['user_id']); ?></td>
                <td><?php echo h($payment['user_name']); ?></td>
                <td><?php echo h($payment['course_name']); ?></td>
                <td><?php echo h($payment['start_date']); ?></td>
                <td><?php echo h($payment['lesson_time']); ?></td>
                <td><?php echo h($payment['total_period']); ?></td>
                <td><?php echo h($payment['payment_method']); ?></td>
                <td><?php echo h($payment['depositor_name']); ?></td>
                <td><?php echo h($payment['deposit_date']); ?></td>

                <!-- 결제상태 -->
                <td>
                  <?php
                    // 입금완료된 건은 select 박스를 보여주지 않음
                    if($is_paid){
                  ?>
                    <span class="status_badge paid">입금완료</span>
                  <?php
                    }else{
                  ?>
                    <select name="payment_status" class="admin_select" form="<?php echo h($payment_form_id); ?>">
                      <option value="입금대기" <?php if($payment['payment_status'] == '입금대기'){ echo 'selected'; } ?>>입금대기</option>
                      <option value="입금완료" <?php if($payment['payment_status'] == '입금완료'){ echo 'selected'; } ?>>입금완료</option>
                      <option value="취소" <?php if($payment['payment_status'] == '취소'){ echo 'selected'; } ?>>취소</option>
                    </select>
                  <?php
                    }
                  ?>
                </td>

                <!-- 결제액 -->
                <td>
                  <?php
                    // 입금완료된 건은 input 박스를 보여주지 않음
                    if($is_paid){
                  ?>
                    <strong><?php echo number_format((int)$payment['payment_amount']); ?>원</strong>
                  <?php
                    }else{
                  ?>
                    <input
                      type="text"
                      name="payment_amount"
                      class="payment_amount_input"
                      value="<?php echo h($payment['payment_amount']); ?>"
                      form="<?php echo h($payment_form_id); ?>"
                    >
                  <?php
                    }
                  ?>
                </td>

                  <!-- 수강상태 -->
                  <td>
                    <?php
                      if($payment['lesson_status'] == '수강중'){
                    ?>
                      <span class="status_badge studying">수강중</span>
                    <?php
                      }else if($payment['lesson_status'] == '수강종료'){
                    ?>
                      <span class="status_badge ended">수강종료</span>
                    <?php
                      }else{
                    ?>
                      <a href="./lesson_register.php?payment_no=<?php echo h($payment['no']); ?>" class="status_badge need lesson_register_btn">
                        등록필요
                      </a>
                    <?php
                      }
                    ?>
                  </td>

                <!-- 관리 -->
                <td>
                  <?php
                    // 입금완료된 건은 저장 버튼을 보여주지 않음
                    if($is_paid){
                  ?>
                    <span class="admin_done_text">저장완료</span>
                  <?php
                    }else{
                  ?>
                    <form
                      id="<?php echo h($payment_form_id); ?>"
                      action="./payment_update_ok.php"
                      method="post"
                      onsubmit="return confirm('결제정보를 저장하시겠습니까?');"
                    >
                      <input type="hidden" name="no" value="<?php echo h($payment['no']); ?>">
                    </form>

                    <button type="submit" class="admin_small_btn" form="<?php echo h($payment_form_id); ?>">저장</button>
                  <?php
                    }
                  ?>
                </td>
              </tr>

            <?php
                }
              }else{
            ?>

              <tr>
                <td colspan="14">등록된 결제회원이 없습니다.</td>
              </tr>

            <?php
              }
            ?>
          </tbody>
        </table>
      </div>

    </section>

    <!-- 수강생관리 내용 영역 -->
    <!-- 수강생관리 내용 영역 -->
    <section id="lesson_panel" class="admin_panel <?php if($active_tab == 'lesson'){ echo 'active'; } ?>">

      <div class="admin_panel_title">
        <h3>수강생관리</h3>
        <p>수강등록이 완료된 학생의 수업 기간, 강사, 남은 횟수, 출결 및 홀드 현황을 확인합니다.</p>
      </div>

        <!-- 홀드 신청 관리 버튼 -->
        <div style="margin-bottom:15px; text-align:right;">
          <a
            href="./hold_request_list.php"
            style="display:inline-block; padding:8px 14px; background-color:#1486b8; color:#fff; font-size:13px; border-radius:4px;"
          >
            홀드 신청 관리
          </a>
        </div>

      <div class="admin_table_area wide_table_area">
        <table class="admin_table student_lesson_table">
          <caption>수강생관리 목록</caption>

          <thead>
            <tr>
              <th>번호</th>
              <th>수강생명</th>
              <th>아이디</th>
              <th>휴대전화</th>
              <th>이메일</th>
              <th>가입일자</th>
              <th>Course</th>
              <th>수업시작일</th>
              <th>수업종료일</th>
              <th>수강여부</th>
              <th>요일</th>
              <th>시간</th>
              <th>강사명</th>
              <th>총횟수</th>
              <th>남은횟수</th>
              <th>출석</th>
              <th>결석</th>
              <th>홀드</th>
              <th>관리</th>
            </tr>
          </thead>

          <tbody>
            <?php
              // 수강생 데이터가 있을 때
              if($lesson_result && mysqli_num_rows($lesson_result) > 0){

                $lesson_num = 1;

                while($lesson = mysqli_fetch_array($lesson_result, MYSQLI_ASSOC)){

                  // 출석/결석/홀드 개수
                  $attendance_count = (int)$lesson['attendance_count'];
                  $absence_count = (int)$lesson['absence_count'];
                  $hold_count = (int)$lesson['hold_count'];

                  // 총 수업횟수
                  $total_count = (int)$lesson['total_count'];

                  // 남은횟수
                  // 출석과 결석은 수업을 사용한 것으로 보고 차감
                  // 홀드는 아직 수업을 사용하지 않은 것으로 보고 차감하지 않음
                  $remain_count = $total_count - $attendance_count - $absence_count;

                  if($remain_count < 0){
                    $remain_count = 0;
                  }
            ?>

              <tr>
                <td><?php echo h($lesson_num); ?></td>
                <td><?php echo h($lesson['user_name']); ?></td>
                <td><?php echo h($lesson['user_id']); ?></td>
                <td><?php echo h($lesson['phone']); ?></td>
                <td><?php echo h($lesson['email']); ?></td>
                <td><?php echo h($lesson['join_date']); ?></td>
                <td><?php echo h($lesson['course_name']); ?></td>
                <td><?php echo h($lesson['lesson_start_date']); ?></td>
                <td><?php echo h($lesson['lesson_end_date']); ?></td>

                <td>
                  <?php
                    if($lesson['lesson_status'] == '수강중'){
                  ?>
                    <span class="status_badge studying">수강중</span>
                  <?php
                    }else if($lesson['lesson_status'] == '수강종료'){
                  ?>
                    <span class="status_badge ended">수강종료</span>
                  <?php
                    }else{
                  ?>
                    <span class="status_badge need"><?php echo h($lesson['lesson_status']); ?></span>
                  <?php
                    }
                  ?>
                </td>

                <td><?php echo h($lesson['lesson_days']); ?></td>
                <td><?php echo h($lesson['lesson_times']); ?></td>
                <td><?php echo h($lesson['teacher_name']); ?></td>
                <td><?php echo h($total_count); ?>회</td>
                <td><?php echo h($remain_count); ?>회</td>
                <td><?php echo h($attendance_count); ?>회</td>
                <td><?php echo h($absence_count); ?>회</td>
                <td><?php echo h($hold_count); ?>회</td>
                <td>
                  <a href="./lesson_view.php?payment_no=<?php echo h($lesson['payment_no']); ?>" class="admin_small_btn">
                    상세관리
                  </a>
                </td>
              </tr>

            <?php
                  $lesson_num++;
                }
              }else{
            ?>

              <tr>
                <td colspan="19">
                  아직 등록된 수강생 데이터가 없습니다. 결제회원에서 수강등록을 하면 이곳에 표시됩니다.
                </td>
              </tr>

            <?php
              }
            ?>
          </tbody>
        </table>
      </div>

    </section>

  </div>
</main>

<script>
  // 관리자 탭을 바꾸는 함수
  function showAdminTab(tabName){

    // 모든 탭 내용 영역을 가져옴
    let panels = document.querySelectorAll('.admin_panel');

    // 모든 탭 버튼을 가져옴
    let buttons = document.querySelectorAll('.admin_tab_btn');

    // 모든 탭 내용을 숨김
    panels.forEach(function(panel){
      panel.classList.remove('active');
    });

    // 모든 버튼의 active 제거
    buttons.forEach(function(button){
      button.classList.remove('active');
    });

    // 선택한 탭 내용 보이기
    let targetPanel = document.querySelector('#' + tabName + '_panel');

    if(targetPanel){
      targetPanel.classList.add('active');
    }

    // 선택한 탭 버튼 active 처리
    let targetButton = document.querySelector('.admin_tab_btn[data-tab="' + tabName + '"]');

    if(targetButton){
      targetButton.classList.add('active');
    }

    // 주소창 tab 값도 바꿔줌
    history.pushState(null, '', './admin.php?tab=' + tabName);
  }
</script>

</body>
</html>