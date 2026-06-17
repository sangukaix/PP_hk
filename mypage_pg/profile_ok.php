<?php
  // 세션 시작
  session_start();

  // DB 연결
  include "../common/db.php";

  // 로그인하지 않은 사용자는 접근 불가
  if(!isset($_SESSION['user_no'])){
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

  // POST로 넘어온 값 받기
  $user_pw = $_POST['user_pw'] ?? '';
  $user_pw_check = $_POST['user_pw_check'] ?? '';
  $phone = $_POST['phone'] ?? '';

  // 사진 삭제 여부
  // profile.php에서 사진 삭제 버튼을 누르면 이 값이 1로 넘어옴
  $delete_profile_img = $_POST['delete_profile_img'] ?? '0';

  // 전화번호 SQL 안전 처리
  $phone = mysqli_real_escape_string($db, $phone);

  // 현재 회원 정보 가져오기
  // 기존 프로필 사진을 유지/삭제/교체하기 위해 필요함
  $sql = "SELECT * FROM hk_members WHERE no = '$user_no'";
  $result = mysqli_query($db, $sql);

  if($result && mysqli_num_rows($result) > 0){
    $member = mysqli_fetch_array($result, MYSQLI_ASSOC);
  }else{
    echo "
      <script>
        alert('회원 정보를 찾을 수 없습니다.');
        history.back();
      </script>
    ";
    exit;
  }

  // 기존 프로필 이미지
  $profile_img = '';

  if(isset($member['profile_img'])){
    $profile_img = $member['profile_img'];
  }

  // 기존 사진 파일명 따로 저장
  // 새 사진 업로드 시 기존 파일을 삭제하기 위해 사용
  $old_profile_img = $profile_img;

  // 사용자가 사진 삭제를 선택한 경우
  if($delete_profile_img == "1"){

    // 기존 사진 파일이 있으면 실제 파일 삭제
    if($profile_img != ""){
      $old_file_path = "../upload/profile/" . $profile_img;

      if(file_exists($old_file_path)){
        unlink($old_file_path);
      }
    }

    // DB에는 빈 값으로 저장되게 함
    $profile_img = "";
  }

  // 새 비밀번호를 입력했는지 확인
  $change_pw = false;
  $new_pw_hash = '';

  if($user_pw != '' || $user_pw_check != ''){

    // 비밀번호와 비밀번호 확인이 다르면 중단
    if($user_pw != $user_pw_check){
      echo "
        <script>
          alert('비밀번호가 서로 다릅니다.');
          history.back();
        </script>
      ";
      exit;
    }

    // 비밀번호 암호화
    $new_pw_hash = password_hash($user_pw, PASSWORD_DEFAULT);
    $new_pw_hash = mysqli_real_escape_string($db, $new_pw_hash);

    $change_pw = true;
  }

  // 프로필 사진 업로드 처리
  if(isset($_FILES['profile_img']) && $_FILES['profile_img']['error'] != 4){

    // 업로드 오류 확인
    if($_FILES['profile_img']['error'] != 0){
      echo "
        <script>
          alert('파일 업로드 중 오류가 발생했습니다.');
          history.back();
        </script>
      ";
      exit;
    }

    // 파일 정보
    $file_name = $_FILES['profile_img']['name'];
    $file_tmp = $_FILES['profile_img']['tmp_name'];
    $file_size = $_FILES['profile_img']['size'];

    // 확장자 가져오기
    $file_ext = pathinfo($file_name, PATHINFO_EXTENSION);
    $file_ext = strtolower($file_ext);

    // 허용할 이미지 확장자
    $allow_ext = ['jpg', 'jpeg', 'png', 'gif'];

    // 이미지 파일인지 확인
    if(!in_array($file_ext, $allow_ext)){
      echo "
        <script>
          alert('jpg, jpeg, png, gif 이미지 파일만 업로드할 수 있습니다.');
          history.back();
        </script>
      ";
      exit;
    }

    // 파일 크기 제한: 2MB
    if($file_size > 1024 * 1024 * 2){
      echo "
        <script>
          alert('프로필 사진은 2MB 이하만 업로드할 수 있습니다.');
          history.back();
        </script>
      ";
      exit;
    }

    // 업로드 폴더
    $upload_dir = "../upload/profile/";

    // 폴더가 없으면 생성
    if(!is_dir($upload_dir)){
      mkdir($upload_dir, 0777, true);
    }

    // 새 파일명 만들기
    $new_file_name = "profile_" . $user_no . "_" . time() . "_" . rand(1000, 9999) . "." . $file_ext;

    // 실제 저장 경로
    $save_path = $upload_dir . $new_file_name;

    // 파일 이동
    if(move_uploaded_file($file_tmp, $save_path)){

      // 기존 프로필 사진이 있으면 삭제
      if($old_profile_img != ""){
        $old_file_path = "../upload/profile/" . $old_profile_img;

        if(file_exists($old_file_path)){
          unlink($old_file_path);
        }
      }

      // DB에 저장할 새 파일명
      $profile_img = $new_file_name;

    }else{
      echo "
        <script>
          alert('프로필 사진 저장에 실패했습니다.');
          history.back();
        </script>
      ";
      exit;
    }
  }

  // 프로필 이미지 파일명 SQL 안전 처리
  $profile_img = mysqli_real_escape_string($db, $profile_img);

  // 비밀번호 변경 여부에 따라 SQL 다르게 작성
  if($change_pw == true){

    $update_sql = "
      UPDATE hk_members
      SET
        user_pw = '$new_pw_hash',
        phone = '$phone',
        profile_img = '$profile_img'
      WHERE no = '$user_no'
    ";

  }else{

    $update_sql = "
      UPDATE hk_members
      SET
        phone = '$phone',
        profile_img = '$profile_img'
      WHERE no = '$user_no'
    ";

  }

  // UPDATE 실행
  $update_result = mysqli_query($db, $update_sql);

  if($update_result){
    echo "
      <script>
        alert('개인정보가 수정되었습니다.');
        location.href='./profile.php';
      </script>
    ";
  }else{
    echo "
      <script>
        alert('개인정보 수정에 실패했습니다.');
        history.back();
      </script>
    ";
  }

  // DB 연결 종료
  mysqli_close($db);
?>