<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/sugang/admin/include/admin_check.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/sugang/include/db.php';

if (!isset($_POST['id'], $_POST['post'])) {
    echo "<script>alert('잘못된 접근입니다.'); history.back();</script>";
    exit;
}

$commentID = intval($_POST['id']);
$postID    = intval($_POST['post']);

$stmt = $con->prepare("SELECT 상태 FROM 댓글 WHERE 댓글ID = ?");
$stmt->bind_param("i", $commentID);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    $currentStatus = (int)$row['상태'];
    $newStatus = ($currentStatus === 1) ? 0 : 1;

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