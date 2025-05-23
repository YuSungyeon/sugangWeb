<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/sugang/admin/include/admin_check.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/sugang/include/db.php';
include $_SERVER['DOCUMENT_ROOT'].'/sugang/include/header.php';

$sql = "SELECT 수강신청.학번, 사용자.이름, 수강신청.강의코드, 강의.강의명, 강의.교수명, 수강신청.시간차이, 수강신청.중요도
        FROM 수강신청
        JOIN 사용자 ON 수강신청.학번 = 사용자.학번
        JOIN 강의 ON 수강신청.강의코드 = 강의.강의코드
        ORDER BY 수강신청.강의코드 ASC, 수강신청.중요도 DESC";
$result = $con->query($sql);
?>

<h2>수강신청 관리</h2>

<table border="1" cellpadding="5">
    <tr>
        <th>학번</th>
        <th>이름</th>
        <th>강의코드</th>
        <th>강의명</th>
        <th>교수명</th>
        <th>시간차이</th>
        <th>중요도</th>
        <th>관리</th>
    </tr>
    <?php while ($row = $result->fetch_assoc()): ?>
    <tr>
        <td><?= htmlspecialchars($row['학번']) ?></td>
        <td><?= htmlspecialchars($row['이름']) ?></td>
        <td><?= htmlspecialchars($row['강의코드']) ?></td>
        <td><?= htmlspecialchars($row['강의명']) ?></td>
        <td><?= htmlspecialchars($row['교수명']) ?></td>
        <td><?= htmlspecialchars($row['시간차이']) ?></td>
        <td><?= htmlspecialchars($row['중요도']) ?></td>
        <td>
            <form action="courses_delete.php" method="post" style="display:inline;" onsubmit="return confirm('정말 삭제하시겠습니까?');">
                <input type="hidden" name="학번" value="<?= $row['학번'] ?>">
                <input type="hidden" name="강의코드" value="<?= $row['강의코드'] ?>">
                <button type="submit">🗑 삭제</button>
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
