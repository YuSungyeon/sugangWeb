<?php
session_start();
header('Content-Type: application/json');

require_once $_SERVER['DOCUMENT_ROOT'].'/sugang/include/db.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (!isset($_SESSION['userID'])) {
    echo json_encode(['success' => false, 'message' => '로그인이 필요합니다.']);
    exit;
}

$userID = $_SESSION['userID'];

// JSON 입력 받기
$input = json_decode(file_get_contents('php://input'), true);

$courseCode = $input['courseCode'] ?? '';
$priority = $input['priority'] ?? null;
$timeDiff = $input['timeDiff'] ?? null;

if (!$courseCode || $priority === null || $timeDiff === null) {
    echo json_encode(['success' => false, 'message' => '필수 정보가 누락되었습니다.']);
    exit;
}

// 중복 신청 확인
$check_sql = "SELECT * FROM course_enrollment WHERE user_id = ? AND course_code = ?";
$stmt = mysqli_prepare($con, $check_sql);
mysqli_stmt_bind_param($stmt, "ss", $userID, $courseCode);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) > 0) {
    echo json_encode(['success' => false, ']()_
