<?php
session_start();

// 공통 헤더
require_once $_SERVER['DOCUMENT_ROOT'].'/sugang/include/header.php';
?>

<main style="padding: 20px;">
    <h2>환영합니다!</h2>

    <!-- 로그인 확인 -->
    <?php if (isset($_SESSION['userID'])): ?>
        <!-- 로그인 상태일 때 -->
        <p><strong><?= htmlspecialchars($_SESSION['name']) ?></strong>님, 안녕하세요</p>
        <p><a href="/sugang/user/mypage.php">마이페이지</a> | <a href="/sugang/user/logout.php">로그아웃</a></p>

        <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin']): ?>
            <!-- 관리자일 때 관리자 페이지 링크 추가 제공 -->
            <p><a href="/sugang/admin">[관리자 페이지 바로가기]</a></p>
        <?php endif; ?>

    <!-- 비로그인 상태일 때 -->
    <?php else: ?>
        <p><a href="/sugang/user/login.php">로그인</a> | <a href="/sugang/user/signup.php">회원가입</a></p>
    <?php endif; ?>

    <hr>
    <h3>바로가기</h3>
    <ul>
        <li><a href="/sugang/course/course_main.php">수강신청</a></li>
        <li><a href="/sugang/board/board_list.php">게시판 가기</a></li>
    </ul>
</main>

<?php
// 공통 푸터
require_once $_SERVER['DOCUMENT_ROOT'].'/sugang/include/footer.php';
?>
