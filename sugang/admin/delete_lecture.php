<?php
// 데이터베이스 연결 설정 & 관리자 로그인 상태 확인
require_once $_SERVER['DOCUMENT_ROOT'].'/sugang/admin/include/admin_check.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/sugang/include/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['강의코드'])) {
    $code = $_POST['강의코드'];
    $return_url = $_POST['return_url'] ?? '/sugang/admin/manage_lectures.php';

    // 삭제 진행
    $stmt = $con->prepare("DELETE FROM 강의 WHERE 강의코드 = ?");
    $stmt->bind_param("s", $code);
    $stmt->execute();

    $stmt->close();
}

// 삭제 후 원래 페이지로 복귀
header("Location: " . $return_url);
exit;

