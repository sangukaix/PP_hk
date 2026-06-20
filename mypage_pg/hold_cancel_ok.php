<?php
  session_start();

  // 한글 깨짐 방지
  header("Content-Type:text/html; charset=utf-8");

  // 로그인 체크
  if(!isset($_SESSION['user_id'])){
    echo "
      <script>
        alert('로그인 후 이용해주세요.');
        location.href='../member_pg/login.php';
      </script>
    ";
    exit;
  }

  // DB 연결
  include "../common/db.php";

  // 로그인한 회원 번호
  $user_no = (int)$_SESSION['user_no'];

  // POST 값 받기
  $lesson_no = $_POST['lesson_no'] ?? '';
  $payment_no = $_POST['payment_no'] ?? '';

  $lesson_no = (int)$lesson_no;
  $payment_no = (int)$payment_no;

  if($lesson_no < 1 || $payment_no < 1){
    echo "
      <script>
        alert('잘못된 접근입니다.');
        location.href='./schedule.php';
      </script>
    ";
    exit;
  }

  // 본인의 수업인지 확인
  $lesson_sql = "
    SELECT *
    FROM hk_lesson_schedule
    WHERE no = '$lesson_no'
    AND payment_no = '$payment_no'
    AND member_no = '$user_no'
  ";

  $lesson_result = mysqli_query($db, $lesson_sql);

  if(!$lesson_result || mysqli_num_rows($lesson_result) < 1){
    echo "
      <script>
        alert('수업 정보를 찾을 수 없습니다.');
        location.href='./schedule.php';
      </script>
    ";
    exit;
  }

  $lesson = mysqli_fetch_array($lesson_result, MYSQLI_ASSOC);

  // ==============================
    // 수업 1주일 전까지만 홀드취소 가능
    // 예: 오늘이 6월 19일이면 6월 26일 수업부터 가능
    // ==============================
    $limit_date = date('Y-m-d', strtotime('+3 days'));

    if($lesson['lesson_date'] < $limit_date){
      echo "
        <script>
          alert('홀드취소는 수업일 기준 3일 전까지만 가능합니다.');
          location.href='./schedule.php';
        </script>
      ";
      exit;
    }

  // 1. 아직 승인 전인 홀드신청중인지 확인
  $pending_hold_sql = "
    SELECT no
    FROM hk_hold_requests
    WHERE lesson_no = '$lesson_no'
    AND payment_no = '$payment_no'
    AND member_no = '$user_no'
    AND request_type = '홀드신청'
    AND request_status = '대기'
    LIMIT 1
  ";

  $pending_hold_result = mysqli_query($db, $pending_hold_sql);

  // 홀드신청중이면 학생이 직접 취소 가능
  // 이건 관리자 승인 전 취소라서 홀드취소 횟수 차감 대상 아님
  if($pending_hold_result && mysqli_num_rows($pending_hold_result) > 0){

    $pending_hold = mysqli_fetch_array($pending_hold_result, MYSQLI_ASSOC);
    $request_no = (int)$pending_hold['no'];

    $cancel_sql = "
      UPDATE hk_hold_requests
      SET
        request_status = '취소',
        processed_date = NOW()
      WHERE no = '$request_no'
    ";

    $cancel_result = mysqli_query($db, $cancel_sql);

    if($cancel_result){
      echo "
        <script>
          alert('홀드 신청이 취소되었습니다.');
          location.href='./schedule.php';
        </script>
      ";
    }else{
      echo "
        <script>
          alert('홀드 신청 취소 중 오류가 발생했습니다.');
          history.back();
        </script>
      ";
    }

    exit;
  }

  // 2. 이미 홀드 승인된 수업이면 홀드취소요청으로 접수
  if($lesson['attendance_status'] == "홀드"){

    // ==============================
    // 홀드취소 사용한도 체크
    // 관리자 승인된 홀드취소만 사용 횟수로 계산
    // ==============================
    $limit_sql = "
      SELECT
        p.hold_cancel_limit,

        (
          SELECT COUNT(*)
          FROM hk_hold_requests hr
          WHERE hr.payment_no = p.no
          AND hr.member_no = '$user_no'
          AND hr.request_type = '홀드취소'
          AND hr.request_status = '승인'
        ) AS hold_cancel_used_count

      FROM hk_payments p
      WHERE p.no = '$payment_no'
      AND p.member_no = '$user_no'
      LIMIT 1
    ";

    $limit_result = mysqli_query($db, $limit_sql);

    if(!$limit_result || mysqli_num_rows($limit_result) < 1){
      echo "
        <script>
          alert('수강 정보를 찾을 수 없습니다.');
          location.href='./schedule.php';
        </script>
      ";
      exit;
    }

    $limit_row = mysqli_fetch_array($limit_result, MYSQLI_ASSOC);

    $hold_cancel_limit = (int)$limit_row['hold_cancel_limit'];
    $hold_cancel_used_count = (int)$limit_row['hold_cancel_used_count'];

    if($hold_cancel_used_count >= $hold_cancel_limit){
      echo "
        <script>
          alert('홀드취소 사용한도를 초과했습니다.');
          location.href='./schedule.php';
        </script>
      ";
      exit;
    }

    // 이미 홀드취소요청중인지 확인
    $check_cancel_sql = "
      SELECT no
      FROM hk_hold_requests
      WHERE lesson_no = '$lesson_no'
      AND payment_no = '$payment_no'
      AND member_no = '$user_no'
      AND request_type = '홀드취소'
      AND request_status = '대기'
      LIMIT 1
    ";

    $check_cancel_result = mysqli_query($db, $check_cancel_sql);

    if($check_cancel_result && mysqli_num_rows($check_cancel_result) > 0){
      echo "
        <script>
          alert('이미 홀드 취소 요청이 접수된 수업입니다.');
          location.href='./schedule.php';
        </script>
      ";
      exit;
    }

    // 홀드취소 요청 저장
    $insert_sql = "
      INSERT INTO hk_hold_requests (
        lesson_no,
        payment_no,
        member_no,
        request_type,
        hold_reason,
        request_status
      ) VALUES (
        '$lesson_no',
        '$payment_no',
        '$user_no',
        '홀드취소',
        '학생이 홀드 취소를 요청했습니다.',
        '대기'
      )
    ";

    $insert_result = mysqli_query($db, $insert_sql);

    if($insert_result){
      echo "
        <script>
          alert('홀드 취소 요청이 접수되었습니다. 관리자 확인 후 처리됩니다.');
          location.href='./schedule.php';
        </script>
      ";
    }else{
      echo "
        <script>
          alert('홀드 취소 요청 저장에 실패했습니다.');
          history.back();
        </script>
      ";
    }

    exit;
  }

  // 홀드신청중도 아니고, 홀드된 수업도 아닐 때
  echo "
    <script>
      alert('홀드된 수업이 없습니다.');
      location.href='./schedule.php';
    </script>
  ";

  mysqli_close($db);
?>