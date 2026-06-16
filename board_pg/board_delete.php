<?php
  // 세션 사용
  session_start();

  // 한글 깨짐 방지
  header("Content-Type:text/html; charset=utf-8");

  // DB 연결
  include "../common/db.php";

  // 로그인하지 않은 경우
  if(!isset($_SESSION['user_no'])){
    echo "
      <script>
        alert('로그인 후 이용해주세요.');
        location.href='../member_pg/login.php';
      </script>
    ";
    exit;
  }

  // 삭제할 글 번호 받기
  $no = $_POST['no'] ?? '';

  // 글 번호가 없으면 이전 페이지로 이동
  if($no == ''){
    echo "
      <script>
        alert('잘못된 접근입니다.');
        history.back();
      </script>
    ";
    exit;
  }

  // 숫자로 변환
  $no = (int)$no;

  // 현재 로그인한 회원 번호
  $user_no = (int)$_SESSION['user_no'];

  // 삭제하려는 글 정보 가져오기
  $sql = "SELECT * FROM hk_board WHERE no = $no";
  $result = mysqli_query($db, $sql);
  $row = mysqli_fetch_array($result, MYSQLI_ASSOC);

  // 글이 없을 때
  if(!$row){
    echo "
      <script>
        alert('존재하지 않는 글입니다.');
        location.href='./board.php';
      </script>
    ";
    exit;
  }

  // 본인이 쓴 글이 아닐 때
  if((int)$row['member_no'] !== $user_no){
    echo "
      <script>
        alert('본인이 쓴 글만 삭제할 수 있습니다.');
        history.back();
      </script>
    ";
    exit;
  }

  // 답변완료된 글은 삭제 불가
  if($row['status'] == '답변완료'){
    echo "
      <script>
        alert('답변완료된 글은 삭제할 수 없습니다.');
        history.back();
      </script>
    ";
    exit;
  }

  // 삭제 실행
  $delete_sql = "DELETE FROM hk_board WHERE no = $no AND member_no = $user_no";
  $delete_result = mysqli_query($db, $delete_sql);

  // 삭제 성공 여부 확인
  if($delete_result){
    echo "
      <script>
        alert('글이 삭제되었습니다.');
        location.href='./board.php';
      </script>
    ";
    exit;
  }else{
    echo "
      <script>
        alert('삭제 중 오류가 발생했습니다.');
        history.back();
      </script>
    ";
    exit;
  }
?>