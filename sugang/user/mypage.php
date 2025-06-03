<?php
session_start();

// 데이터베이스 연결 설정 & 로그인 상태 확인
require_once $_SERVER['DOCUMENT_ROOT'].'/sugang/include/db.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/sugang/include/check_login.php';

// 로그인된 학번 정보 가져오기
$userID = $_SESSION['userID'];

// 사용자 정보 조회 쿼리
$sql = "SELECT * FROM 사용자 WHERE 학번 = ?";
$stmt = $con->prepare($sql);
$stmt->bind_param("s", $userID);
$stmt->execute();
$result = $stmt->get_result();

// 결과에서 사용자 정보 추출
if ($row = $result->fetch_assoc()) {
    $name = $row['이름'];
} else {
    echo "사용자 정보를 불러올 수 없습니다.";
    exit;
}

$stmt->close();
$con->close();
?>

<!-- 공통 헤더 -->
<?php require_once $_SERVER['DOCUMENT_ROOT'].'/sugang/include/header.php'; ?>

<h1>마이페이지</h1>

<p><strong>학번:</strong> <?= htmlspecialchars($userID) ?></p>
<p><strong>이름:</strong> <?= htmlspecialchars($name) ?></p>

<br>
<ul>
  <li><a href="/sugang/course/mycourses.php">나의 수강내역</a></li>
  <li><a href="/sugang/stats/stats_solo.php">수강신청 보고서</a></li>
  <li><a href="/sugang/board/my_board.php">내가 쓴 글</a></li>
</ul>

<br>
<a href="/sugang/user/edit_profile.php">회원정보 수정</a> |
<a href="/sugang/user/logout.php">로그아웃</a> |
<a href="/sugang/user/delete_account.php" onclick="return confirm('정말 탈퇴하시겠습니까?');" style="color:red;">회원 탈퇴</a> |
<a href="/sugang/index.php">처음으로</a>

<!-- 공통 푸터 -->
<?php require_once $_SERVER['DOCUMENT_ROOT'].'/sugang/include/footer.php'; ?>
