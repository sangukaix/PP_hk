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
  $hold_reason = $_POST['hold_reason'] ?? '';

  // 숫자 처리
  $lesson_no = (int)$lesson_no;
  $payment_no = (int)$payment_no;

  // 사유 앞뒤 공백 제거
  $hold_reason = trim($hold_reason);

  // 기본값 체크
  if($lesson_no < 1 || $payment_no < 1){
    echo "
      <script>
        alert('잘못된 접근입니다.');
        location.href='./schedule.php';
      </script>
    ";
    exit;
  }

  // 홀드 사유가 비어있는지 체크
  if($hold_reason == ""){
    echo "
      <script>
        alert('홀드 신청 사유를 입력해주세요.');
        history.back();
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
        alert('신청 가능한 수업을 찾을 수 없습니다.');
        location.href='./schedule.php';
      </script>
    ";
    exit;
  }

  $lesson = mysqli_fetch_array($lesson_result, MYSQLI_ASSOC);

        // ==============================
        // 수업 1주일 전까지만 홀드 신청 가능
        // 예: 오늘이 6월 19일이면 6월 26일 수업부터 신청 가능
        // ==============================
        $limit_date = date('Y-m-d', strtotime('+7 days'));

        if($lesson['lesson_date'] < $limit_date){
        echo "
            <script>
            alert('홀드 및 홀드취소는 수업 1주일 이전에만 가능합니다.');
            location.href='./schedule.php';
            </script>
        ";
        exit;
        }

  // 이미 홀드된 수업이면 홀드신청이 아니라 홀드취소를 해야 함
  if($lesson['attendance_status'] == "홀드"){
    echo "
      <script>
        alert('이미 홀드된 수업입니다. 홀드취소를 이용해주세요.');
        location.href='./schedule.php';
      </script>
    ";
    exit;
  }

  // 출석/결석 처리된 수업은 홀드 신청 불가
  if($lesson['attendance_status'] != "예정"){
    echo "
      <script>
        alert('출석 또는 결석 처리된 수업은 홀드 신청을 할 수 없습니다.');
        location.href='./schedule.php';
      </script>
    ";
    exit;
  }

  // ==============================
  // 홀드 사용한도 체크
  // 관리자 승인된 홀드만 사용 횟수로 계산
  // ==============================
  $limit_sql = "
    SELECT
      p.hold_limit,

      (
        SELECT COUNT(*)
        FROM hk_hold_requests hr
        WHERE hr.payment_no = p.no
        AND hr.member_no = '$user_no'
        AND hr.request_type = '홀드신청'
        AND hr.request_status = '승인'
      ) AS hold_used_count

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

  $hold_limit = (int)$limit_row['hold_limit'];
  $hold_used_count = (int)$limit_row['hold_used_count'];

  if($hold_used_count >= $hold_limit){
    echo "
      <script>
        alert('홀드 사용한도를 초과했습니다.');
        location.href='./schedule.php';
      </script>
    ";
    exit;
  }

  // 같은 수업에 대기 중인 홀드 신청이 있는지 확인
  $check_sql = "
    SELECT no
    FROM hk_hold_requests
    WHERE lesson_no = '$lesson_no'
    AND member_no = '$user_no'
    AND request_type = '홀드신청'
    AND request_status = '대기'
  ";

  $check_result = mysqli_query($db, $check_sql);

  if($check_result && mysqli_num_rows($check_result) > 0){
    echo "
      <script>
        alert('이미 홀드 신청한 수업입니다.');
        location.href='./schedule.php';
      </script>
    ";
    exit;
  }

  // SQL 특수문자 처리
  $hold_reason = mysqli_real_escape_string($db, $hold_reason);

  // 홀드 신청 저장
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
      '홀드신청',
      '$hold_reason',
      '대기'
    )
  ";

  $insert_result = mysqli_query($db, $insert_sql);

  if($insert_result){
    echo "
      <script>
        alert('홀드 신청이 접수되었습니다. 관리자 확인 후 처리됩니다.');
        location.href='./schedule.php';
      </script>
    ";
  }else{
    echo "
      <script>
        alert('홀드 신청 저장에 실패했습니다.');
        history.back();
      </script>
    ";
  }

  mysqli_close($db);
?>