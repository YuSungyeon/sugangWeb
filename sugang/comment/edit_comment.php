<?php
session_start();

// 데이터베이스 연결 설정 & 로그인 상태 확인
require_once $_SERVER['DOCUMENT_ROOT'].'/sugang/include/db.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/sugang/include/check_login.php';

// 필수 파라미터 유효성 검사
if (!isset($_GET['id'], $_GET['post'])) {
    echo "<script>alert('잘못된 접근입니다.'); history.back();</script>";
    exit;
}

// 파라미터 정리
$댓글ID = intval($_GET['id']);
$게시글ID = intval($_GET['post']);
$사용자 = $_SESSION['userID'];

// 댓글 정보 조회 (내용 및 작성자 확인) ────────────────────────────────
$sql = "SELECT 내용, 작성자 FROM 댓글 WHERE 댓글ID = ?";
$stmt = $con->prepare($sql);
$stmt->bind_param("i", $댓글ID);
$stmt->execute();
$result = $stmt->get_result();
$comment = $result->fetch_assoc();
/* ──────────────────────────────────────────────────────────────── */

// 권한 확인
if (!$comment || $comment['작성자'] != $사용자) {
    echo "<script>alert('수정 권한이 없습니다.'); history.back();</script>";
    exit;
}
?>

<!-- 댓글 수정 폼 -->
<h3>댓글 수정</h3>
<form action="update_comment.php" method="post">
    <!-- 숨겨진 필드로 댓글 ID와 게시글 ID 전달 -->
    <input type="hidden" name="댓글ID" value="<?= $댓글ID ?>">
    <input type="hidden" name="게시글ID" value="<?= $게시글ID ?>">

    <!-- 기존 댓글 내용을 textarea에 표시 -->
    <textarea name="내용" rows="3" style="width:100%;" required><?= htmlspecialchars($comment['내용']) ?></textarea><br>
    <button type="submit">수정 완료</button>
</form>
<p><a href="/sugang/board/board_view.php?id=<?= $게시글ID ?>">← 게시글로 돌아가기</a></p>
