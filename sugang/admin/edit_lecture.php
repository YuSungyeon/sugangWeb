<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/sugang/admin/include/admin_check.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/sugang/include/db.php';

if (!isset($_GET['code'])) {
    header("Location: manage_lectures.php");
    exit;
}

$code = $_GET['code'];

// 기존 강의 정보 불러오기
$stmt = $con->prepare("SELECT 강의코드, 강의명, 교수명, 최대인원 FROM 강의 WHERE 강의코드 = ?");
$stmt->bind_param("s", $code);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: manage_lectures.php");
    exit;
}

$lecture = $result->fetch_assoc();
$stmt->close();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $강의명 = trim($_POST['강의명']);
    $교수명 = trim($_POST['교수명']);
    $최대인원 = intval($_POST['최대인원']);

    if ($강의명 === '' || $교수명 === '' || $최대인원 <= 0) {
        $error = "모든 항목을 올바르게 입력해주세요.";
    } else {
        $stmt = $con->prepare("UPDATE 강의 SET 강의명 = ?, 교수명 = ?, 최대인원 = ? WHERE 강의코드 = ?");
        $stmt->bind_param("ssis", $강의명, $교수명, $최대인원, $code);
        $stmt->execute();

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

<?php if (isset($error)): ?>
    <p style="color:red;"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>

<form action="edit_lecture.php?code=<?= urlencode($code) ?>" method="post">
    <p>강의코드: <?= htmlspecialchars($lecture['강의코드']) ?></p>
    <label>강의명: <input type="text" name="강의명" value="<?= htmlspecialchars($lecture['강의명']) ?>" required></label><br><br>
    <label>담당 교수: <input type="text" name="교수명" value="<?= htmlspecialchars($lecture['교수명']) ?>" required></label><br><br>
    <label>최대인원: <input type="number" name="최대인원" min="1" max="300" value="<?= htmlspecialchars($lecture['최대인원']) ?>" required></label><br><br>
    <button type="submit">수정</button>
</form>

<?php
include $_SERVER['DOCUMENT_ROOT'].'/sugang/include/footer.php';
$con->close();
?>
