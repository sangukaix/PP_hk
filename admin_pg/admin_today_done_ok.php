<?php
  // 관리자 로그인 체크
  include "./admin_check.php";

  // 한글 깨짐 방지
  header("Content-Type:text/html; charset=utf-8");

  // DB 연결
  include "../common/db.php";

  // 어떤 항목을 처리완료할지 받기
  $done_type = $_POST['done_type'] ?? '';

  // 신규 가입자 처리완료
  if($done_type == "member"){

    $sql = "
      UPDATE hk_members
      SET admin_checked_at = NOW()
      WHERE admin_checked_at IS NULL
    ";

    mysqli_query($db, $sql);
  }

  // 문의글 처리완료
  else if($done_type == "board"){

    $sql = "
      UPDATE hk_board
      SET admin_checked_at = NOW()
      WHERE admin_checked_at IS NULL
    ";

    mysqli_query($db, $sql);
  }

  // 신규 결제 처리완료
  else if($done_type == "payment"){

    $sql = "
      UPDATE hk_payments
      SET admin_checked_at = NOW()
      WHERE admin_checked_at IS NULL
    ";

    mysqli_query($db, $sql);
  }

  // 홀드/홀드취소 신청 처리완료
  else if($done_type == "hold"){

    $sql = "
      UPDATE hk_hold_requests
      SET admin_checked_at = NOW()
      WHERE admin_checked_at IS NULL
      AND request_status = '대기'
    ";

    mysqli_query($db, $sql);
  }

  else{
    echo "
      <script>
        alert('잘못된 접근입니다.');
        history.back();
      </script>
    ";
    exit;
  }

  mysqli_close($db);

  echo "
    <script>
      location.href='./admin.php?tab=member';
    </script>
  ";
?>