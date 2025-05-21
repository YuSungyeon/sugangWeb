<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'].'/sugang/include/db.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/sugang/include/header.php';

if (!isset($_SESSION['userID'])) {
    echo "<script>alert('로그인이 필요합니다.'); location.href='/sugang/user/login.php';</script>";
    exit;
}

$userID = $_SESSION['userID'];

if (!isset($_POST['selected_courses']) || !is_array($_POST['selected_courses'])) {
    echo "<script>alert('선택한 과목이 없습니다.'); history.back();</script>";
    exit;
}

$selectedCourses = $_POST['selected_courses'];
$priorities = $_POST['priority'];
?>

<h2 class="mb-4">⏱ 수강 신청 시작</h2>
<p>오전 10시 00분 00초 기준으로 버튼을 누른 시간 차이를 밀리초 단위로 기록합니다.</p>
<p><strong>현재 시각: <span id="clock">시작 전</span></strong></p>

<table border="1" cellpadding="8" cellspacing="0">
    <thead>
        <tr>
            <th>강의코드</th>
            <th>우선순위</th>
            <th>신청</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($selectedCourses as $code): ?>
            <?php
                $code = htmlspecialchars($code);
                $priority = isset($priorities[$code]) ? (int)$priorities[$code] : null;
                if (!$priority) continue;
            ?>
            <tr>
                <td><?= $code ?></td>
                <td><?= $priority ?>순위</td>
                <td>
                    <button onclick="submitCourse('<?= $code ?>', <?= $priority ?>)">수강 신청</button>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<script>
let targetTime = new Date();
targetTime.setHours(10, 0, 0, 0); // 오전 10시 00분 00초 000

let startTime = new Date();
startTime.setHours(9, 59, 30, 0); // 시작 기준

// 시계 표시
function updateClock() {
    const now = new Date();
    const diff = now - startTime;
    const displayTime = new Date(startTime.getTime() + diff);
    const ms = String(displayTime.getMilliseconds()).padStart(3, '0');
    const timeStr = displayTime.toLocaleTimeString('ko-KR', { hour12: true }) + ' ' + ms;
    document.getElementById('clock').textContent = timeStr;
}
setInterval(updateClock, 33);

// 수강 신청 처리
function submitCourse(courseCode, priority) {
    const now = new Date();
    const timeDiff = now - targetTime; // 밀리초 차이

    fetch('submit_course.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            courseCode,
            priority,
            timeDiff
        })
    })
    .then(res => res.json())
    .then(data => {
        alert(data.message);
    })
    .catch(err => {
        alert('요청 실패: ' + err);
    });
}
</script>

<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/sugang/include/footer.php';
?>
