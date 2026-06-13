<?php
    header("Content-Type:text/html; charset=utf-8");

    include "../common/db.php";

    date_default_timezone_set('Asia/Seoul');

    $user_id = $_POST['user_id'] ?? '';
    $user_pw = $_POST['user_pw'] ?? '';
    $user_name = $_POST['user_name'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $email = $_POST['email'] ?? '';

    if($user_id == '' || $user_pw == '' || $user_name == ''){
        echo "
            <script>
                alert('아이디, 비밀번호, 이름은 반드시 입력해주세요.');
                history.back();
            </script>
        ";
        exit;
    }

    $user_id = mysqli_real_escape_string($db, $user_id);
    $user_name = mysqli_real_escape_string($db, $user_name);
    $phone = mysqli_real_escape_string($db, $phone);
    $email = mysqli_real_escape_string($db, $email);

    // 비밀번호 암호화
    $user_pw = password_hash($user_pw, PASSWORD_DEFAULT);
    $user_pw = mysqli_real_escape_string($db, $user_pw);

    $now = date('Y-m-d H:i:s');

    $sql = "INSERT INTO hk_members(user_id, user_pw, user_name, phone, email, date)
            VALUES('$user_id', '$user_pw', '$user_name', '$phone', '$email', '$now')";

    $result = mysqli_query($db, $sql);

    if($result){
        echo "
            <script>
                alert('회원가입이 완료되었습니다.');
                location.href='./login.php';
            </script>
        ";
    }else{
        echo "
            <script>
                alert('회원가입 실패. 이미 사용 중인 아이디일 수 있습니다.');
                history.back();
            </script>
        ";
    }

    mysqli_close($db);
?>