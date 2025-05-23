<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'].'/sugang/include/db.php';

if (!isset($_SESSION['userID']) || !isset($_POST['게시글ID'], $_POST['내용'])) {
    echo "<script>alert('잘못된 요청입니다.'); history.back();</script>";
    exit;
}

$작성자 = $_SESSION['userID'];
$게시글ID = intval($_POST['게시글ID']);
$내용 = trim($_POST['내용']);

if ($내용 === '') {
    echo "<script>alert('내용을 입력해주세요.'); history.back();</script>";
    exit;
}

$sql = "INSERT INTO 댓글 (게시글ID, 작성자, 내용) VALUES (?, ?, ?)";
$stmt = $con->prepare($sql);
$stmt->bind_param("iis", $게시글ID, $작성자, $내용);

if ($stmt->execute()) {
    header("Location: /sugang/board/board_view.php?id=$게시글ID");
} else {
    echo "<script>alert('댓글 작성에 실패했습니다.'); history.back();</script>";
}
$stmt->close();
$con->close();
?>
