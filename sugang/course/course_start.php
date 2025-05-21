<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'].'/sugang/include/db.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/sugang/include/header.php';

if (!isset($_SESSION['userID'])) {
    echo "<script>alert('로그인이 필요합니다.'); location.href='/sugang/user/login.php';</script>";
    exit;
}

if (!isset($_SESSION['selected_courses']) || empty($_SESSION['selected_courses'])) {
    echo "<script>alert('수강신청할 과목이 선택되지 않았습니다.'); location.href='course_select.php';</script>";
    exit;
}

$userID = $_SESSION['userID'];
$selected_courses = $_SESSION['selected_courses'];
?>

<h1>수강신청 시뮬레이션</h1>
<p>수강신청 시간: <strong>10:00:00.000</strong></p>
<p>현재 시간: <span id="current-time">--:--:--.---</span></p>

<table border="1" cellpadding="5" cellspacing="0">
    <thead>
        <tr>
            <th>강의코드</th>
            <th>우선순위</th>
            <th>수강신청</th>
            <th>신청 결과</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($selected_courses as $course): ?>
        <tr id="row-<?=htmlspecialchars($course['course_code'])?>">
            <td><?=htmlspecialchars($course['course_code'])?></td>
            <td><?=htmlspecialchars($course['priority'])?></td>
            <td>
                <button class="apply-btn"
                    data-code="<?=htmlspecialchars($course['course_code'])?>"
                    data-priority="<?=htmlspecialchars($course['priority'])?>">수강신청</button>
            </td>
            <td class="result"></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<script>
// 시뮬레이션 시작: 09:59:30.000
const simStart = new Date();
simStart.setHours(9, 59, 30, 0);  // 기준 시뮬레이션 시작 시간

// 진짜 시간 기준으로 시뮬레이션 시간 생성
const realStart = new Date(); // 페이지 로딩된 실제 시간

function getSimulatedTime() {
    const now = new Date();
    const diff = now - realStart;  // 실제 경과 시간
    return new Date(simStart.getTime() + diff);
}

function pad(n, digits = 2) {
    return n.toString().padStart(digits, '0');
}

function updateTime() {
    const simTime = getSimulatedTime();
    const h = pad(simTime.getHours());
    const m = pad(simTime.getMinutes());
    const s = pad(simTime.getSeconds());
    const ms = pad(simTime.getMilliseconds(), 3);
    document.getElementById('current-time').textContent = `${h}:${m}:${s}.${ms}`;
}
setInterval(updateTime, 1);

// 버튼 클릭 이벤트: 수강신청
document.querySelectorAll('.apply-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        const courseCode = btn.dataset.code;
        const priority = btn.dataset.priority;
        const simTime = getSimulatedTime();

        const baseTime = new Date(simTime);
        baseTime.setHours(10, 0, 0, 0);  // 기준시간 10:00:00.000

        const timeDiff = simTime - baseTime; // 밀리초 차이 (음수 가능)

        fetch('submit_course.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                courses: [ // key 이름을 "courses"로
                    {
                        code: courseCode,
                        priority: priority,
                        time_diff: timeDiff
                    }
                ]
            })
        })
        .then(res => res.json())
        .then(data => {
            const row = document.getElementById('row-' + courseCode);
            const resultCell = row.querySelector('.result');

            const diffStr = (timeDiff >= 0 ? '+' : '') + timeDiff + 'ms';

            if (data.success) {
                resultCell.textContent = `신청 성공 (지연: ${diffStr})`;
                btn.disabled = true;
            } else {
                resultCell.textContent = `실패: ${data.message} (지연: ${diffStr})`;
            }
        })
        .catch(() => alert('서버와 통신 실패'));
    });
});
</script>

<?php require_once $_SERVER['DOCUMENT_ROOT'].'/sugang/include/footer.php'; ?>
