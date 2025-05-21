<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'].'/sugang/include/db.php';

// 로그인 확인
if (!isset($_SESSION['userID'])) {
    echo "<script>alert('로그인이 필요합니다.'); location.href='/sugang/user/login.php';</script>";
    exit;
}

// 사용자 학번
$userID = $_SESSION['userID'];

// 폼이 제출된 경우
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $new_name = trim($_POST['name']);
    $new_password = trim($_POST['password']);

    // 입력값 유효성 검사
    if ($new_name == "") {
        echo "이름을 입력해주세요.<br><a href='/sugang/user/edit_profile.php'>돌아가기</a>";
        exit();
    }

    // 비밀번호가 입력되었을 경우만 변경
    if ($new_password != "") {
        $password_hashed = password_hash($new_password, PASSWORD_DEFAULT);
        $sql = "UPDATE 사용자 SET 이름 = ?, 비밀번호 = ? WHERE 학번 = ?";
        $stmt = $con->prepare($sql);
        $stmt->bind_param("sss", $new_name, $password_hashed, $userID);
    } else {
        $sql = "UPDATE 사용자 SET 이름 = ? WHERE 학번 = ?";
        $stmt = $con->prepare($sql);
        $stmt->bind_param("ss", $new_name, $userID);
    }

    // 쿼리 실행
    if ($stmt->execute()) {
        echo "<script>alert('정보가 수정되었습니다.'); location.href='/sugang/user/mypage.php';</script>";
    } else {
        echo "수정 실패: " . $stmt->error;
    }

    $stmt->close();
    $con->close();
    exit();
}

// 사용자 정보 불러오기 (수정 전 이름 표시용)
$sql = "SELECT 이름 FROM 사용자 WHERE 학번 = ?";
$stmt = $con->prepare($sql);
$stmt->bind_param("s", $userID);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$curr_name = $row['이름'];

$stmt->close();
$con->close();
?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <title>회원정보 수정</title>
</head>
<body>
    <h1>회원정보 수정</h1>

    <form method="post" action="/sugang/user/edit_profile.php">
        <label>학번: </label><?php echo htmlspecialchars($userID); ?><br><br>

        <label>이름: </label>
        <input type="text" name="name" value="<?php echo htmlspecialchars($curr_name); ?>" required><br><br>

        <label>새 비밀번호: </label>
        <input type="password" name="password" placeholder="변경하지 않으려면 비워두세요"><br><br>

        <input type="submit" value="수정하기">
    </form>

    <br>
    <a href="/sugang/user/mypage.php">마이페이지로 돌아가기</a>
</body>
</html>
