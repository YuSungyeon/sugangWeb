<?php
session_start();

// 로그인 여부 확인
if (!isset($_SESSION['userID'])) {
    echo "<script>alert('로그인이 필요합니다.'); location.href='/sugang/login.php';</script>";
    exit;
}

// 관리자 권한 확인
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    echo "<script>alert('관리자만 접근 가능합니다.'); location.href='/sugang/index.php';</script>";
    exit;
}