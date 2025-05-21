<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'].'/sugang/include/db.php';

if (!isset($_SESSION['userID'])) {
    echo "<script>alert('로그인이 필요합니다.'); location.href='/sugang/user/login.php';</script>";
    exit;
}

$userID = $_SESSION['userID'];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $inputPassword = trim($_POST['password']);

    if ($inputPassword === "") {
        echo "<script>alert('비밀번호를 입력해주세요.'); history.back();</script>";
        exit;
    }

    // 현재 비밀번호 가져오기
    $sql = "SELECT 비밀번호 FROM 사용자 WHERE 학번 = ?";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("s", $userID);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $hashedPassword = $row['비밀번호'];

        if (password_verify($inputPassword, $hashedPassword)) {
            // 탈퇴 처리 (완전 삭제로 구현)
            $delete_sql = "DELETE FROM 사용자 WHERE 학번 = ?";
            $del_stmt = $con->prepare($delete_sql);
            $del_stmt->bind_param("s", $userID);

            if ($del_stmt->execute()) {
                session_destroy();
                echo "<script>alert('회원탈퇴가 완료되었습니다.'); location.href='/sugang/user/login.php';</script>";
                exit;
            } else {
                echo "탈퇴 실패: " . $del_stmt->error;
            }

            $del_stmt->close();
        } else {
            echo "<script>alert('비밀번호가 일치하지 않습니다.'); history.back();</script>";
        }
    } else {
        echo "<script>alert('사용자 정보를 찾을 수 없습니다.'); location.href='/sugang/user/mypage.php';</script>";
    }

    $stmt->close();
    $con->close();
    exit;
}
?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <title>회원탈퇴</title>
</head>
<body>
    <?php require_once $_SERVER['DOCUMENT_ROOT'].'/sugang/include/header.php'; ?>

    <h1>회원탈퇴</h1>
    <p>회원 탈퇴를 위해 비밀번호를 입력해주세요.</p>

    <form method="post" action="/sugang/user/delete_account.php">
        <label for="password">비밀번호:</label>
        <input type="password" name="password" id="password" required>
        <br><br>
        <input type="submit" value="회원탈퇴">
    </form>

    <br>
    <a href="/sugang/user/mypage.php">마이페이지로 돌아가기</a>

    <?php require_once $_SERVER['DOCUMENT_ROOT'].'/sugang/include/footer.php'; ?>
</body>
</html>
