<?php
  // 세션 시작
  // 로그인한 사용자의 정보를 사용하기 위해 필요함
  session_start();

  // 한글 깨짐 방지
  header("Content-Type:text/html; charset=utf-8");

  // DB 연결 파일 불러오기
  // $db 라는 변수로 MySQL에 연결됨
  include "../common/db.php";

  // 날짜/시간을 한국 시간 기준으로 설정
  date_default_timezone_set('Asia/Seoul');

  // 로그인 여부 확인
  // $_SESSION['user_id']가 없으면 로그인하지 않은 상태
  if(!isset($_SESSION['user_id'])){
    echo "
      <script>
        alert('로그인 후 이용해주세요.');
        location.href='../member_pg/login.php';
      </script>
    ";
    exit;
  }

  // 로그인한 회원 정보 가져오기
  // 이 값들은 login_ok.php에서 로그인 성공 시 저장해둔 세션 값
  $member_no = $_SESSION['user_no'];
  $writer = $_SESSION['user_name'];

  // board_write.php의 form에서 넘어온 값 받기
  // input/select/textarea의 name 값과 같아야 함
  $title = $_POST['title'] ?? '';
  $category = $_POST['category'] ?? '';
  $content = $_POST['content'] ?? '';

  // 필수 입력값 확인
  // 제목, 상담 유형, 내용 중 하나라도 비어 있으면 이전 페이지로 돌려보냄
  if($title == '' || $category == '' || $content == ''){
    echo "
      <script>
        alert('제목, 상담 유형, 내용을 모두 입력해주세요.');
        history.back();
      </script>
    ";
    exit;
  }

  // DB에 저장하기 전에 사용자가 입력한 값을 안전하게 처리
  // 따옴표나 특수문자로 인해 SQL 오류나 공격이 생기는 것을 줄여줌
  $writer = mysqli_real_escape_string($db, $writer);
  $title = mysqli_real_escape_string($db, $title);
  $category = mysqli_real_escape_string($db, $category);
  $content = mysqli_real_escape_string($db, $content);

  // 현재 날짜와 시간 만들기
  // 예: 2026-06-14 13:25:10
  $now = date('Y-m-d H:i:s');

  // 문의글을 hk_board 테이블에 저장하는 SQL
  // member_no: 작성한 회원 번호
  // writer: 작성자 이름
  // title: 제목
  // category: 상담 유형
  // message: 문의 내용
  // date: 작성 날짜
  $sql = "INSERT INTO hk_board(member_no, writer, title, category, message, date)
          VALUES('$member_no', '$writer', '$title', '$category', '$content', '$now')";

  // SQL 실행
  $result = mysqli_query($db, $sql);

  // 저장 성공 여부 확인
if($result){
  echo "
    <script>
      alert('문의글이 등록되었습니다.');

      // 문의글 등록 후 사용자 게시판으로 이동
      // 관리자는 admin_pg/board_list.php에서 따로 확인 가능
      location.href='../board_pg/board.php';
    </script>
  ";
}else{
    echo "
      <script>
        alert('문의글 등록에 실패했습니다.');
        history.back();
      </script>
    ";
  }

  // DB 연결 종료
  mysqli_close($db);
?>