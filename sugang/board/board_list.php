<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'].'/sugang/include/db.php';

$sql = "SELECT 게시글.게시글ID, 게시글.제목, 사용자.이름 AS 작성자, 게시글.작성시간
FROM 게시글
JOIN 사용자 ON 게시글.작성자 = 사용자.학번
ORDER BY 게시글.게시글ID DESC";

$result = $con->query($sql);

include $_SERVER['DOCUMENT_ROOT'].'/sugang/include/header.php';
?>

<h2>게시판</h2>

<table border="1">
    <tr>
        <th>번호</th>
        <th>제목</th>
        <th>작성자</th>
        <th>작성일</th>
    </tr>
    <?php while ($row = $result->fetch_assoc()): ?>
    <tr>
        <td><?= htmlspecialchars($row['게시글ID']) ?></td>
        <td><a href="/sugang/board/board_view.php?id=<?= $row['게시글ID'] ?>"><?= htmlspecialchars($row['제목']) ?></a></td>
        <td><?= htmlspecialchars($row['작성자']) ?></td>
        <td><?= htmlspecialchars($row['작성시간']) ?></td>
    </tr>
    <?php endwhile; ?>

    <?php if ($result->num_rows === 0): ?>
    <tr>
        <td colspan="4">게시글이 없습니다.</td>
    </tr>
    <?php endif; ?>
</table>

<?php if (isset($_SESSION['userID'])): ?>
    <p><a href="/sugang/board/board_write.php">글쓰기</a></p>
<?php else: ?>
    <p><a href="/sugang/user/login.php">로그인 후 글쓰기</a></p>
<?php endif; ?>

<p><a href="/sugang">← 메인으로 돌아가기</a></p>

<?php
include $_SERVER['DOCUMENT_ROOT'].'/sugang/include/footer.php';
$con->close();
?>
