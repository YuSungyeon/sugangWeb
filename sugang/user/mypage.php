<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'].'/sugang/include/db.php';

// 로그인 되어 있는지 확인
if (!isset($_SESSION['userID'])) {
    echo "<script>alert('로그인이 필요합니다.'); location.href='/sugang/user/login.php';</script>";
    exit;
}

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
    $regDate = $row['가입일']; // 가입일이라는 컬럼이 있다고 가정
} else {
    echo "사용자 정보를 불러올 수 없습니다.";
    exit;
}

$stmt->close();
$con->close();
?>

<?php require_once $_SERVER['DOCUMENT_ROOT'].'/sugang/include/header.php'; ?>

<h1>마이페이지</h1>

<p><strong>학번:</strong> <?php echo htmlspecialchars($userID); ?></p>
<p><strong>이름:</strong> <?php echo htmlspecialchars($name); ?></p>

<br>
<a href="/sugang/user/edit_profile.php">회원정보 수정</a> |
<a href="/sugang/user/logout.php">로그아웃</a> |
<a href="/sugang/user/delete_account.php" onclick="return confirm('정말 탈퇴하시겠습니까?');" style="color:red;">회원 탈퇴</a> |
<a href="/sugang/index.php">처음으로</a>

<?php require_once $_SERVER['DOCUMENT_ROOT'].'/sugang/include/footer.php'; ?>
