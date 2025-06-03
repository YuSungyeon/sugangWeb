<?php
/* ── 0. 디버그 ── */
ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(E_ALL);
mysqli_report(MYSQLI_REPORT_ERROR|MYSQLI_REPORT_STRICT);

session_start();
require_once $_SERVER['DOCUMENT_ROOT'].'/sugang/include/db.php';

if (!isset($_GET['id'])) {
    echo "<script>alert('잘못된 접근입니다.'); location.href='/sugang/board/board_list.php';</script>";
    exit;
}

$id = intval($_GET['id']);
$sql = "SELECT 게시글.제목, 게시글.내용, 게시글.작성시간, 게시글.상태,
            게시글.수정시간, 사용자.학번, 사용자.이름 AS 작성자
        FROM 게시글
        JOIN 사용자 ON 게시글.작성자 = 사용자.학번
        WHERE 게시글.게시글ID = ?";
$stmt = $con->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo "<script>alert('존재하지 않는 글입니다.'); location.href='/sugang/board/board_list.php';</script>";
    exit;
}

$row = $result->fetch_assoc();
if (!$row['상태']) {
    echo "<script>alert('관리자에 의해 삭제된 게시글입니다.'); history.back();</script>";
    exit;
}

include $_SERVER['DOCUMENT_ROOT'].'/sugang/include/header.php';
?>

<h2><?= htmlspecialchars($row['제목']) ?></h2>
<p>작성자: <?= htmlspecialchars($row['작성자']) ?></p>
<p>작성일: <?= htmlspecialchars($row['작성시간']) ?></p>
<?php if ($row['수정시간']): ?>
    <p>수정일: <?= htmlspecialchars($row['수정시간']) ?></p>
<?php endif; ?>
<hr>
<p><?= nl2br(htmlspecialchars($row['내용'])) ?></p>
<hr>

<p>
<?php if ($row['학번'] == $_SESSION['userID']): ?>
    <a href="board_edit.php?id=<?= $id ?>">✏️ 수정</a> |
    <a href="board_delete.php?id=<?= $id ?>" onclick="return confirm('정말 삭제하시겠습니까?');">🗑 삭제</a> 
<?php endif; ?>
<?php if ($_SESSION['is_admin']): ?>
    <form action="/sugang/admin/board_toggle.php" method="post" style="display:inline;">
        <input type="hidden" name="id" value="<?= $id ?>">
        <button type="submit">
            <?= $row['상태'] ? '삭제' : '복구' ?>
        </button>
    </form>
<?php endif; ?>
</p>

<?php
// 댓글 조회
$sql = "SELECT 댓글.댓글ID, 댓글.내용, 댓글.작성시간, 댓글.수정시간, 댓글.상태, 사용자.학번, 사용자.이름 AS 작성자
        FROM 댓글
        JOIN 사용자 ON 댓글.작성자 = 사용자.학번
        WHERE 댓글.게시글ID = ?
        ORDER BY 댓글.작성시간 ASC";
$stmt = $con->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$comments = $stmt->get_result();
?>

<h3>💬 댓글</h3>
<?php if (isset($_SESSION['userID'])): ?>
    <h4>작성</h4>
    <form action="/sugang/comment/add_comment.php" method="post">
        <input type="hidden" name="게시글ID" value="<?= $id ?>">
        <textarea name="내용" rows="3" style="width:100%;" required></textarea><br>
        <button type="submit">댓글 등록</button>
    </form>
<?php else: ?>
    <p>댓글을 작성하려면 <a href="/sugang/login.php">로그인</a>이 필요합니다.</p>
<?php endif; ?>

<?php if ($comments->num_rows == 0): ?>
    <p>댓글이 없습니다.</p>
<?php else: ?>
    <ul>
        <?php while ($comment = $comments->fetch_assoc()): ?>
            <li>
                <strong><?= htmlspecialchars($comment['작성자']) ?></strong>
                (<?= htmlspecialchars($comment['작성시간']) ?>)<br>
                <?php if (!empty($comment['수정시간'])): ?>
                    <small style="color:gray;">(수정됨: <?= htmlspecialchars($comment['수정시간']) ?>)</small><br>
                <?php endif; ?>
                <?php if ((int)$comment['상태'] === 0): ?>
                    <em style="color:gray;">관리자에 의해 삭제된 댓글입니다.</em>
                <?php else: ?>
                    <?= nl2br(htmlspecialchars($comment['내용'])) ?>&nbsp
                <?php endif; ?>

                <?php if ($comment['학번'] == $_SESSION['userID'] && (int)$comment['상태'] !== 0): ?>
                    <a href="/sugang/comment/edit_comment.php?id=<?= $comment['댓글ID'] ?>&post=<?= $id ?>">✏️ 수정</a> | 
                    <a href="/sugang/comment/delete_comment.php?id=<?= $comment['댓글ID'] ?>&post=<?= $id ?>" onclick="return confirm('정말 삭제하시겠습니까?');">🗑 삭제</a>
                <?php endif; ?>

                <?php if ($_SESSION['is_admin']): ?>
                    <form action="/sugang/admin/comment_toggle.php" method="post" style="display:inline;">
                        <input type="hidden" name="id" value="<?= $comment['댓글ID'] ?>">
                        <input type="hidden" name="post" value="<?= $id ?>">  <!-- 게시글 ID -->
                        <button type="submit">
                            <?= ((int)$comment['상태']) ? '삭제' : '복구' ?>
                        </button>
                    </form>
                <?php endif; ?>
            </li>
            <hr>
        <?php endwhile; ?>
    </ul>
<?php endif; ?>


<p><a href="/sugang/board/board_list.php">← 게시판 목록</a></p>

<?php
include $_SERVER['DOCUMENT_ROOT'].'/sugang/include/footer.php';
$stmt->close();
$con->close();
?>
