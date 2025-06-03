<?php
session_start();

// 데이터베이스 연결 설정 & 로그인 상태 확인 & DB 설정
require_once $_SERVER['DOCUMENT_ROOT'].'/sugang/include/db.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/sugang/include/check_login.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/sugang/include/db.php';

// 필수 파라미터 유효성 검사
if (!isset($_POST['게시글ID'], $_POST['내용'])) {
    echo "<script>alert('잘못된 요청입니다.'); history.back();</script>";
    exit;
}

// 입력값 추출 및 정리
$작성자 = $_SESSION['userID'];
$게시글ID = intval($_POST['게시글ID']);
$내용 = trim($_POST['내용']);

// 내용 예외 처리
if ($내용 === '') {
    echo "<script>alert('내용을 입력해주세요.'); history.back();</script>";
    exit;
}

// 댓글 추가 쿼리 실행 ──────────────────────────────────────────────
$sql = "INSERT INTO 댓글 (게시글ID, 작성자, 내용) VALUES (?, ?, ?)";
$stmt = $con->prepare($sql);
$stmt->bind_param("iis", $게시글ID, $작성자, $내용);
/* ──────────────────────────────────────────────────────────────── */

// 추가 성공 후 해당 게시글로 복귀, 실패 시 에러 알림
if ($stmt->execute()) {
    header("Location: /sugang/board/board_view.php?id=$게시글ID");
} else {
    echo "<script>alert('댓글 작성에 실패했습니다.'); history.back();</script>";
}
$stmt->close();
$con->close();
?>
