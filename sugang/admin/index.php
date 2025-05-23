<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/sugang/admin/include/admin_check.php';
include $_SERVER['DOCUMENT_ROOT'].'/sugang/include/header.php';
?>

<h2>관리자 대시보드</h2>
<ul>
    <li><a href="manage_users.php">👤 사용자 관리</a></li>
    <li><a href="manage_board.php">📝 게시판 관리</a></li>
    <li><a href="manage_lectures.php">📚 강의 관리</a></li>
    <li><a href="manage_courses.php">📚 수강신청 관리</a></li>
</ul>

<?php include $_SERVER['DOCUMENT_ROOT'].'/sugang/include/footer.php'; ?>
