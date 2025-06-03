<?php
session_start();

// 세션에 저장된 모든 데이터 제거
$_SESSION = array();  // $_SESSION 변수 초기화

// 세션 쿠키가 있다면 삭제
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(), // 세션 쿠키 이름
        '',             // 빈 값으로 설정
        time() - 42000, // 만료시간을 과거로 설정하여 제거
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// 세션 파괴 (파일 제거)
session_destroy();

// 로그아웃 후 초기 화면으로 이동
header("Location: /sugang");
exit;
?>
