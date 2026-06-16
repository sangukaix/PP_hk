<?php
  // 관리자 로그인 체크 파일 불러오기
  // 관리자 로그인이 안 되어 있으면 admin_login.php로 이동
  include "./admin_check.php";

  // 한글 깨짐 방지
  header("Content-Type:text/html; charset=utf-8");

  // DB 연결 파일 불러오기
  // 회원 목록과 문의글 목록을 admin.php 안에서 보여주기 위해 필요함
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

  // 화면에 출력할 때 특수문자를 안전하게 바꿔주는 함수
  function h($str){
    return htmlspecialchars((string)$str, ENT_QUOTES, "UTF-8");
  }
    // 현재 어떤 탭을 보여줄지 정함
  $active_tab = $_GET['tab'] ?? 'member';

  if($active_tab != 'board'){
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
      <span><?php echo $_SESSION['admin_id']; ?> 관리자님</span>

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
      <!-- 회원관리 탭 버튼 -->
      <li>
      <!-- 회원관리 탭 버튼 -->
      <!-- $active_tab 값이 member일 때만 active 클래스를 붙임 -->
      <button type="button" class="admin_tab_btn <?php if($active_tab == 'member'){ echo 'active'; } ?>" onclick="showAdminTab('member')">
        회원관리
      </button>
      </li>

      <!-- 문의글관리 탭 버튼 -->
      <li>
        <!-- 문의글관리 탭 버튼 -->
        <!-- $active_tab 값이 board일 때만 active 클래스를 붙임 -->
        <button type="button" class="admin_tab_btn <?php if($active_tab == 'board'){ echo 'active'; } ?>" onclick="showAdminTab('board')">
          문의글관리
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

  </div>
</main>

<script>
  // 관리자 탭을 바꾸는 함수
  // tabName 값으로 'member' 또는 'board'가 들어옴
  function showAdminTab(tabName){

    // 모든 탭 내용 영역을 가져옴
    let panels = document.querySelectorAll('.admin_panel');

    // 모든 탭 버튼을 가져옴
    let buttons = document.querySelectorAll('.admin_tab_btn');

    // 일단 모든 탭 내용을 숨김
    panels.forEach(function(panel){
      panel.classList.remove('active');
    });

    // 일단 모든 버튼의 active 표시를 제거
    buttons.forEach(function(button){
      button.classList.remove('active');
    });

    // 회원관리 탭을 눌렀을 때
    if(tabName === 'member'){
      document.querySelector('#member_panel').classList.add('active');
      buttons[0].classList.add('active');
    }

    // 문의글관리 탭을 눌렀을 때
    if(tabName === 'board'){
      document.querySelector('#board_panel').classList.add('active');
      buttons[1].classList.add('active');
    }
  }
</script>

</body>
</html>