<?php
session_start();

// 데이터베이스 연결 설정 & 로그인 상태 확인
require_once $_SERVER['DOCUMENT_ROOT'].'/sugang/include/db.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/sugang/include/check_login.php';

// 필수 파라미터 유효성 검사
if (!isset($_POST['댓글ID'], $_POST['게시글ID'], $_POST['내용'])) {
    echo "<script>alert('잘못된 요청입니다.'); history.back();</script>";
    exit;
}

// 입력값 추출 및 정리
$댓글ID = intval($_POST['댓글ID']);      // 댓글 고유 ID
$게시글ID = intval($_POST['게시글ID']);   // 댓글이 달린 게시글 ID
$내용 = trim($_POST['내용']);            // 수정할 댓글 내용
$사용자 = $_SESSION['userID'];          // 현재 로그인한 사용자 ID

// 내용 예외 처리
if ($내용 === '') {
    echo "<script>alert('내용을 입력해주세요.'); history.back();</script>";
    exit;
}

// 댓글 작성자 확인 (본인이 작성한 댓글인지 확인) ────────────────────────────────
$sql = "SELECT 작성자 FROM 댓글 WHERE 댓글ID = ?";
$stmt = $con->prepare($sql);
$stmt->bind_param("i", $댓글ID);
$stmt->execute();
$result = $stmt->get_result();
$comment = $result->fetch_assoc();
/* ──────────────────────────────────────────────────────────────── */

// 작성자가 본인이 아닌 경우 수정 불가 처리
if (!$comment || $comment['작성자'] != $사용자) {
    echo "<script>alert('수정 권한이 없습니다.'); history.back();</script>";
    exit;
}

// 댓글 내용 수정 쿼리 (수정시간도 현재 시간으로 업데이트) ────────────────────────────────
$sql = "UPDATE 댓글 SET 내용 = ?, 수정시간 = NOW() WHERE 댓글ID = ?";
$stmt = $con->prepare($sql);
$stmt->bind_param("si", $내용, $댓글ID);
/* ──────────────────────────────────────────────────────────────── */

// 수정 성공 후 해당 게시글로 복귀, 실패 시 에러 알림
if ($stmt->execute()) {
    header("Location: /sugang/board/board_view.php?id=$게시글ID");
} else {
    echo "<script>alert('댓글 수정 실패'); history.back();</script>";
}
