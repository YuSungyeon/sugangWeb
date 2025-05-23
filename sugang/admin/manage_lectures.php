<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/sugang/admin/include/admin_check.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/sugang/include/db.php';
include $_SERVER['DOCUMENT_ROOT'].'/sugang/include/header.php';

$sql = "SELECT 강의코드, 강의명, 교수명, 최대인원 FROM 강의 ORDER BY 강의코드";
$result = $con->query($sql);
?>

<h2>강의 관리</h2>
<a href="add_lecture.php">강의 추가</a>

<table border="1" cellpadding="5" cellspacing="0">
    <tr>
        <th>강의코드</th>
        <th>강의명</th>
        <th>담당 교수</th>
        <th>학점</th>
        <th>수정</th>
        <th>삭제</th>
    </tr>
    <?php while ($row = $result->fetch_assoc()): ?>
    <tr>
        <td><?= htmlspecialchars($row['강의코드']) ?></td>
        <td><?= htmlspecialchars($row['강의명']) ?></td>
        <td><?= htmlspecialchars($row['교수명']) ?></td>
        <td><?= htmlspecialchars($row['최대인원']) ?></td>
        <td><a href="edit_lecture.php?code=<?= urlencode($row['강의코드']) ?>">수정</a></td>
        <td>
            <form action="delete_lecture.php" method="post" onsubmit="return confirm('정말 삭제하시겠습니까?');">
                <input type="hidden" name="강의코드" value="<?= htmlspecialchars($row['강의코드']) ?>">
                <button type="submit">삭제</button>
            </form>
        </td>
    </tr>
    <?php endwhile; ?>
</table>

<br>
<a href="/sugang/admin">-> 관리자 메인 페이지로</a>

<?php
include $_SERVER['DOCUMENT_ROOT'].'/sugang/include/footer.php';
$con->close();
?>
