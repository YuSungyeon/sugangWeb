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

// 각 강의코드에 대해 강의명, 교수명, 최대인원 추가 조회
$course_details = [];
$placeholders = implode(',', array_fill(0, count($selected_courses), '?'));
$codes = array_column($selected_courses, 'course_code');

$sql = "SELECT 강의코드, 강의명, 교수명, 최대인원 FROM 강의 WHERE 강의코드 IN ($placeholders)";
$stmt = $con->prepare($sql);
$stmt->bind_param(str_repeat('s', count($codes)), ...$codes);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $course_details[$row['강의코드']] = $row;
}
$stmt->close();
?>

<h1>수강신청 시뮬레이션</h1>
<p>수강신청 시간: <strong>10:00:00.000</strong></p>
<p>현재 시간: <span id="current-time">--:--:--.---</span></p>

<table border="1" cellpadding="5" cellspacing="0">
    <thead>
        <tr>
            <th>강의코드</th>
            <th>강의명</th>
            <th>교수명</th>
            <th>최대인원</th>
            <th>우선순위</th>
            <th>수강신청</th>
            <th>신청 결과</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($selected_courses as $course): 
        $code = $course['course_code'];
        $priority = $course['priority'];
        $info = $course_details[$code] ?? ['강의명'=>'-', '교수명'=>'-', '최대인원'=>'-'];
    ?>
        <tr id="row-<?=htmlspecialchars($code)?>">
            <td><?=htmlspecialchars($code)?></td>
            <td><?=htmlspecialchars($info['강의명'])?></td>
            <td><?=htmlspecialchars($info['교수명'])?></td>
            <td><?=htmlspecialchars($info['최대인원'])?></td>
            <td><?=htmlspecialchars($priority)?></td>
            <td>
                <button class="apply-btn"
                    data-code="<?=htmlspecialchars($code)?>"
                    data-priority="<?=htmlspecialchars($priority)?>">수강신청</button>
            </td>
            <td class="result"></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>


<script>
// 시뮬레이션 시작 시간: 09:59:30.000
const simStart = new Date();
simStart.setHours(9, 59, 30, 0);

// 페이지 로딩된 실제 시간
const realStart = new Date();

function getSimulatedTime() {
    const now = new Date();
    const diff = now - realStart;  // 실제 경과 시간(ms)
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

// 전체 과목 수
const totalCourses = <?= count($selected_courses) ?>;
let successCount = 0;

document.querySelectorAll('.apply-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        const courseCode = btn.dataset.code;
        const priority = btn.dataset.priority;
        const simTime = getSimulatedTime();

        const baseTime = new Date(simTime);
        baseTime.setHours(10, 0, 0, 0);

        const timeDiff = simTime - baseTime; // 밀리초 단위 (음수 가능)

        fetch('submit_course.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                courses: [
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

                successCount++;
                if (successCount === totalCourses) {
                    alert('모든 수강신청이 완료되었습니다. 홈으로 이동합니다.');
                    window.location.href = '/sugang/index.php'; // 홈 주소에 맞게 수정하세요
                }
            } else {
                resultCell.textContent = `실패: ${data.message} (지연: ${diffStr})`;
            }
        })
        .catch(() => alert('서버와 통신 실패'));
    });
});
</script>

<?php require_once $_SERVER['DOCUMENT_ROOT'].'/sugang/include/footer.php'; ?>
