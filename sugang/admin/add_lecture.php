<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/sugang/admin/include/admin_check.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/sugang/include/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $강의코드 = trim($_POST['강의코드']);
    $강의명 = trim($_POST['강의명']);
    $교수명 = trim($_POST['교수명']);
    $최대인원 = intval($_POST['최대인원']);

    if ($강의코드 === '' || $최대인원 <= 0) {
        $error = "모든 항목을 올바르게 입력해주세요.";
    } else {
        $stmt = $con->prepare("INSERT INTO 강의 (강의코드, 강의명, 교수명, 최대인원) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("sssi", $강의코드, $강의명, $교수명, $최대인원);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            header("Location: manage_lectures.php");
            exit;
        } else {
            $error = "강의 추가에 실패했습니다.";
        }
        $stmt->close();
    }
}

include $_SERVER['DOCUMENT_ROOT'].'/sugang/include/header.php';
?>

<h2>강의 추가</h2>

<?php if (isset($error)): ?>
    <p style="color:red;"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>

<form action="add_lecture.php" method="post">
    <label>강의코드: <input type="text" name="강의코드" maxlength="5" required placeholder="예: 12345(5자리)"></label><br><br>
    <label>강의명: <input type="text" name="강의명" maxlength="50"></label><br><br>
    <label>담당 교수: <input type="text" name="교수명" maxlength="30"></label><br><br>
    <label>최대 인원: <input type="number" name="최대인원" min="1" max="300" required></label><br><br>
    <button type="submit">추가</button>
</form>

<br>
<a href="/sugang/admin">-> 관리자 메인 페이지로</a>

<?php
include $_SERVER['DOCUMENT_ROOT'].'/sugang/include/footer.php';
$con->close();
?>
