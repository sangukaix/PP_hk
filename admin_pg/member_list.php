<?php
  // 관리자 로그인 체크 파일 불러오기
  // 관리자 로그인이 안 되어 있으면 admin_login.php로 이동시킴
  include "./admin_check.php";


    header("Content-Type:text/html; charset=utf-8");

    include "../common/db.php";

    $sql = "SELECT * FROM hk_members ORDER BY no DESC";
    $result = mysqli_query($db, $sql);
?>

<!DOCTYPE html>
<html lang="ko">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <title>Hello Korean - Member List</title>

  <link rel="stylesheet" href="./admin.css">
</head>

<body>

<header id="admin_header">
  <div class="admin_top">

    <h1>Hello Korean Admin</h1>

    <div class="admin_user">
      <span>관리자 모드</span>
      <a href="./admin_login.php">로그인</a>
      <a href="./admin_logout.php">로그아웃</a>
    </div>

  </div>

  <nav class="admin_nav">
    <ul>
      <li><a href="./admin.php">관리자 홈</a></li>
      <li><a href="./member_list.php" class="active">회원 관리</a></li>
      <li><a href="./board_list.php">문의글 관리</a></li>
    </ul>
  </nav>
</header>

<main id="admin_main">

  <section class="admin_title">
    <h2>회원 관리</h2>
    <p>회원가입한 학생들의 정보를 확인하는 페이지입니다.</p>
  </section>

  <section class="admin_table_area">

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
          if(mysqli_num_rows($result) > 0){
            while($row = mysqli_fetch_array($result, MYSQLI_ASSOC)){
        ?>
              <tr>
                <td><?php echo $row['no']; ?></td>
                <td><?php echo $row['user_id']; ?></td>
                <td><?php echo $row['user_name']; ?></td>
                <td><?php echo $row['phone']; ?></td>
                <td><?php echo $row['email']; ?></td>
                <td><?php echo $row['role']; ?></td>
                <td><?php echo $row['date']; ?></td>
              </tr>
        <?php
            }
          }else{
        ?>
            <tr>
              <td colspan="7">가입한 회원이 없습니다.</td>
            </tr>
        <?php
          }
        ?>
      </tbody>
    </table>

  </section>

</main>

</body>
</html>