<?php
    session_start();

    header("Content-Type:text/html; charset=utf-8");

    include "../common/db.php";

    $user_id = $_POST['user_id'] ?? '';
    $user_pw = $_POST['user_pw'] ?? '';

    if($user_id == '' || $user_pw == ''){
        echo "
            <script>
                alert('아이디와 비밀번호를 입력해주세요.');
                history.back();
            </script>
        ";
        exit;
    }

    $user_id = mysqli_real_escape_string($db, $user_id);

    $sql = "SELECT * FROM hk_members WHERE user_id='$user_id'";
    $result = mysqli_query($db, $sql);

    if($result){
        $row_num = mysqli_num_rows($result);

        if($row_num > 0){
            $row = mysqli_fetch_array($result, MYSQLI_ASSOC);

            if(password_verify($user_pw, $row['user_pw'])){
                $_SESSION['user_no'] = $row['no'];
                $_SESSION['user_id'] = $row['user_id'];
                $_SESSION['user_name'] = $row['user_name'];
                $_SESSION['role'] = $row['role'];

                echo "
                    <script>
                        alert('로그인 성공');
                        location.href='../main_pg/01main_pg.html';
                    </script>
                ";
            }else{
                echo "
                    <script>
                        alert('비밀번호가 틀렸습니다.');
                        history.back();
                    </script>
                ";
            }
        }else{
            echo "
                <script>
                    alert('존재하지 않는 아이디입니다.');
                    history.back();
                </script>
            ";
        }
    }else{
        echo "
            <script>
                alert('로그인 처리 중 오류가 발생했습니다.');
                history.back();
            </script>
        ";
    }

    mysqli_close($db);
?>