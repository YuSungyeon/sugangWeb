<?php
session_start();
if (!isset($_SESSION['userID'])) {
    header("Location: /sugang/user/login.php");
    exit();
}
?>

<?php require_once $_SERVER['DOCUMENT_ROOT'].'/sugang/include/header.php'; ?>

<h2>안녕하세요, <?php echo htmlspecialchars($_SESSION['name']); ?>님!</h2>
<p>원하는 작업을 선택하세요:</p>

<div class="menu">
    <a href="/sugang/course/course_select.php">수강 신청</a>
    <a href="/sugang/course/course_view.php">수강 조회</a>
</div>

<?php require_once $_SERVER['DOCUMENT_ROOT'].'/sugang/include/footer.php'; ?>