<?php
session_start();

// 데이터베이스 연결 설정 & 로그인 상태 확인
require_once $_SERVER['DOCUMENT_ROOT'].'/sugang/include/db.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/sugang/include/check_login.php';

// 필수 파라미터 유효성 검사
if (!isset($_GET['id'], $_GET['post'])) {
    echo "<script>alert('잘못된 요청입니다.'); history.back();</script>";
    exit;
}

// 파라미터 정리
$댓글ID = intval($_GET['id']);
$게시글ID = intval($_GET['post']);
$사용자 = $_SESSION['userID'];

// 댓글 작성자 확인 (본인이 작성한 댓글인지 확인) ────────────────────────────────
$sql = "SELECT 작성자 FROM 댓글 WHERE 댓글ID = ?";
$stmt = $con->prepare($sql);
$stmt->bind_param("i", $댓글ID);
$stmt->execute();
$result = $stmt->get_result();
$comment = $result->fetch_assoc();
/* ──────────────────────────────────────────────────────────────── */

// 작성자가 본인이 아닌 경우 삭제 불가 처리
if (!$comment || $comment['작성자'] != $사용자) {
    echo "<script>alert('삭제 권한이 없습니다.'); history.back();</script>";
    exit;
}


// 댓글 삭제 쿼리 ────────────────────────────────
$sql = "DELETE FROM 댓글 WHERE 댓글ID = ?";
$stmt = $con->prepare($sql);
$stmt->bind_param("i", $댓글ID);
/* ──────────────────────────────────────────────────────────────── */

// 삭제 성공 후 해당 게시글로 복귀, 실패 시 에러 알림
if ($stmt->execute()) {
    header("Location: /sugang/board/board_view.php?id=$게시글ID");
} else {
    echo "<script>alert('댓글 삭제 실패'); history.back();</script>";
}
