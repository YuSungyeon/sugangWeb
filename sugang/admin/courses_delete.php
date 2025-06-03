<?php
// 데이터베이스 연결 설정 & 관리자 로그인 상태 확인
require_once $_SERVER['DOCUMENT_ROOT'].'/sugang/admin/include/admin_check.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/sugang/include/db.php';

if (!isset($_POST['학번'], $_POST['강의코드'])) {
    echo "<script>alert('잘못된 요청입니다.'); history.back();</script>";
    exit;
}

$학번 = intval($_POST['학번']);
$강의코드 = $_POST['강의코드'];

$stmt = $con->prepare("DELETE FROM 수강신청 WHERE 학번 = ? AND 강의코드 = ?");
$stmt->bind_param("is", $학번, $강의코드);
$stmt->execute();

echo "<script>alert('삭제되었습니다.'); location.href='manage_courses.php';</script>";

$stmt->close();
$con->close();
?>
