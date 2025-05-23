<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'].'/sugang/include/db.php';

if (!isset($_GET['id'])) {
    echo "<script>alert('잘못된 접근입니다.'); location.href='/sugang/board/board_list.php';</script>";
    exit;
}

$id = intval($_GET['id']);
$userID = $_SESSION['userID'];

// 작성자 확인
$sql = "SELECT 작성자 FROM 게시글 WHERE 게시글ID = ?";
$stmt = $con->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

if (!$_SESSION['is_admin']){
    if (!$row || $row['작성자'] != $userID) {
        echo "<script>alert('삭제 권한이 없습니다.'); location.href='/sugang/board/board_list.php';</script>";
        exit;
    }
}

$delete_sql = "DELETE FROM 게시글 WHERE 게시글ID = ?";
$delete_stmt = $con->prepare($delete_sql);
$delete_stmt->bind_param("i", $id);
$delete_stmt->execute();

echo "<script>alert('삭제되었습니다.'); location.href='/sugang/board/board_list.php';</script>";
?>
