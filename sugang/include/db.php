<?php
$db_host = "localhost";
$db_user = "cookUser";
$db_password = "1234";
$db_name = "sugangDB";

$con = mysqli_connect($db_host, $db_user, $db_password, $db_name);

if (!$con) {
    die("접속 실패: " . mysqli_connect_error());
}

// 한글 처리를 위한 문자셋 설정
mysqli_set_charset($con, "utf8mb4");
?>