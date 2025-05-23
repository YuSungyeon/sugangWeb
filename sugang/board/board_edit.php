<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'].'/sugang/include/db.php';

if (!isset($_GET['id'])) {
    echo "<script>alert('잘못된 접근입니다.'); location.href='/sugang/board/board_list.php';</script>";
    exit;
}

$id = intval($_GET['id']);
$userID = $_SESSION['userID'];

$sql = "SELECT * FROM 게시글 WHERE 게시글ID = ?";
$stmt = $con->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo "<script>alert('글이 존재하지 않습니다.'); location.href='/sugang/board/board_list.php';</script>";
    exit;
}

$row = $result->fetch_assoc();

// 작성자 확인
if ($row['작성자'] != $userID) {
    echo "<script>alert('본인 글만 수정할 수 있습니다.'); location.href='/sugang/board/board_list.php';</script>";
    exit;
}
?>

<h2>게시글 수정</h2>
<form method="post" action="board_edit_process.php">
    <input type="hidden" name="id" value="<?= $id ?>">
    제목: <input type="text" name="title" value="<?= htmlspecialchars($row['제목']) ?>" required><br><br>
    내용:<br>
    <textarea name="content" rows="10" cols="50" required><?= htmlspecialchars($row['내용']) ?></textarea><br><br>
    <input type="submit" value="수정">
</form>
<p><a href="board_view.php?id=<?= $id ?>">← 돌아가기</a></p>
