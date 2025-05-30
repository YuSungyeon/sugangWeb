<?php

// 데이터베이스 연결 설정을 불러옴
require_once $_SERVER['DOCUMENT_ROOT'].'/sugang/include/db.php';

// 폼이 제출되었을 때만 실행 (POST 방식)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // POST로 전달된 입력값을 받아서 앞뒤 공백 제거
    $student_id = trim($_POST['student_id']);
    $password = trim($_POST['password']);
    $name = trim($_POST['name']);

    /* ② 기본 빈값 검사 */
    if ($student_id === '' || $password === '' || $name === '') {
        exit("모든 항목을 입력해주세요.<br><a href='/sugang/user/signup.php'>돌아가기</a>");
    }

    /* ③ 학번 형식 검증 */
    if (!preg_match('/^\d{8}$/', $student_id)) {               // 8자리 숫자
        exit("학번은 숫자 8자리여야 합니다.<br><a href='/sugang/user/signup.php'>돌아가기</a>");
    }

    $yearPart     = intval(substr($student_id, 0, 4));         // 앞 4자리
    $currentYear  = intval(date('Y'));
    if ($yearPart < 1900 || $yearPart > $currentYear) {        // 1900~현재년도
        exit("학번의 앞 4자리는 1900~{$currentYear} 사이여야 합니다.<br><a href='/sugang/user/signup.php'>돌아가기</a>");
    }
    
    // 동일한 학번이 이미 등록되어 있는지 확인
    $check_sql = "SELECT * FROM 사용자 WHERE 학번 = '".$student_id."'";
    $check_result = mysqli_query($con, $check_sql);
    if ($check_result && mysqli_num_rows($check_result) > 0) {
        exit("이미 등록된 학번입니다.<br><a href='/sugang/user/signup.php'>돌아가기</a>");
    }

    // 비밀번호 보안을 위해 해싱 (password_hash는 PHP에서 추천하는 안전한 방식)
    $password_hashed = password_hash($password, PASSWORD_DEFAULT);

    // 사용자 정보 INSERT 쿼리 생성 및 실행
    $sql = "INSERT INTO 사용자 (학번, 비밀번호, 이름) 
            VALUES ('".$student_id."', '".$password_hashed."', '".$name."')";
    $result = mysqli_query($con, $sql);

    // 결과 확인
    if ($result) {
        echo "회원가입 성공!<br>";
        echo "<a href='/sugang/user/login.php'>로그인하기</a>";
    } else {
        echo "회원가입 실패!<br>";
        echo "실패 원인: ".mysqli_error($con)."<br>";
        echo "<a href='/sugang/user/signup.php'>돌아가기</a>";
    }

    // DB 연결 종료
    mysqli_close($con);
    exit();
}
?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <title>회원가입</title>
</head>
<body>
    <h1>회원가입</h1>
    
    <!-- 회원가입 폼 -->
    <form method="post" action="/sugang/user/signup.php">
        학번: <input type="text" name="student_id" maxlength="8" required><br>
        비밀번호: <input type="password" name="password" maxlength="70" required><br>
        이름: <input type="text" name="name" maxlength="30" required><br>
        <input type="submit" value="회원가입">
    </form>
    
    <br>
    <a href="/sugang/user/login.php">로그인으로 돌아가기</a>
</body>
</html>
