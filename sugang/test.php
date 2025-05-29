<?php
// 세션 시작 (로그인 여부 확인용)
session_start();

// 공통 헤더 포함 (HTML <head>와 상단 메뉴 구성 포함)
require_once $_SERVER['DOCUMENT_ROOT'] . '/sugang/include/header.php';
?>

<main style="padding: 20px;">
    <h2>환영합니다!</h2>

    <?php if (!empty($_SESSION['userID'])): ?>
        <!-- 로그인 상태 -->
        <p>
            <strong><?= htmlspecialchars($_SESSION['name'] ?? '', ENT_QUOTES, 'UTF-8') ?></strong>님, 안녕하세요
        </p>
        <nav>
            <a href="/sugang/user/mypage.php">마이페이지</a> |
            <a href="/sugang/user/logout.php">로그아웃</a>
        </nav>

        <?php if (!empty($_SESSION['is_admin'])): ?>
            <!-- 관리자일 경우 관리자 페이지 링크 -->
            <p>
                <a href="/sugang/admin"><strong>[관리자 페이지 바로가기]</strong></a>
            </p>
        <?php endif; ?>

    <?php else: ?>
        <!-- 비로그인 상태 -->
        <nav>
            <a href="/sugang/user/login.php">로그인</a> |
            <a href="/sugang/user/signup.php">회원가입</a>
        </nav>
    <?php endif; ?>

    <hr>

    <section>
        <h3>바로가기</h3>
        <ul>
            <li><a href="/sugang/course/course_main.php">수강신청</a></li>
            <li><a href="/sugang/board/board_list.php">게시판 가기</a></li>
        </ul>
    </section>
</main>

<?php
// 공통 푸터 포함
require_once $_SERVER['DOCUMENT_ROOT'] . '/sugang/include/footer.php';
?>
