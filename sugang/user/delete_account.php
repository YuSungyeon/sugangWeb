<?php
session_start();

// 데이터베이스 연결 설정 & 로그인 상태 확인
require_once $_SERVER['DOCUMENT_ROOT'].'/sugang/include/db.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/sugang/include/check_login.php';

$userID = $_SESSION['userID'];

// 폼이 제출된 경우
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $inputPassword = trim($_POST['password']);

    // 입력 공백 확인
    if ($inputPassword === "") {
        echo "<script>alert('비밀번호를 입력해주세요.'); history.back();</script>";
        exit;
    }

    // DB에서 현재 비밀번호 가져오기
    $sql = "SELECT 비밀번호 FROM 사용자 WHERE 학번 = ?";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("s", $userID);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $hashedPassword = $row['비밀번호'];

        if (password_verify($inputPassword, $hashedPassword)) {

            // 외래키 제약 조건을 위반하지 않도록 관련 테이블 데이터 먼저 삭제하는 것은 패스 (ON DELETE CASCADE 처리 해둠)

            // 사용자 계정 삭제
            $deleteUser = $con->prepare("DELETE FROM 사용자 WHERE 학번 = ?");
            $deleteUser->bind_param("s", $userID);

            if ($deleteUser->execute()) {
                session_destroy(); // 세션 파기
                echo "<script>alert('회원탈퇴가 완료되었습니다.'); location.href='/sugang/user/login.php';</script>";
                exit;
            } else {
                echo "<script>alert('회원 정보 삭제 중 오류 발생'); history.back();</script>";
            }

            $deleteUser->close();

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
