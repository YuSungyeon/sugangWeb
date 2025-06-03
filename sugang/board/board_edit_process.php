<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'].'/sugang/include/db.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id = intval($_POST['id']);
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    $userID = $_SESSION['userID'];

    // 작성자 확인
    $check_sql = "SELECT 작성자 FROM 게시글 WHERE 게시글ID = ?";
    $check_stmt = $con->prepare($check_sql);
    $check_stmt->bind_param("i", $id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    $check_row = $check_result->fetch_assoc();

    if (!$check_row || $check_row['작성자'] != $userID) {
        echo "<script>alert('수정 권한이 없습니다.'); location.href='/sugang/board/board_list.php';</script>";
        exit;
    }

    $sql = "UPDATE 게시글 SET 제목 = ?, 내용 = ?, 수정시간 = NOW() WHERE 게시글ID = ?";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("ssi", $title, $content, $id);

    if ($stmt->execute()) {
        echo "<script>location.href='board_view.php?id=$id';</script>";
    } else {
        echo "수정 실패: " . $stmt->error;
    }
}
?>
