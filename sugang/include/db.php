<?php
// ───── 데이터베이스 접속 정보 설정
$db_host     = "localhost"; // 데이터베이스 호스트명
$db_user     = "cookUser";  // DB 사용자 계정명
$db_password = "1234";      // DB 사용자 비밀번호
$db_name     = "sugangDB";  // 사용할 데이터베이스 이름

// ───── MySQL 데이터베이스에 연결
$con = mysqli_connect($db_host, $db_user, $db_password, $db_name);

// 연결 실패 확인
if (!$con) {
    die("접속 실패: " . mysqli_connect_error());
}

// ───── 문자 인코딩을 UTF-8로 설정 (한글 깨짐 방지)
mysqli_set_charset($con, "utf8mb4");
?>