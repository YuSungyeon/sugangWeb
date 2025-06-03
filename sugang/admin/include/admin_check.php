<?php
session_start();

// 로그인 확인
require_once $_SERVER['DOCUMENT_ROOT'].'/sugang/include/check_login.php';

// 관리자 권한 확인
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    echo "<script>alert('관리자만 접근 가능합니다.'); location.href='/sugang/index.php';</script>";
    exit;
}