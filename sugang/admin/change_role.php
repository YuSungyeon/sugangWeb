<?php
session_start();
// 데이터베이스 연결 설정 & 관리자 로그인 상태 확인
require_once $_SERVER['DOCUMENT_ROOT'].'/sugang/include/db.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/sugang/admin/include/admin_check.php';


// 필수 파라미터 확인
if (!isset($_POST['학번'])) {
    echo "<script>alert('잘못된 요청입니다.'); history.back();</script>";
    exit;
}

$학번 = intval($_POST['학번']); // 대상 사용자 학번

// 자기 자신 권한 변경 방지 ─────────────
if ($_SESSION['userID'] == $학번) {
    echo "<script>alert('자기 자신의 권한은 변경할 수 없습니다.'); history.back();</script>";
    exit;
}

// 현재 권한 확인 ─────────────
$stmt = $con->prepare("SELECT 관리자여부 FROM 사용자 WHERE 학번 = ?");
$stmt->bind_param("i", $학번);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows == 0) {
    echo "<script>alert('해당 사용자를 찾을 수 없습니다.'); history.back();</script>";
    exit;
}
$current = $result->fetch_assoc()['관리자여부']; // 현재 권한 상태 (1: 관리자, 0: 사용자)
$stmt->close();

// 변경할 권한 결정 ─────────────
$newRole = ($current == 1) ? 0 : 1; // 관리자였다면 사용자로, 사용자였다면 관리자로 전환

// 관리자 -> 사용자로 변경 시 코드(sugang) 입력 필요 ─────────────
if ($current == 1 && $newRole == 0) {
    if (!isset($_POST['승인코드']) || trim($_POST['승인코드']) !== 'sugang') {
        echo "<script>alert('관리자 권한을 해제하려면 \"정확한 승인코드\"을 입력해야 합니다.'); history.back();</script>";
        exit;
    }
}

// ───────────── 권한 변경 실행 ─────────────
$stmt = $con->prepare("UPDATE 사용자 SET 관리자여부 = ? WHERE 학번 = ?");
$stmt->bind_param("ii", $newRole, $학번);
$stmt->execute();

echo "<script>alert('권한이 변경되었습니다.'); location.href='manage_users.php';</script>";

$stmt->close();
$con->close();
