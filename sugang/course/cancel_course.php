<?php
session_start();
header('Content-Type: application/json');

// 데이터베이스 연결 설정
require_once $_SERVER['DOCUMENT_ROOT'].'/sugang/include/db.php';

// 로그인 체크
if (!isset($_SESSION['userID'])) {
    echo json_encode(['success' => false, 'message' => '로그인이 필요합니다.']);
    exit;
}

// JSON 형식의 POST 데이터 읽기
$input = json_decode(file_get_contents('php://input'), true);

// 강의코드 상태 확인
if (!isset($input['code'])) {
    echo json_encode(['success' => false, 'message' => '강의 코드가 전달되지 않았습니다.']);
    exit;
}

// 사용자 ID 및 요청된 강의 코드 추출
$userID = $_SESSION['userID'];
$code = $input['code'];

// 수강신청 테이블에서 해당 사용자의 강의코드 삭제
/* ──────────────────────────────── */
$stmt = $con->prepare("DELETE FROM 수강신청 WHERE 학번 = ? AND 강의코드 = ?");
if (!$stmt) {
    echo json_encode(['success' => false, 'message' => '쿼리 준비 중 오류 발생: ' . $con->error]);
    exit;
}
$stmt->bind_param("ss", $userID, $code);
$stmt->execute();

// 삭제 성공 여부에 따라 응답
if ($stmt->affected_rows > 0) {
    echo json_encode(['success' => true, 'message' => '수강이 취소되었습니다.']);
} else {
    echo json_encode(['success' => false, 'message' => '해당 수강내역이 없습니다.']);
}
/* ──────────────────────────────── */

$stmt->close();
?>
