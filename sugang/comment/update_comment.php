<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'].'/sugang/include/db.php';

if (!isset($_POST['댓글ID'], $_POST['게시글ID'], $_POST['내용'], $_SESSION['userID'])) {
    echo "<script>alert('잘못된 요청입니다.'); history.back();</script>";
    exit;
}

$댓글ID = intval($_POST['댓글ID']);
$게시글ID = intval($_POST['게시글ID']);
$내용 = trim($_POST['내용']);
$사용자 = $_SESSION['userID'];

// 본인 댓글인지 확인
$sql = "SELECT 작성자 FROM 댓글 WHERE 댓글ID = ?";
$stmt = $con->prepare($sql);
$stmt->bind_param("i", $댓글ID);
$stmt->execute();
$result = $stmt->get_result();
$comment = $result->fetch_assoc();

if (!$comment || $comment['작성자'] != $사용자) {
    echo "<script>alert('수정 권한이 없습니다.'); history.back();</script>";
    exit;
}

$sql = "UPDATE 댓글 SET 내용 = ?, 수정시간 = NOW() WHERE 댓글ID = ?";
$stmt = $con->prepare($sql);
$stmt->bind_param("si", $내용, $댓글ID);

if ($stmt->execute()) {
    header("Location: /sugang/board/board_view.php?id=$게시글ID");
} else {
    echo "<script>alert('댓글 수정 실패'); history.back();</script>";
}
