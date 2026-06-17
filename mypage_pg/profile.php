<?php
  // 세션 시작
  session_start();

  // DB 연결
  include "../common/db.php";

  // 화면 출력 시 특수문자 보호
  function h($str){
    return htmlspecialchars((string)$str, ENT_QUOTES, "UTF-8");
  }

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
  $user_no = $_SESSION['user_no'];

  // 로그인한 회원 정보 가져오기
  $sql = "SELECT * FROM hk_members WHERE no = '$user_no'";
  $result = mysqli_query($db, $sql);

  // 회원 정보가 있는지 확인
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
  // 아직 사진을 등록하지 않았으면 빈 값
  $profile_img = "";

  if(isset($member['profile_img'])){
    $profile_img = $member['profile_img'];
  }

  // 프로필 이미지 경로
  $profile_path = "../upload/profile/" . $profile_img;
?>

<!DOCTYPE html>
<html lang="ko">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <title>Hello Korean - Profile</title>

  <!-- 공통 CSS -->
  <link rel="stylesheet" href="../common/common.css">

  <!-- 마이페이지 CSS -->
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
        <li><a href="../main_pg/01main_pg.php">Home</a></li>
        <li><a href="#">Course</a></li>
        <li><a href="#">Teacher</a></li>
        <li><a href="#">Price</a></li>
        <li><a href="../contact_pg/contact.php">Contact</a></li>
        <li><a href="../board_pg/board.php" class="board">Board</a></li>

        <li>
          <a href="../mypage_pg/mypage.php" class="mypage_btn">My page</a>
        </li>

        <li>
          <a href="../member_pg/logout.php" class="login_btn">로그아웃</a>
        </li>

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
          <li><a href="./mypage.php">수강신청현황</a></li>
          <li><a href="./schedule.php">수업스케줄</a></li>
          <li><a href="#" class="disabled" onclick="return false;">쿠폰관리</a></li>
          <li><a href="#" class="disabled" onclick="return false;">결제내역</a></li>
          <li><a href="./profile.php" class="active">개인정보수정</a></li>
        </ul>
      </div>

      <!-- 개인정보수정 박스 -->
      <div class="mypage_box profile_box">

        <div class="mypage_title">
          <h3>개인정보수정</h3>
        </div>

        <!-- 개인정보 수정 폼 -->
        <form action="./profile_ok.php" method="post" enctype="multipart/form-data" class="profile_form">

        <!-- 프로필 사진 영역 -->
        <div class="profile_photo_area">

        <!-- 동그라미를 누르면 파일 선택창이 열리도록 label 사용 -->
        <label for="profile_img" id="profile_preview_circle" class="profile_preview_circle upload_circle">

            <?php
            if($profile_img != "" && file_exists($profile_path)){
            ?>

            <img src="<?php echo h($profile_path); ?>" alt="profile image">

            <?php
            }else{
            ?>

            <span>사진등록</span>

            <?php
            }
            ?>

        </label>

        <!-- 실제 파일 선택 input은 숨김 -->
        <input type="file" name="profile_img" id="profile_img" class="hidden_file_input" accept=".jpg,.jpeg,.png,.gif">

        <!-- 사진 삭제 여부를 profile_ok.php로 보내기 위한 hidden input -->
        <input type="hidden" name="delete_profile_img" id="delete_profile_img" value="0">

        <div class="profile_photo_text">
            <p>프로필 사진</p>

            <small>
            왼쪽 이미지를 클릭하면 사진을 업로드 하실 수 있습니다.
            </small>

            <button
            type="button"
            id="profile_delete_btn"
            class="profile_delete_btn"
            onclick="deleteProfileImg()"
            <?php
                if($profile_img == "" || !file_exists($profile_path)){
                echo "style='display:none;'";
                }
            ?>
            >
            삭제
            </button>

        </div>

        </div>

          <!-- 아이디 -->
          <div class="form_row">
            <label for="user_id">아이디</label>
            <input type="text" id="user_id" value="<?php echo h($member['user_id']); ?>" readonly>
          </div>

          <!-- 이름 -->
          <div class="form_row">
            <label for="user_name">이름</label>
            <input type="text" id="user_name" value="<?php echo h($member['user_name']); ?>" readonly>
          </div>

          <!-- 새 비밀번호 -->
          <div class="form_row">
            <label for="user_pw">새 비밀번호</label>
            <input type="password" id="user_pw" name="user_pw" placeholder="변경할 경우에만 입력하세요">
          </div>

          <!-- 새 비밀번호 확인 -->
          <div class="form_row">
            <label for="user_pw_check">새 비밀번호 확인</label>
            <input type="password" id="user_pw_check" name="user_pw_check" placeholder="비밀번호를 한 번 더 입력하세요">
          </div>

          <!-- 전화번호 -->
          <div class="form_row">
            <label for="phone">전화번호</label>
            <input type="text" id="phone" name="phone" value="<?php echo h($member['phone']); ?>">
          </div>

          <!-- 버튼 -->
          <div class="profile_btn_area">
            <button type="submit" class="profile_submit_btn">수정하기</button>
          </div>

        </form>

      </div>

    </div>
  </section>

</main>

<footer>
  <div class="container">
    <p>© Global Link Co., Ltd. All rights reserved.</p>
  </div>
</footer>

<script>
  // 필요한 태그들을 변수에 저장
  let profileInput = document.querySelector('#profile_img');
  let previewCircle = document.querySelector('#profile_preview_circle');
  let deleteInput = document.querySelector('#delete_profile_img');
  let deleteBtn = document.querySelector('#profile_delete_btn');

  // 파일을 선택했을 때 바로 미리보기
  profileInput.addEventListener('change', function(){
    let file = this.files[0];

    // 파일을 선택하지 않았으면 중단
    if(!file){
      return;
    }

    // 이미지 파일인지 확인
    if(!file.type.startsWith('image/')){
      alert('이미지 파일만 선택할 수 있습니다.');
      this.value = '';
      return;
    }

    // 파일을 읽어주는 객체
    let reader = new FileReader();

    // 파일 읽기가 끝나면 실행
    reader.onload = function(e){
      previewCircle.innerHTML = "<img src='" + e.target.result + "' alt='profile preview'>";

      // 새 사진을 선택했으므로 삭제 값은 다시 0
      deleteInput.value = '0';

      // 삭제 버튼 보이기
      if(deleteBtn){
        deleteBtn.style.display = 'inline-block';
      }
    };

    // 선택한 파일을 읽기 시작
    reader.readAsDataURL(file);
  });

  // 사진 삭제 버튼
  function deleteProfileImg(){
    let result = confirm('현재 프로필 사진을 삭제하시겠습니까?');

    if(result){
      // 삭제하겠다는 값을 profile_ok.php로 보냄
      deleteInput.value = '1';

      // 선택된 파일이 있다면 비우기
      profileInput.value = '';

      // 화면에서도 사진을 지우고 사진등록 글씨로 변경
      previewCircle.innerHTML = "<span>사진등록</span>";

      // 삭제 버튼 숨기기
      if(deleteBtn){
        deleteBtn.style.display = 'none';
      }
    }
  }
</script>

</body>
</html>