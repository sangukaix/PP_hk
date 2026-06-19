<?php
  // 관리자 로그인 체크
  include "./admin_check.php";

  // 한글 깨짐 방지
  header("Content-Type:text/html; charset=utf-8");

  // DB 연결
  include "../common/db.php";

  // 출력 보안 함수
  function h($str){
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
  }

  // 홀드 신청 목록 가져오기
  $sql = "
    SELECT
      hr.*,

      m.user_id,
      m.user_name,

      p.course_name,

      s.lesson_date,
      s.lesson_day,
      s.lesson_time,
      s.teacher_name,
      s.attendance_status

    FROM hk_hold_requests hr

    LEFT JOIN hk_members m
    ON hr.member_no = m.no

    LEFT JOIN hk_payments p
    ON hr.payment_no = p.no

    LEFT JOIN hk_lesson_schedule s
    ON hr.lesson_no = s.no

    ORDER BY hr.request_date DESC, hr.no DESC
  ";

  $result = mysqli_query($db, $sql);
?>

<!DOCTYPE html>
<html lang="ko">
<head>
  <meta charset="UTF-8">
  <title>Hello Korean - 홀드 신청 관리</title>

  <link rel="stylesheet" href="./admin.css">
</head>

<body>

<div class="admin_wrap">

  <h2>홀드 신청 관리</h2>

  <div style="margin-bottom:20px;">
    <a href="./admin.php?tab=lesson" style="font-size:14px; color:#1486b8;">
      ← 수강생관리로 돌아가기
    </a>
  </div>

  <table class="admin_table">
    <thead>
      <tr>
        <th>번호</th>
        <th>학생</th>
        <th>과정명</th>
        <th>수업일</th>
        <th>시간</th>
        <th>강사</th>
        <th>status</th>
        <th>신청사유</th>
        <th>신청상태/결과</th>
        <th>신청일</th>
        <th>처리</th>
      </tr>
    </thead>

    <tbody>

      <?php
        if($result && mysqli_num_rows($result) > 0){
          while($row = mysqli_fetch_array($result, MYSQLI_ASSOC)){
      ?>

        <tr>
          <td><?php echo h($row['no']); ?></td>

          <td>
            <?php echo h($row['user_name']); ?><br>
            <span style="font-size:12px; color:#777;">
              <?php echo h($row['user_id']); ?>
            </span>
          </td>

          <td><?php echo h($row['course_name']); ?></td>

          <td>
            <?php echo h($row['lesson_date']); ?><br>
            <span style="font-size:12px; color:#777;">
              <?php echo h($row['lesson_day']); ?>요일
            </span>
          </td>

          <td><?php echo h($row['lesson_time']); ?></td>

          <td><?php echo h($row['teacher_name']); ?></td>

          <td>
            <?php
                // 관리자 화면에서 보여줄 status 만들기
                $display_status = $row['attendance_status'];

                if($row['request_status'] == "대기"){

                if($row['request_type'] == "홀드신청"){
                    $display_status = "홀드신청중";
                }else if($row['request_type'] == "홀드취소"){
                    $display_status = "홀드취소요청중";
                }

                }

                echo h($display_status);
            ?>
            </td>

          <td style="text-align:left;">
            <?php echo nl2br(h($row['hold_reason'])); ?>
          </td>

          <td>
            <?php echo h($row['request_status']); ?>
          </td>

          <td><?php echo h($row['request_date']); ?></td>
          <td>
            <?php
                if($row['request_status'] == "대기"){
            ?>

                <a
                href="./hold_request_update_ok.php?no=<?php echo h($row['no']); ?>&action=approve"
                onclick="return confirm('이 홀드 신청을 승인하시겠습니까?');"
                style="display:inline-block; padding:5px 9px; background-color:#1486b8; color:#fff; font-size:12px; border-radius:4px; margin-bottom:4px;"
                >
                승인
                </a>

                <a
                href="./hold_request_update_ok.php?no=<?php echo h($row['no']); ?>&action=reject"
                onclick="return confirm('이 홀드 신청을 반려하시겠습니까?');"
                style="display:inline-block; padding:5px 9px; background-color:#777; color:#fff; font-size:12px; border-radius:4px;"
                >
                반려
                </a>

            <?php
                }else{
            ?>

                <span style="font-size:12px; color:#777;">
                처리완료
                </span>

            <?php
                }
            ?>
            </td>
        </tr>

      <?php
          }
        }else{
      ?>

        <tr>
          <td colspan="11" style="text-align:center; padding:30px;">
            접수된 홀드 신청이 없습니다.
          </td>
        </tr>

      <?php
        }
      ?>

    </tbody>
  </table>

</div>

</body>
</html>