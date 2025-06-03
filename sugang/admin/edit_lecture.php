<?php
// 데이터베이스 연결 설정 & 관리자 로그인 상태 확인
require_once $_SERVER['DOCUMENT_ROOT'].'/sugang/admin/include/admin_check.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/sugang/include/db.php';

// 필수 파라미터 확인
if (!isset($_GET['code'])) { // 강의 코드 확인
    header("Location: manage_lectures.php");
    exit;
}

$code = $_GET['code']; // 강의 코드

// ────────── 기존 강의 정보 조회 ──────────
$stmt = $con->prepare(
    "SELECT 강의코드, 강의명, 교수명, 최대인원 
    FROM 강의 
    WHERE 강의코드 = ?"
);
$stmt->bind_param("s", $code);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) { // 해당 강의 존재하지 않으면 복귀
    header("Location: manage_lectures.php");
    exit;
}

$lecture = $result->fetch_assoc(); // 조회 결과 저장
$stmt->close();

// ────────── 폼 제출 시 강의 정보 수정 처리 ──────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $강의명 = trim($_POST['강의명']);
    $교수명 = trim($_POST['교수명']);
    $최대인원 = intval($_POST['최대인원']);

    // 유효성 검사: 최대인원이 1 이상인지 확인
    if ($최대인원 <= 0) {
        $error = "모든 항목을 올바르게 입력해주세요.";
    } else {
        // UPDATE 쿼리 실행
        $stmt = $con->prepare("UPDATE 강의 SET 강의명 = ?, 교수명 = ?, 최대인원 = ? WHERE 강의코드 = ?");
        $stmt->bind_param("ssis", $강의명, $교수명, $최대인원, $code);
        $stmt->execute();

        // 변경사항이 반영되었는지 여부 확인
        if ($stmt->affected_rows >= 0) {
            header("Location: manage_lectures.php");
            exit;
        } else {
            $error = "수정에 실패했습니다.";
        }
        $stmt->close();
    }
}

include $_SERVER['DOCUMENT_ROOT'].'/sugang/include/header.php';
?>

<h2>강의 수정</h2>


<!-- ────────── 에러 메시지 출력 (예외처리 부분 출력) ────────── -->
<?php if (isset($error)): ?>
    <p style="color:red;"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>

<!-- ────────── 수정 폼 ────────── -->
<form action="edit_lecture.php?code=<?= urlencode($code) ?>" method="post">
    <p>강의코드: <?= htmlspecialchars($lecture['강의코드']) ?></p>
    <label>강의명: <input type="text" name="강의명" value="<?= htmlspecialchars($lecture['강의명']) ?>"></label><br><br>
    <label>담당 교수: <input type="text" name="교수명" value="<?= htmlspecialchars($lecture['교수명']) ?>"></label><br><br>
    <label>최대인원: <input type="number" name="최대인원" min="1" max="300" value="<?= htmlspecialchars($lecture['최대인원']) ?>" required></label><br><br>
    <button type="submit">수정</button>
</form>

<?php
include $_SERVER['DOCUMENT_ROOT'].'/sugang/include/footer.php';
$con->close();
?>
