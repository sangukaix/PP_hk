<?php
    // DB 연결 파일

    $db = mysqli_connect("localhost", "sangukaix", "a1s2d3f4!", "sangukaix");

    mysqli_query($db, "set names utf8");

    if(!$db){
        echo "DB 연결 실패";
    }
?>