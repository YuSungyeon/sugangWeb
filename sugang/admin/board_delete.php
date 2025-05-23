<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/sugang/admin/include/admin_check.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/sugang/include/db.php';

if (!isset($_POST['id'])) {
    echo "<script>alert('잘못된 접근입니다.'); history.back();</script>";
    exit;
}

$id = intval($_POST['id']);

$stmt = $con->prepare("DELETE FROM 게시글 WHERE 게시글ID = ?");
$stmt->bind_param("i", $id);
$stmt->execute();

echo "<script>alert('삭제되었습니다.'); location.href='manage_board.php';</script>";

$stmt->close();
$con->close();
?>
