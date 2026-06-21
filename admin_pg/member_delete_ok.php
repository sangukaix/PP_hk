<?php
  // 관리자 로그인 체크
  include "./admin_check.php";

  // 한글 깨짐 방지
  header("Content-Type:text/html; charset=utf-8");

  // DB 연결
  include "../common/db.php";

  // POST로 넘어온 회원 번호 받기
  $member_no = $_POST['member_no'] ?? '';

  // 숫자로 변환
  $member_no = (int)$member_no;

  // 회원 번호가 없으면 중단
  if($member_no < 1){
    echo "
      <script>
        alert('잘못된 접근입니다.');
        location.href='./admin.php?tab=member';
      </script>
    ";
    exit;
  }

  // 삭제할 회원 정보 먼저 가져오기
  // 프로필 이미지 파일도 같이 삭제하기 위해 필요함
  $member_sql = "
    SELECT *
    FROM hk_members
    WHERE no = '$member_no'
  ";

  $member_result = mysqli_query($db, $member_sql);

  if(!$member_result || mysqli_num_rows($member_result) < 1){
    echo "
      <script>
        alert('삭제할 회원을 찾을 수 없습니다.');
        location.href='./admin.php?tab=member';
      </script>
    ";
    exit;
  }

  $member = mysqli_fetch_array($member_result, MYSQLI_ASSOC);

  // 프로필 이미지 파일명
  $profile_img = "";

  if(isset($member['profile_img'])){
    $profile_img = $member['profile_img'];
  }

  // 삭제 시작
  // 관련 데이터 삭제 중 하나라도 실패하면 전체를 취소하기 위해 사용
  mysqli_begin_transaction($db);

  $delete_ok = true;

  // 1. 홀드 / 홀드취소 신청 기록 삭제
  $sql1 = "
    DELETE FROM hk_hold_requests
    WHERE member_no = '$member_no'
  ";

  if(!mysqli_query($db, $sql1)){
    $delete_ok = false;
  }

  // 2. 수업 일정 삭제
  $sql2 = "
    DELETE FROM hk_lesson_schedule
    WHERE member_no = '$member_no'
  ";

  if(!mysqli_query($db, $sql2)){
    $delete_ok = false;
  }

  // 3. 수강신청 / 결제 정보 삭제
  $sql3 = "
    DELETE FROM hk_payments
    WHERE member_no = '$member_no'
  ";

  if(!mysqli_query($db, $sql3)){
    $delete_ok = false;
  }

  // 4. 문의글 삭제
  $sql4 = "
    DELETE FROM hk_board
    WHERE member_no = '$member_no'
  ";

  if(!mysqli_query($db, $sql4)){
    $delete_ok = false;
  }

  // 5. 회원 정보 삭제
  $sql5 = "
    DELETE FROM hk_members
    WHERE no = '$member_no'
  ";

  if(!mysqli_query($db, $sql5)){
    $delete_ok = false;
  }

  // 삭제 성공 / 실패 처리
  if($delete_ok == true){

    // DB 삭제 확정
    mysqli_commit($db);

    // 프로필 이미지 파일 삭제
    if($profile_img != ""){
      $profile_path = "../upload/profile/" . $profile_img;

      if(file_exists($profile_path)){
        unlink($profile_path);
      }
    }

    echo "
      <script>
        alert('회원과 관련 데이터가 모두 삭제되었습니다.');
        location.href='./admin.php?tab=member';
      </script>
    ";

  }else{

    // DB 삭제 취소
    mysqli_rollback($db);

    echo "
      <script>
        alert('회원 삭제 중 오류가 발생했습니다.');
        location.href='./admin.php?tab=member';
      </script>
    ";
  }

  mysqli_close($db);
?>