<?php
  // 관리자 로그인 체크
  include "./admin_check.php";

  // 한글 깨짐 방지
  header("Content-Type:text/html; charset=utf-8");

  // DB 연결
  include "../common/db.php";

  // form에서 넘어온 값 받기
  $title = $_POST['title'] ?? '';
  $content = $_POST['content'] ?? '';

  // 제목 확인
  if($title == ''){
    echo "
      <script>
        alert('공지사항 제목을 입력해주세요.');
        history.back();
      </script>
    ";
    exit;
  }

  // 내용 확인
  if($content == ''){
    echo "
      <script>
        alert('공지사항 내용을 입력해주세요.');
        history.back();
      </script>
    ";
    exit;
  }

  // DB 저장 전 특수문자 처리
  $title = mysqli_real_escape_string($db, $title);
  $content = mysqli_real_escape_string($db, $content);

  // 기존 공지사항 1개 확인
  $check_sql = "SELECT no FROM hk_board_notice ORDER BY no ASC LIMIT 1";
  $check_result = mysqli_query($db, $check_sql);
  $notice = mysqli_fetch_array($check_result, MYSQLI_ASSOC);

  // 공지사항이 있으면 수정, 없으면 새로 등록
  if($notice){
    $notice_no = (int)$notice['no'];

    $sql = "UPDATE hk_board_notice
            SET title = '$title',
                content = '$content',
                updated_at = NOW()
            WHERE no = $notice_no";
  }else{
    $sql = "INSERT INTO hk_board_notice (title, content, updated_at)
            VALUES ('$title', '$content', NOW())";
  }

  // SQL 실행
  $result = mysqli_query($db, $sql);

  // 저장 결과 확인
  if($result){
    echo "
      <script>
        alert('공지사항이 저장되었습니다.');
        location.href='./board_notice_edit.php?v=".time()."';
      </script>
    ";
  }else{
    echo "
      <script>
        alert('공지사항 저장에 실패했습니다.');
        history.back();
      </script>
    ";
  }

  // DB 연결 종료
  mysqli_close($db);
?>