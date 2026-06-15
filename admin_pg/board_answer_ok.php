<?php
  // 관리자 로그인 체크
  include "./admin_check.php";

  // 한글 깨짐 방지
  header("Content-Type:text/html; charset=utf-8");

  // DB 연결 파일 불러오기
  include "../common/db.php";

  // form에서 넘어온 값 받기
  $no = $_POST['no'] ?? '';
  $answer = $_POST['answer'] ?? '';

  // 글 번호가 없으면 잘못된 접근
  if($no == ''){
    echo "
      <script>
        alert('잘못된 접근입니다.');
        location.href='./admin.php';
      </script>
    ";
    exit;
  }

  // 답변 내용이 비어 있으면 다시 돌아감
  if($answer == ''){
    echo "
      <script>
        alert('답변 내용을 입력해주세요.');
        history.back();
      </script>
    ";
    exit;
  }

  // 글 번호는 숫자로 변환
  $no = (int)$no;

  // DB에 저장하기 전에 특수문자 처리
  $answer = mysqli_real_escape_string($db, $answer);

  // 한국 시간 설정
  date_default_timezone_set('Asia/Seoul');

  // 현재 시간 만들기
  $answer_date = date('Y-m-d H:i:s');

  // 관리자 답변 저장 SQL
  // 답변을 저장하면서 상태도 답변완료로 변경
  $sql = "UPDATE hk_board
          SET answer = '$answer',
              answer_date = '$answer_date',
              status = '답변완료'
          WHERE no = $no";

  // SQL 실행
  $result = mysqli_query($db, $sql);

  // 저장 결과 확인
  if($result){
    echo "
      <script>
        alert('답변이 저장되었습니다.');
        location.href='./board_view.php?no=$no';
      </script>
    ";
  }else{
    echo "
      <script>
        alert('답변 저장에 실패했습니다.');
        history.back();
      </script>
    ";
  }

  // DB 연결 종료
  mysqli_close($db);
?>