<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once $_SERVER['DOCUMENT_ROOT'].'/sugang/include/db.php';

if (!isset($_SESSION['userID'])) {
    echo "<script>alert('로그인이 필요합니다.'); location.href='/sugang/login.php';</script>";
    exit;
}

$userID = $_SESSION['userID'];

$sql = "SELECT 강의.강의코드, 강의명, 교수명, 시간차이
        FROM 수강신청 
        JOIN 강의 ON 수강신청.강의코드 = 강의.강의코드 
        WHERE 수강신청.학번 = ?";
$stmt = $con->prepare($sql);
$stmt->bind_param("s", $userID);
$stmt->execute();
$result = $stmt->get_result();

include $_SERVER['DOCUMENT_ROOT'].'/sugang/include/header.php';
?>

<h2>나의 수강 과목</h2>

<table border="1">
    <tr>
        <th>강의코드</th>
        <th>강의명</th>
        <th>교수명</th>
        <th>시간차이(초)</th>
    </tr>

    <?php while ($row = $result->fetch_assoc()): ?>
    <tr>
        <td><?= htmlspecialchars($row['강의코드']) ?></td>
        <td><?= htmlspecialchars($row['강의명']) ?></td>
        <td><?= htmlspecialchars($row['교수명']) ?></td>
        <td>
        <?= ROUND(abs($row['시간차이'])/1000,5) ?>
        <?php if ($row['시간차이'] < 0): ?>
            <span style="color: red;">(전)</span>
        <?php else: ?>
            <span style="color: blue;">(후)</span>
        <?php endif; ?>
        </td>
    </tr>
    <?php endwhile; ?>

    <?php if ($result->num_rows === 0): ?>
    <tr>
        <td colspan="4">수강한 과목이 없습니다.</td>
    </tr>
    <?php endif; ?>
</table>

<p><a href="/sugang">← 메인으로 돌아가기</a></p>

<?php
include $_SERVER['DOCUMENT_ROOT'].'/sugang/include/footer.php';
$stmt->close();
?>
