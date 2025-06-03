<?php
// 사용자 로그인 여부 확인
if (!isset($_SESSION['userID'])) {
    echo "<script>alert('로그인이 필요합니다.'); location.href='/sugang/user/login.php';</script>";
    exit;
}

// require_once $_SERVER['DOCUMENT_ROOT'].'/sugang/include/check_login.php';