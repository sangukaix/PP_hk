<?php
  // 세션 시작
  // 관리자 로그인 정보를 저장하기 위해 필요함
  session_start();

  // 한글 깨짐 방지
  header("Content-Type:text/html; charset=utf-8");

  // form에서 넘어온 관리자 아이디와 비밀번호 받기
  $admin_id = $_POST['admin_id'] ?? '';
  $admin_pw = $_POST['admin_pw'] ?? '';

  // 연습용 관리자 아이디 / 비밀번호
  // 나중에 실제 서비스에서는 DB + password_hash 방식으로 바꾸는 것이 좋음
  $real_admin_id = "hellokorean";
  $real_admin_pw = "1234";

  // 아이디 또는 비밀번호가 비어 있는지 확인
  if($admin_id == '' || $admin_pw == ''){
    echo "
      <script>
        alert('관리자 아이디와 비밀번호를 입력해주세요.');
        history.back();
      </script>
    ";
    exit;
  }

  // 관리자 아이디와 비밀번호 확인
  if($admin_id == $real_admin_id && $admin_pw == $real_admin_pw){

    // 관리자 로그인 성공 정보를 세션에 저장
    $_SESSION['admin_login'] = true;
    $_SESSION['admin_id'] = $admin_id;

    echo "
      <script>
        alert('관리자 로그인 성공');
        location.href='./admin.php';
      </script>
    ";
  }else{
    echo "
      <script>
        alert('관리자 아이디 또는 비밀번호가 틀렸습니다.');
        history.back();
      </script>
    ";
  }
?>