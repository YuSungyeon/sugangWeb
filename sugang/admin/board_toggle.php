<?php
// 데이터베이스 연결 설정 & 관리자 로그인 상태 확인
require_once $_SERVER['DOCUMENT_ROOT'].'/sugang/admin/include/admin_check.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/sugang/include/db.php';

// 필수 파라미터 확인
if (!isset($_POST['id'])) {
    echo "<script>alert('잘못된 접근입니다.'); history.back();</script>";
    exit;
}

$id = intval($_POST['id']);

// ───────────── 게시글 상태 조회 ─────────────
$stmt = $con->prepare("SELECT 상태 FROM 게시글 WHERE 게시글ID = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    $currentStatus = $row['상태']; // 현재 상태값 (1: 게시, 0: 삭제)
    $newStatus = ($currentStatus == 1) ? 0 : 1; // 상태 반전 (토글)

    // ───────────── 상태 업데이트 쿼리 실행 ─────────────
    $updateStmt = $con->prepare("UPDATE 게시글 SET 상태 = ? WHERE 게시글ID = ?");
    $updateStmt->bind_param("ii", $newStatus, $id);
    $updateStmt->execute();
    $updateStmt->close();

    // ───────────── 사용자 피드백 및 이동 ─────────────
    $message = $newStatus ? '복원되었습니다.' : '삭제되었습니다.';
    echo "<script>alert('$message'); location.href='manage_board.php';</script>";
} else {
    // ───────────── 존재하지 않는 게시글 ID에 대한 처리 ─────────────
    echo "<script>alert('해당 게시글을 찾을 수 없습니다.'); history.back();</script>";
}

$stmt->close();
$con->close();
?>
