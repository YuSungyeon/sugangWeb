<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'].'/sugang/include/db.php';

// 로그인 확인
if (!isset($_SESSION['userID'])) {
    echo "<script>alert('로그인이 필요합니다.'); location.href='/sugang/user/login.php';</script>";
    exit;
}

$userID = $_SESSION['userID'];
$userName = $_SESSION['name'];

// 폼 제출 처리
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);

    if ($title == "" || $content == "") {
        echo "<script>alert('제목과 내용을 모두 입력해주세요.'); history.back();</script>";
        exit;
    }

    $sql = "INSERT INTO 게시글 (제목, 내용, 작성자, 작성시간) VALUES (?, ?, ?, NOW())";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("sss", $title, $content, $userID);
    
    if ($stmt->execute()) {
        echo "<script>alert('글이 작성되었습니다.'); location.href='/sugang/board/board_list.php';</script>";
    } else {
        echo "글 작성 실패: " . $stmt->error;
    }

    $stmt->close();
    $con->close();
    exit;
}

include $_SERVER['DOCUMENT_ROOT'].'/sugang/include/header.php';
?>

<h2>글쓰기</h2>

<form method="post" action="/sugang/board/board_write.php">
    <label>제목: </label><br>
    <input type="text" name="title" required><br><br>

    <label>내용: </label><br>
    <textarea name="content" rows="20" cols="60" required></textarea><br><br>

    <input type="submit" value="작성">
</form>

<p><a href="/sugang/board/board_list.php">← 게시판 목록</a></p>

<?php include $_SERVER['DOCUMENT_ROOT'].'/sugang/include/footer.php'; ?>
