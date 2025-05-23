<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/sugang/admin/include/admin_check.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/sugang/include/db.php';
include $_SERVER['DOCUMENT_ROOT'].'/sugang/include/header.php';

$sql = "SELECT 게시글.게시글ID, 게시글.제목, 게시글.작성시간, 사용자.이름 AS 작성자
        FROM 게시글
        JOIN 사용자 ON 게시글.작성자 = 사용자.학번
        ORDER BY 게시글.게시글ID DESC";
$result = $con->query($sql);
?>

<h2>게시판 관리</h2>

<table border="1" cellpadding="5">
    <tr>
        <th>번호</th>
        <th>제목</th>
        <th>작성자</th>
        <th>작성일</th>
        <th>관리</th>
    </tr>
    <?php while ($row = $result->fetch_assoc()): ?>
    <tr>
        <td><?= htmlspecialchars($row['게시글ID']) ?></td>
        <td><a href="/sugang/board/board_view.php?id=<?= $row['게시글ID'] ?>"><?= htmlspecialchars($row['제목']) ?></a></td>
        <td><?= htmlspecialchars($row['작성자']) ?></td>
        <td><?= htmlspecialchars($row['작성시간']) ?></td>
        <td>
            <form action="board_delete.php" method="post" onsubmit="return confirm('정말 삭제하시겠습니까?');" style="display:inline;">
                <input type="hidden" name="id" value="<?= $row['게시글ID'] ?>">
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
