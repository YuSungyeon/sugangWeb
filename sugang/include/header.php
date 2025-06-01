<?php
// 세션 시작 (모든 페이지에서 로그인 정보를 사용하기 위해 필요)
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <title>수강신청 시스템</title>
    
    <link rel="stylesheet" href="/sugang/assets/css/style.css">
</head>
<body>
    <header>
        <h1><a href="/sugang">수강신청 시스템</a></h1>
        
        <!-- 상단 네비게이션 -->
        <nav>
            <ul>
                <?php if (isset($_SESSION['userID'])): ?>
                    <li><a href="/sugang/user/mypage.php">마이페이지</a></li>
                    <li><a href="/sugang/course/course_main.php">수강신청</a></li>
                    <li><a href="/sugang/board/board_list.php">게시판</a></li>
                    
                    <?php if (!empty($_SESSION['is_Admin']) && $_SESSION['is_Admin']): ?>
                        <li><a href="/sugang/admin/dashboard.php">관리자 페이지</a></li>
                    <?php endif; ?>

                    <li><a href="/sugang/user/logout.php">로그아웃</a></li>
                    <li><span style="font-weight: bold;"><?php echo htmlspecialchars($_SESSION['name']); ?>님</span></li>
                <?php else: ?>
                    <li><a href="/sugang/user/login.php">로그인</a></li>
                    <li><a href="/sugang/user/signup.php">회원가입</a></li>
                <?php endif; ?>
            </ul>
        </nav>
        <pre><?php print_r($_SESSION); ?></pre>
        <hr>
    </header>
