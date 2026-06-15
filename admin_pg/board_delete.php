<?php
  // 관리자 로그인 체크
  // 관리자 로그인이 안 되어 있으면 admin_login.php로 이동
  include "./admin_check.php";

  // 한글 깨짐 방지
  header("Content-Type:text/html; charset=utf-8");

  // DB 연결 파일 불러오기
  include "../common/db.php";

  // admin.php의 삭제 form에서 넘어온 문의글 번호 받기
  $no = $_POST['no'] ?? '';

  // 글 번호가 없으면 잘못된 접근으로 처리
  if($no == ''){
    echo "
      <script>
        alert('잘못된 접근입니다.');
        location.href='./admin.php?tab=board';
      </script>
    ";
    exit;
  }

  // 글 번호는 숫자로만 사용하기 위해 정수로 변환
  $no = (int)$no;

  // hk_board 테이블에서 해당 번호의 문의글 삭제
  $sql = "DELETE FROM hk_board WHERE no = $no";

  // SQL 실행
  $result = mysqli_query($db, $sql);

  // 삭제 성공 여부 확인
  if($result){
    echo "
      <script>
        alert('문의글이 삭제되었습니다.');
        location.href='./admin.php?tab=board';
      </script>
    ";
  }else{
    echo "
      <script>
        alert('문의글 삭제에 실패했습니다.');
        history.back();
      </script>
    ";
  }

  // DB 연결 종료
  mysqli_close($db);
?>