<?php
session_start();

// 데이터베이스 연결 설정 & 로그인 상태 확인 & 헤더
require_once $_SERVER['DOCUMENT_ROOT'].'/sugang/include/db.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/sugang/include/check_login.php';
include $_SERVER['DOCUMENT_ROOT'].'/sugang/include/header.php';

$userID = $_SESSION['userID'];
$userName = $_SESSION['name'];

// 폼이 제출된 경우(POST 요청 처리)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = trim($_POST['title']);     // 제목 공백 제거 후 저장
    $content = trim($_POST['content']); // 내용 공백 제거 후 저장

    // 제목이나 내용이 비어있을 경우 경고 후 이전 페이지로
    if ($title == "" || $content == "") {
        echo "<script>alert('제목과 내용을 모두 입력해주세요.'); history.back();</script>";
        exit;
    }

    // 게시글 저장 쿼리 실행
    $sql = "INSERT INTO 게시글 (제목, 내용, 작성자) VALUES (?, ?, ?)";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("sss", $title, $content, $userID);

    // 실행 성공 여부에 따라 알림 처리
    if ($stmt->execute()) {
        echo "<script>alert('글이 작성되었습니다.'); location.href='/sugang/board/board_list.php';</script>";
    } else {
        echo "<script>alert('글 작성에 실패했습니다.'); history.back();</script>";
    }

    // 자원 해제
    $stmt->close();
    $con->close();
    exit;
}
?>

<!-- 글쓰기 화면 출력 -->
<h2>글쓰기</h2>

<form method="post" action="/sugang/board/board_write.php">
    <label>제목: </label><br>
    <input type="text" name="title" required><br><br>

    <label>내용: </label><br>
    <textarea name="content" rows="20" cols="60" required></textarea><br><br>

    <input type="submit" value="작성">
</form>

<!-- 게시판 목록으로 돌아가기 링크 -->
<p><a href="/sugang/board/board_list.php">← 게시판 목록</a></p>

<?php include $_SERVER['DOCUMENT_ROOT'].'/sugang/include/footer.php'; ?>