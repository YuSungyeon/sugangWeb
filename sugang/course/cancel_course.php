<?php
session_start();
header('Content-Type: application/json');
require_once $_SERVER['DOCUMENT_ROOT'].'/sugang/include/db.php';

// 로그인 체크
if (!isset($_SESSION['userID'])) {
    echo json_encode(['success' => false, 'message' => '로그인이 필요합니다.']);
    exit;
}

// POST로 JSON 데이터 받기
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['code'])) {
    echo json_encode(['success' => false, 'message' => '강의 코드가 전달되지 않았습니다.']);
    exit;
}

$userID = $_SESSION['userID'];
$code = $input['code'];

// 삭제 쿼리 실행
$stmt = $con->prepare("DELETE FROM 수강신청 WHERE 학번 = ? AND 강의코드 = ?");
if (!$stmt) {
    echo json_encode(['success' => false, 'message' => '쿼리 준비 중 오류 발생: ' . $con->error]);
    exit;
}
$stmt->bind_param("ss", $userID, $code);
$stmt->execute();

if ($stmt->affected_rows > 0) {
    echo json_encode(['success' => true, 'message' => '수강이 취소되었습니다.']);
} else {
    echo json_encode(['success' => false, 'message' => '해당 수강내역이 없습니다.']);
}

$stmt->close();
?>
