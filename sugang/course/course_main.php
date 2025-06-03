<?php
session_start();

// 로그인 상태 확인 & 헤더
require_once $_SERVER['DOCUMENT_ROOT'].'/sugang/include/check_login.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/sugang/include/header.php'; 
?>

<h2>안녕하세요, <?php echo htmlspecialchars($_SESSION['name']); ?>님!</h2>
<p>원하는 작업을 선택하세요:</p>

<ul>
  <li><a href="/sugang/course/course_select.php">수강 신청</a></li>
  <li><a href="/sugang/course/mycourses.php">수강 조회</a></li>
  <li><a href="/sugang/stats/stats_total.php">수강신청 통계</a></li>
</ul>

<?php require_once $_SERVER['DOCUMENT_ROOT'].'/sugang/include/footer.php'; ?>