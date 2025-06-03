<?php
session_start();

// 데이터베이스 연결 설정 & 헤더
require_once $_SERVER['DOCUMENT_ROOT'].'/sugang/include/db.php';
include $_SERVER['DOCUMENT_ROOT'].'/sugang/include/header.php';

// ─────────────────────────────────────────────
// 게시글 목록 조회 (작성자 이름 및 댓글 수 포함)
// 게시글 테이블 + 사용자 테이블 조인 + 서브쿼리로 댓글 수 계산
// 상태가 true인 게시글만 출력, 최신순 정렬
// ─────────────────────────────────────────────
$sql = "
SELECT 
    게시글.게시글ID, 
    게시글.제목, 
    사용자.이름 AS 작성자, 
    게시글.작성시간,
    (SELECT COUNT(*) FROM 댓글 WHERE 댓글.게시글ID = 게시글.게시글ID) AS 댓글수
FROM 게시글
JOIN 사용자 ON 게시글.작성자 = 사용자.학번
WHERE 게시글.상태 = TRUE
ORDER BY 게시글.게시글ID DESC
";

$result = $con->query($sql);
?>

<h2>게시판</h2>

<table border="1">
    <tr>
        <th>번호</th>
        <th>제목</th>
        <th>댓글</th>
        <th>작성자</th>
        <th>작성일</th>
    </tr>
    <!-- 게시글이 있을 경우 -->
    <?php while ($row = $result->fetch_assoc()): ?>
    <tr>
        <td><?= htmlspecialchars($row['게시글ID']) ?></td>
        <td>
            <a href="/sugang/board/board_view.php?id=<?= $row['게시글ID'] ?>">
                <?= htmlspecialchars($row['제목']) ?>
            </a>
        </td>
        <td><?= (int)$row['댓글수'] ?></td>
        <td><?= htmlspecialchars($row['작성자']) ?></td>
        <td><?= htmlspecialchars($row['작성시간']) ?></td>
    </tr>
    <?php endwhile; ?>
    <!-- 게시글이 없는 경우 -->
    <?php if ($result->num_rows === 0): ?>
    <tr>
        <td colspan="5">게시글이 없습니다.</td>
    </tr>
    <?php endif; ?>
</table>

<!-- 로그인한 사용자만 글쓰기 가능 -->
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