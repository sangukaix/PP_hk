<?php
  // 관리자 로그인 체크
  include "./admin_check.php";

  // 한글 깨짐 방지
  header("Content-Type:text/html; charset=utf-8");

  // DB 연결
  include "../common/db.php";

  // GET 값 받기
  $request_no = $_GET['no'] ?? '';
  $action = $_GET['action'] ?? '';

  // 숫자 처리
  $request_no = (int)$request_no;

  // 잘못된 접근 체크
  if($request_no < 1){
    echo "
      <script>
        alert('잘못된 접근입니다.');
        location.href='./hold_request_list.php';
      </script>
    ";
    exit;
  }

  // 승인/반려 값 체크
  if($action != "approve" && $action != "reject"){
    echo "
      <script>
        alert('잘못된 처리값입니다.');
        location.href='./hold_request_list.php';
      </script>
    ";
    exit;
  }

  // 홀드 신청 정보 가져오기
  $sql = "
    SELECT *
    FROM hk_hold_requests
    WHERE no = '$request_no'
  ";

  $result = mysqli_query($db, $sql);

  if(!$result || mysqli_num_rows($result) < 1){
    echo "
      <script>
        alert('홀드 신청 정보를 찾을 수 없습니다.');
        location.href='./hold_request_list.php';
      </script>
    ";
    exit;
  }

  $request = mysqli_fetch_array($result, MYSQLI_ASSOC);

  // 이미 처리된 신청은 다시 처리하지 않음
  if($request['request_status'] != "대기"){
    echo "
      <script>
        alert('이미 처리된 요청입니다.');
        location.href='./hold_request_list.php';
      </script>
    ";
    exit;
  }

  $lesson_no = (int)$request['lesson_no'];
  $request_type = $request['request_type'];

  // ==============================
  // 승인 처리
  // ==============================
  if($action == "approve"){

    // 1. 요청 상태를 승인으로 변경
    $update_request_sql = "
      UPDATE hk_hold_requests
      SET
        request_status = '승인',
        processed_date = NOW()
      WHERE no = '$request_no'
    ";

    $request_result = mysqli_query($db, $update_request_sql);

    // ==============================
    // 홀드신청 승인
    // ==============================
    if($request_type == "홀드신청"){

      // 원래 수업 정보 가져오기
      $origin_sql = "
        SELECT *
        FROM hk_lesson_schedule
        WHERE no = '$lesson_no'
      ";

      $origin_result = mysqli_query($db, $origin_sql);

      if(!$origin_result || mysqli_num_rows($origin_result) < 1){
        echo "
          <script>
            alert('원래 수업 정보를 찾을 수 없습니다.');
            location.href='./hold_request_list.php';
          </script>
        ";
        exit;
      }

      $origin_lesson = mysqli_fetch_array($origin_result, MYSQLI_ASSOC);

      $payment_no = (int)$origin_lesson['payment_no'];
      $member_no = (int)$origin_lesson['member_no'];

      // 원래 수업을 홀드 상태로 변경
      $update_lesson_sql = "
        UPDATE hk_lesson_schedule
        SET attendance_status = '홀드'
        WHERE no = '$lesson_no'
      ";

      $lesson_result = mysqli_query($db, $update_lesson_sql);

      // 이미 이 수업의 보강수업이 만들어졌는지 확인
      $makeup_check_sql = "
        SELECT no
        FROM hk_lesson_schedule
        WHERE makeup_for_lesson_no = '$lesson_no'
        AND lesson_type = '홀드 보강수업'
        LIMIT 1
      ";

      $makeup_check_result = mysqli_query($db, $makeup_check_sql);

      // 아직 보강수업이 없으면 새로 추가
      if($makeup_check_result && mysqli_num_rows($makeup_check_result) < 1){

        // 해당 결제건의 마지막 수업 가져오기
        $last_sql = "
          SELECT *
          FROM hk_lesson_schedule
          WHERE payment_no = '$payment_no'
          ORDER BY lesson_date DESC, no DESC
          LIMIT 1
        ";

        $last_result = mysqli_query($db, $last_sql);
        $last_lesson = mysqli_fetch_array($last_result, MYSQLI_ASSOC);

        if(!$last_lesson){
          echo "
            <script>
              alert('마지막 수업 정보를 찾을 수 없습니다.');
              location.href='./hold_request_list.php';
            </script>
          ";
          exit;
        }

        // 기존 수업 요일 패턴 가져오기
        $day_sql = "
          SELECT DISTINCT lesson_day
          FROM hk_lesson_schedule
          WHERE payment_no = '$payment_no'
          AND lesson_type = '정규수업'
          ORDER BY FIELD(lesson_day, '월', '화', '수', '목', '금', '토', '일')
        ";

        $day_result = mysqli_query($db, $day_sql);

        $lesson_days = [];

        while($day_row = mysqli_fetch_array($day_result, MYSQLI_ASSOC)){
          $lesson_days[] = $day_row['lesson_day'];
        }

        if(count($lesson_days) < 1){
          echo "
            <script>
              alert('수업 요일 정보를 찾을 수 없습니다.');
              location.href='./hold_request_list.php';
            </script>
          ";
          exit;
        }

        // 요일 배열
        $day_names = ['일', '월', '화', '수', '목', '금', '토'];

        // 마지막 수업 다음날부터 기존 요일 패턴에 맞는 첫 날짜 찾기
        $current_time = strtotime("+1 day", strtotime($last_lesson['lesson_date']));
        $loop_count = 0;

        $makeup_date = '';
        $makeup_day = '';

        while($loop_count < 30){

          $day_num = date('w', $current_time);
          $day_text = $day_names[$day_num];

          if(in_array($day_text, $lesson_days)){
            $makeup_date = date('Y-m-d', $current_time);
            $makeup_day = $day_text;
            break;
          }

          $current_time = strtotime("+1 day", $current_time);
          $loop_count++;
        }

        if($makeup_date == ''){
          echo "
            <script>
              alert('보강수업 날짜를 찾지 못했습니다.');
              location.href='./hold_request_list.php';
            </script>
          ";
          exit;
        }

        // 보강수업 정보
        $teacher_name = mysqli_real_escape_string($db, $origin_lesson['teacher_name']);
        $lesson_time = mysqli_real_escape_string($db, $origin_lesson['lesson_time']);
        $makeup_date = mysqli_real_escape_string($db, $makeup_date);
        $makeup_day = mysqli_real_escape_string($db, $makeup_day);

        // 보강수업 추가
        $makeup_insert_sql = "
          INSERT INTO hk_lesson_schedule
          (
            payment_no,
            member_no,
            teacher_name,
            lesson_date,
            lesson_day,
            lesson_time,
            lesson_type,
            makeup_for_lesson_no,
            attendance_status
          )
          VALUES
          (
            '$payment_no',
            '$member_no',
            '$teacher_name',
            '$makeup_date',
            '$makeup_day',
            '$lesson_time',
            '홀드 보강수업',
            '$lesson_no',
            '예정'
          )
        ";

        $makeup_result = mysqli_query($db, $makeup_insert_sql);

      }else{
        // 이미 보강수업이 있으면 추가하지 않음
        $makeup_result = true;
      }

      if($request_result && $lesson_result && $makeup_result){
        echo "
          <script>
            alert('홀드 신청이 승인되었습니다. 보강수업이 맨 뒤에 자동 추가되었습니다.');
            location.href='./hold_request_list.php';
          </script>
        ";
      }else{
        echo "
          <script>
            alert('홀드 승인 처리 중 오류가 발생했습니다.');
            history.back();
          </script>
        ";
      }

    }

    // ==============================
    // 홀드취소 승인
    // ==============================
    else if($request_type == "홀드취소"){

      // 홀드취소 승인 → 원래 수업 상태를 다시 예정으로 변경
      $update_lesson_sql = "
        UPDATE hk_lesson_schedule
        SET attendance_status = '예정'
        WHERE no = '$lesson_no'
      ";

      $lesson_result = mysqli_query($db, $update_lesson_sql);

      // 이 홀드 때문에 자동 추가됐던 보강수업 삭제
      $delete_makeup_sql = "
        DELETE FROM hk_lesson_schedule
        WHERE makeup_for_lesson_no = '$lesson_no'
        AND lesson_type = '홀드 보강수업'
      ";

      $delete_makeup_result = mysqli_query($db, $delete_makeup_sql);

      if($request_result && $lesson_result && $delete_makeup_result){
        echo "
          <script>
            alert('홀드 취소 요청이 승인되었습니다. 자동 추가된 보강수업이 삭제되었습니다.');
            location.href='./hold_request_list.php';
          </script>
        ";
      }else{
        echo "
          <script>
            alert('홀드 취소 승인 처리 중 오류가 발생했습니다.');
            history.back();
          </script>
        ";
      }

    }

    else{

      echo "
        <script>
          alert('알 수 없는 요청 종류입니다.');
          location.href='./hold_request_list.php';
        </script>
      ";
      exit;
    }

  // ==============================
  // 반려 처리
  // ==============================
  }else if($action == "reject"){

    // 반려는 요청 상태만 반려로 변경
    // 홀드신청 반려 → 수업 상태는 예정 그대로
    // 홀드취소 반려 → 수업 상태는 홀드 그대로
    $reject_sql = "
      UPDATE hk_hold_requests
      SET
        request_status = '반려',
        processed_date = NOW()
      WHERE no = '$request_no'
    ";

    $reject_result = mysqli_query($db, $reject_sql);

    if($reject_result){

      if($request_type == "홀드신청"){
        $msg = "홀드 신청이 반려되었습니다.";
      }else{
        $msg = "홀드 취소 요청이 반려되었습니다.";
      }

      echo "
        <script>
          alert('$msg');
          location.href='./hold_request_list.php';
        </script>
      ";

    }else{
      echo "
        <script>
          alert('반려 처리 중 오류가 발생했습니다.');
          history.back();
        </script>
      ";
    }
  }

  mysqli_close($db);
?>