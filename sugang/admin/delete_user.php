<?php
// 데이터베이스 연결 설정 & 관리자 로그인 상태 확인
require_once $_SERVER['DOCUMENT_ROOT'].'/sugang/include/db.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/sugang/admin/include/admin_check.php';

// POST로 전달된 학번 확인
if (!isset($_POST['학번'])) {
    die('잘못된 접근입니다.');
}

$학번 = intval($_POST['학번']);

// 본인 계정 삭제 방지
if ($_SESSION['userID'] == $학번) {
    die('본인 계정은 삭제할 수 없습니다.');
}

// 사용자 존재 여부 확인
$stmt = $con->prepare("SELECT * FROM 사용자 WHERE 학번 = ?");
$stmt->bind_param("i", $학번);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die('해당 사용자가 존재하지 않습니다.');
}

// 사용자 삭제 진행
$stmt = $con->prepare("DELETE FROM 사용자 WHERE 학번 = ?");
$stmt->bind_param("i", $학번);

if ($stmt->execute()) {
    echo "<script>
        alert('사용자가 성공적으로 삭제되었습니다.');
        location.href = '/sugang/admin/manage_users.php';
    </script>";
} else {
    echo "<script>
        alert('사용자 삭제에 실패했습니다.');
        history.back();
    </script>";
}

$con->close();
?>
