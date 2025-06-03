<?php
session_start();

// 데이터베이스 연결 설정 & 로그인 상태 확인
require_once $_SERVER['DOCUMENT_ROOT'].'/sugang/include/db.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/sugang/include/check_login.php';

// 필수 파라미터 유효성 검사
if (!isset($_GET['id'])) {
    echo "<script>alert('잘못된 접근입니다.'); location.href='/sugang/board/board_list.php';</script>";
    exit;
}

$id = intval($_GET['id']); // 게시글 ID
$userID = $_SESSION['userID'];

// 본인 글인지 확인
$sql = "SELECT 작성자 FROM 게시글 WHERE 게시글ID = ?";
$stmt = $con->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

// 작성자가 아니거나 게시글이 없는 경우
if (!$row || $row['작성자'] != $userID) {
    echo "<script>alert('삭제 권한이 없습니다.'); location.href='/sugang/board/board_list.php';</script>";
    exit;
}

// 게시글 삭제 처리
$delete_sql = "DELETE FROM 게시글 WHERE 게시글ID = ?";
$delete_stmt = $con->prepare($delete_sql);
$delete_stmt->bind_param("i", $id);
$delete_stmt->execute();

echo "<script>alert('삭제되었습니다.'); location.href='/sugang/board/board_list.php';</script>";
?>
