<?php
// 로그인 상태 유지를 위한 세션 사용
session_start();

// 데이터베이스 연결 설정
require_once $_SERVER['DOCUMENT_ROOT'].'/sugang/include/db.php';

// 폼이 제출 확인 (POST 방식)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // POST로 전달된 입력값 앞뒤 공백 제거
    $student_id = trim($_POST['student_id']);
    $password = trim($_POST['password']);

    // 학번이나 비밀번호가 비어있을 경우 오류 처리
    if ($student_id == "" || $password == "") {
        echo "학번과 비밀번호를 모두 입력해주세요.<br>";
        echo "<a href='/sugang/user/login.php'>돌아가기</a>";
        exit();
    }

    // 사용자 테이블에서 입력한 학번과 일치하는 사용자 조회
    $sql = "SELECT * FROM 사용자 WHERE 학번 = '".$student_id."'";
    $result = mysqli_query($con, $sql);

    // 쿼리 성공 확인 & 사용자 존재 확인
    if ($result && mysqli_num_rows($result) == 1) {
        // 사용자 정보 가져오기
        $row = mysqli_fetch_assoc($result);
        $hashed_password = $row['비밀번호']; // DB에 저장된 암호화된 비밀번호

        // 입력한 비밀번호와 DB의 해시된 비밀번호 비교
        if (password_verify($password, $hashed_password)) {
            // 로그인 성공한 경우 세션에 사용자 정보 저장
            $_SESSION['userID'] = $row['학번'];      // 학번
            $_SESSION['name'] = $row['이름'];        // 이름
            $_SESSION['is_admin'] = (isset($row['관리자여부']) && $row['관리자여부'] == 1);


            // 보안을 위한 세션 ID 재생성
            session_regenerate_id(true);

            // 로그인 후 메인 페이지로 이동
            header("Location: /sugang");
            
            exit();
        } else {
            // 비밀번호 불일치 (로그인 실패)
            echo "비밀번호가 일치하지 않습니다.<br>";
            echo "<a href='/sugang/user/login.php'>다시 시도</a>";
            exit();
        }
    } else {
        // 해당 학번의 사용자가 없음 (로그인 실패)
        echo "존재하지 않는 학번입니다.<br>";
        echo "<a href='/sugang/user/login.php'>다시 시도</a>";
        exit();
    }

    // DB 연결 종료
    mysqli_close($con);
}
?>

<!-- 로그인 폼 출력 -->
<?php require_once $_SERVER['DOCUMENT_ROOT'].'/sugang/include/header.php'; ?>

    <h1>로그인</h1>

    <!-- 로그인 입력 폼 (POST 방식 전송) -->
    <form method="post" action="/sugang/user/login.php">
        <label>학번: <input type="text" name="student_id" maxlength="8" required></label><br>
        <label>비밀번호: <input type="password" name="password" required></label><br>
        <input type="submit" value="로그인">
    </form>

    <br>
    <a href="/sugang/user/signup.php">아직 회원이 아니신가요? 회원가입</a>
<?php require_once $_SERVER['DOCUMENT_ROOT'].'/sugang/include/footer.php'; ?>

