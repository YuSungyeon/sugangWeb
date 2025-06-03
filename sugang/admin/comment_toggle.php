<?php
// 데이터베이스 연결 설정 & 관리자 로그인 상태 확인
require_once $_SERVER['DOCUMENT_ROOT'].'/sugang/admin/include/admin_check.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/sugang/include/db.php';

// 파라미터 유효성 확인
if (!isset($_POST['id'], $_POST['post'])) {
    echo "<script>alert('잘못된 접근입니다.'); history.back();</script>";
    exit;
}

// 파라미터 정리
$commentID = intval($_POST['id']);
$postID    = intval($_POST['post']);

// ───────────── 현재 댓글 상태 조회 ─────────────
$stmt = $con->prepare("SELECT 상태 FROM 댓글 WHERE 댓글ID = ?");
$stmt->bind_param("i", $commentID);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    // 현재 상태가 있으면 상태 토글
    $currentStatus = (int)$row['상태']; // 현재 상태: 1(활성), 0(비활성)
    $newStatus = ($currentStatus === 1) ? 0 : 1; // 토글 후 상태

    // ───────────── 상태 업데이트 쿼리 ─────────────
    $updateStmt = $con->prepare("UPDATE 댓글 SET 상태 = ? WHERE 댓글ID = ?");
    $updateStmt->bind_param("ii", $newStatus, $commentID);
    $updateStmt->execute();
    $updateStmt->close();

    echo "<script>location.href='/sugang/board/board_view.php?id={$postID}';</script>";
} else {
    echo "<script>alert('해당 댓글을 찾을 수 없습니다.'); history.back();</script>";
}

$stmt->close();
$con->close();
?>