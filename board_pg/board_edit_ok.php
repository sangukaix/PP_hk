<?php
  // 세션 시작
  // 로그인한 사용자인지 확인하기 위해 필요함
  session_start();

  // 한글 깨짐 방지
  header("Content-Type:text/html; charset=utf-8");

  // DB 연결 파일 불러오기
  include "../common/db.php";

  // 로그인 여부 확인
  // 로그인하지 않은 사람은 수정 저장 불가
  if(!isset($_SESSION['user_id'])){
    echo "
      <script>
        alert('로그인 후 이용해주세요.');
        location.href='../member_pg/login.php';
      </script>
    ";
    exit;
  }

  // 수정 form에서 넘어온 값 받기
  // board_edit.php의 input/select/textarea name 값과 같아야 함
  $no = $_POST['no'] ?? '';
  $title = $_POST['title'] ?? '';
  $category = $_POST['category'] ?? '';
  $content = $_POST['content'] ?? '';

  // 글 번호가 없으면 잘못된 접근
  if($no == ''){
    echo "
      <script>
        alert('잘못된 접근입니다.');
        location.href='./board.php';
      </script>
    ";
    exit;
  }

  // 필수 입력값 확인
  if($title == '' || $category == '' || $content == ''){
    echo "
      <script>
        alert('제목, 상담 유형, 내용을 모두 입력해주세요.');
        history.back();
      </script>
    ";
    exit;
  }

  // 글 번호는 숫자로만 사용하기 위해 정수로 변환
  $no = (int)$no;

  // 먼저 DB에서 해당 글 정보를 가져옴
  // 본인 글인지, 답변완료 상태인지 다시 확인하기 위함
  $check_sql = "SELECT * FROM hk_board WHERE no = $no";
  $check_result = mysqli_query($db, $check_sql);

  // 글이 존재하는지 확인
  if($check_result && mysqli_num_rows($check_result) > 0){
    $row = mysqli_fetch_array($check_result, MYSQLI_ASSOC);
  }else{
    echo "
      <script>
        alert('존재하지 않는 글입니다.');
        location.href='./board.php';
      </script>
    ";
    exit;
  }

  // 본인이 작성한 글인지 다시 확인
  // 화면에서 막아도 저장 처리 파일에서 한 번 더 막아야 안전함
  if($row['member_no'] != $_SESSION['user_no']){
    echo "
      <script>
        alert('다른사람이 쓴 글은 수정할 수 없습니다.');
        location.href='./board_view.php?no=$no';
      </script>
    ";
    exit;
  }

  // 답변완료된 글인지 다시 확인
  // 관리자가 답변한 이후에는 수정 불가
  if($row['status'] == '답변완료'){
    echo "
      <script>
        alert('답변완료된 글은 수정할 수 없습니다.');
        location.href='./board_view.php?no=$no';
      </script>
    ";
    exit;
  }

  // DB에 저장하기 전에 입력값 안전 처리
  $title = mysqli_real_escape_string($db, $title);
  $category = mysqli_real_escape_string($db, $category);
  $content = mysqli_real_escape_string($db, $content);

  // 문의글 수정 SQL
  // 제목, 상담유형, 문의내용만 수정함
  $sql = "UPDATE hk_board
          SET title = '$title',
              category = '$category',
              message = '$content'
          WHERE no = $no";

  // SQL 실행
  $result = mysqli_query($db, $sql);

  // 수정 성공 여부 확인
  if($result){
    echo "
      <script>
        alert('문의글이 수정되었습니다.');
        location.href='./board_view.php?no=$no';
      </script>
    ";
  }else{
    echo "
      <script>
        alert('문의글 수정에 실패했습니다.');
        history.back();
      </script>
    ";
  }

  // DB 연결 종료
  mysqli_close($db);
?>