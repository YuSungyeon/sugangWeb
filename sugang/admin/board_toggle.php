<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/sugang/admin/include/admin_check.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/sugang/include/db.php';

if (!isset($_POST['id'])) {
    echo "<script>alert('잘못된 접근입니다.'); history.back();</script>";
    exit;
}

$id = intval($_POST['id']);

// 현재 상태 조회
$stmt = $con->prepare("SELECT 상태 FROM 게시글 WHERE 게시글ID = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    $currentStatus = $row['상태'];
    $newStatus = ($currentStatus == 1) ? 0 : 1;

    // 상태 토글
    $updateStmt = $con->prepare("UPDATE 게시글 SET 상태 = ? WHERE 게시글ID = ?");
    $updateStmt->bind_param("ii", $newStatus, $id);
    $updateStmt->execute();
    $updateStmt->close();

    $message = $newStatus ? '복원되었습니다.' : '삭제되었습니다.';
    echo "<script>alert('$message'); location.href='manage_board.php';</script>";
} else {
    echo "<script>alert('해당 게시글을 찾을 수 없습니다.'); history.back();</script>";
}

$stmt->close();
$con->close();
?>
