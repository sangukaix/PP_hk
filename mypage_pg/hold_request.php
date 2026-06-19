<?php
  session_start();

  // 한글 깨짐 방지
  header("Content-Type:text/html; charset=utf-8");

  // 로그인 체크
  if(!isset($_SESSION['user_id'])){
    echo "
      <script>
        alert('로그인 후 이용해주세요.');
        location.href='../member_pg/login.php';
      </script>
    ";
    exit;
  }

  // DB 연결
  include "../common/db.php";

  // 출력 보안 함수
  function h($str){
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
  }

  // 로그인한 회원 번호
  $user_no = (int)$_SESSION['user_no'];

  // GET으로 넘어온 수업 번호
  $lesson_no = $_GET['lesson_no'] ?? '';
  $lesson_no = (int)$lesson_no;

  if($lesson_no < 1){
    echo "
      <script>
        alert('잘못된 접근입니다.');
        location.href='./schedule.php';
      </script>
    ";
    exit;
  }

  // 본인의 수업인지 확인하면서 수업 정보 가져오기
  $sql = "
    SELECT
      s.*,
      p.course_name,
      m.user_name
    FROM hk_lesson_schedule s
    LEFT JOIN hk_payments p
    ON s.payment_no = p.no
    LEFT JOIN hk_members m
    ON s.member_no = m.no
    WHERE s.no = '$lesson_no'
    AND s.member_no = '$user_no'
  ";

  $result = mysqli_query($db, $sql);

  if(!$result || mysqli_num_rows($result) < 1){
    echo "
      <script>
        alert('신청 가능한 수업을 찾을 수 없습니다.');
        location.href='./schedule.php';
      </script>
    ";
    exit;
  }

  $lesson = mysqli_fetch_array($result, MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="ko">
<head>
  <meta charset="UTF-8">
  <title>Hello Korean - 홀드신청</title>

  <link rel="stylesheet" href="../common/common.css">
  <link rel="stylesheet" href="./mypage.css">
</head>

<body>

<section id="mypage_visual">
  <div class="container">
    <h2>My Page</h2>
  </div>
</section>

<section id="mypage_content">
  <div class="container">

    <div class="mypage_nav">
      <ul>
        <li><a href="./mypage.php">수강신청현황</a></li>
        <li><a href="./schedule.php" class="active">수업스케줄</a></li>
        <li><a href="./profile.php">개인정보수정</a></li>
      </ul>
    </div>

    <div class="mypage_box schedule_box">

      <div class="lesson_title_row">
        <h4>홀드 신청</h4>
        <p>홀드를 신청할 수업 정보를 확인하고 사유를 입력해주세요.</p>
      </div>

      <form action="./hold_request_ok.php" method="post">

        <input type="hidden" name="lesson_no" value="<?php echo h($lesson['no']); ?>">
        <input type="hidden" name="payment_no" value="<?php echo h($lesson['payment_no']); ?>">

        <table class="hold_table">
          <tr>
            <th>학생명</th>
            <td><?php echo h($lesson['user_name']); ?></td>
          </tr>

          <tr>
            <th>과정명</th>
            <td><?php echo h($lesson['course_name']); ?></td>
          </tr>

          <tr>
            <th>수업일</th>
            <td><?php echo h($lesson['lesson_date']); ?> <?php echo h($lesson['lesson_day']); ?>요일</td>
          </tr>

          <tr>
            <th>수업시간</th>
            <td><?php echo h($lesson['lesson_time']); ?></td>
          </tr>

          <tr>
            <th>강사</th>
            <td><?php echo h($lesson['teacher_name']); ?> 강사님</td>
          </tr>

          <tr>
            <th>현재상태</th>
            <td><?php echo h($lesson['attendance_status']); ?></td>
          </tr>
        </table>

        <div class="form_row" style="margin-top:25px;">
          <label for="hold_reason">홀드 신청 사유</label>
          <textarea
            id="hold_reason"
            name="hold_reason"
            style="width:100%; height:120px; padding:12px; border:1px solid #ddd; border-radius:6px; box-sizing:border-box; resize:none;"
            placeholder="예: 개인 일정으로 해당 수업 홀드를 신청합니다."
          ></textarea>
        </div>

        <div class="hold_btn_area" style="display:flex; justify-content:space-between; align-items:center; margin-top:28px;">

        <!-- 왼쪽: 뒤로가기 -->
        <div>
            <a
            href="./schedule.php"
            style="display:inline-block; width:130px; height:42px; line-height:42px; text-align:center; background-color:#777; color:#fff; border-radius:6px; font-size:14px; font-weight:bold;"
            >
            뒤로가기
            </a>
        </div>

        <!-- 오른쪽: 홀드 신청 / 홀드 취소 -->
        <div style="display:flex; gap:8px;">

            <button
            type="submit"
            class="profile_submit_btn"
            >
            홀드 신청하기
            </button>

            <button
            type="submit"
            formaction="./hold_cancel_ok.php"
            formmethod="post"
            onclick="return confirm('홀드 신청 또는 승인된 홀드를 취소하시겠습니까?');"
            style="width:160px; height:42px; border:none; border-radius:6px; background-color:#f6b26b; color:#fff; font-size:15px; font-weight:bold; cursor:pointer;"
            >
            홀드 취소하기
            </button>

        </div>

        </div>

            <p style="margin-top:12px; font-size:13px; color:#777; line-height:1.5; text-align:right;">
            ※홀드 및 홀드 취소는 관리자 승인 전까지는 횟수가 차감되지 않습니다.
            </p>

      </form>

    </div>

  </div>
</section>

</body>
</html>