<?php
session_start();

// 데이터베이스 연결 설정 & 로그인 상태 확인
require_once $_SERVER['DOCUMENT_ROOT'].'/sugang/include/db.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/sugang/include/check_login.php';

// 사용자 학번
$userID = $_SESSION['userID'];

// 사용자 수강 과목 조회
/* ──────────────────────────────── */
$sql = "SELECT 강의.강의코드, 강의명, 교수명, 시간차이
        FROM 수강신청 
        JOIN 강의 ON 수강신청.강의코드 = 강의.강의코드 
        WHERE 수강신청.학번 = ?";
$stmt = $con->prepare($sql);
$stmt->bind_param("s", $userID);
$stmt->execute();
$result = $stmt->get_result();
/* ──────────────────────────────── */

// 공통 헤더
include $_SERVER['DOCUMENT_ROOT'].'/sugang/include/header.php';
?>

<h2>나의 수강 과목</h2>

<!-- 수강 과목 출력 테이블 -->
<table border="1">
    <tr>
        <th>강의코드</th>
        <th>강의명</th>
        <th>교수명</th>
        <th>시간차이(초)</th>
    </tr>

    <?php while ($row = $result->fetch_assoc()): ?>
    <tr>
        <td><?= htmlspecialchars($row['강의코드']) ?></td>
        <td><?= htmlspecialchars($row['강의명']) ?></td>
        <td><?= htmlspecialchars($row['교수명']) ?></td>
        <td>
        <!-- 시간차이 절댓값을 초 단위로 변환 (ms → s, 소수점 5자리) -->
        <?= ROUND(abs($row['시간차이'])/1000,5) ?> 
        <?php if ($row['시간차이'] < 0): ?>
            <span style="color: red;">(전)</span> <!-- 강의 시작 전 신청 -->
        <?php else: ?>
            <span style="color: blue;">(후)</span> <!-- 강의 시작 후 신청 -->
        <?php endif; ?>
        </td>
    </tr>
    <?php endwhile; ?>

    <?php if ($result->num_rows === 0): ?>
    <tr>
        <td colspan="4">수강한 과목이 없습니다.</td>
    </tr>
    <?php endif; ?>
</table>

<p><a href="/sugang/user/mypage.php">← 마이페이지로 돌아가기</a></p>

<?php
include $_SERVER['DOCUMENT_ROOT'].'/sugang/include/footer.php';
$stmt->close();
?>
