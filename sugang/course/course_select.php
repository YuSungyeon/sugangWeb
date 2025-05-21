<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'].'/sugang/include/db.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/sugang/include/header.php';

// 로그인 확인
if (!isset($_SESSION['userID'])) {
    echo "<script>alert('로그인이 필요합니다.'); location.href='/sugang/user/login.php';</script>";
    exit;
}

$userID = $_SESSION['userID'];

// 강의 목록 조회
$sql = "SELECT * FROM 강의";
$result = mysqli_query($con, $sql);
?>

<h2 class="mb-4">📚 수강 과목 선택 및 우선순위 설정</h2>

<form action="course_start.php" method="post">
    <table border="1" cellpadding="8" cellspacing="0">
        <thead>
            <tr>
                <th>선택</th>
                <th>강의코드</th>
                <th>강의명</th>
                <th>교수명</th>
                <th>최대인원</th>
                <th>우선순위</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = mysqli_fetch_assoc($result)) { ?>
                <tr>
                    <td>
                        <input type="checkbox" name="selected_courses[]" value="<?= htmlspecialchars($row['강의코드']) ?>">
                    </td>
                    <td><?= htmlspecialchars($row['강의코드']) ?></td>
                    <td><?= htmlspecialchars($row['강의명']) ?></td>
                    <td><?= htmlspecialchars($row['교수명']) ?></td>
                    <td><?= htmlspecialchars($row['최대인원']) ?></td>
                    <td>
                        <select name="priority[<?= htmlspecialchars($row['강의코드']) ?>]">
                            <option value="">선택</option>
                            <?php for ($i = 1; $i <= 5; $i++) { ?>
                                <option value="<?= $i ?>"><?= $i ?>순위</option>
                            <?php } ?>
                        </select>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>

    <br>
    <button type="submit">✅ 수강신청 시작</button>
</form>

<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/sugang/include/footer.php';
?>
