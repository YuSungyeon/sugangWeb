<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'].'/sugang/include/db.php';

if (!isset($_GET['id'], $_GET['post']) || !isset($_SESSION['userID'])) {
    echo "<script>alert('잘못된 요청입니다.'); history.back();</script>";
    exit;
}

$댓글ID = intval($_GET['id']);
$게시글ID = intval($_GET['post']);
$사용자 = $_SESSION['userID'];

$sql = "SELECT 작성자 FROM 댓글 WHERE 댓글ID = ?";
$stmt = $con->prepare($sql);
$stmt->bind_param("i", $댓글ID);
$stmt->execute();
$result = $stmt->get_result();
$comment = $result->fetch_assoc();

if (!$_SESSION['is_admin']) {
    if (!$comment || $comment['작성자'] != $사용자) {
        echo "<script>alert('삭제 권한이 없습니다.'); history.back();</script>";
        exit;
    }
}


$sql = "DELETE FROM 댓글 WHERE 댓글ID = ?";
$stmt = $con->prepare($sql);
$stmt->bind_param("i", $댓글ID);

if ($stmt->execute()) {
    header("Location: /sugang/board/board_view.php?id=$게시글ID");
} else {
    echo "<script>alert('댓글 삭제 실패'); history.back();</script>";
}
