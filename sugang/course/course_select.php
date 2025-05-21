<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'].'/sugang/include/db.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/sugang/include/header.php';

// 로그인 체크
if (!isset($_SESSION['userID'])) {
    echo "<script>alert('로그인이 필요합니다.'); location.href='/sugang/user/login.php';</script>";
    exit;
}

$userID = $_SESSION['userID'];

// 강의 목록 조회
$sql = "SELECT 강의코드, 강의명, 교수명, 최대인원 FROM 강의 ORDER BY 강의코드";
$result = $con->query($sql);
if (!$result) {
    echo "강의 목록 조회 중 오류가 발생했습니다.";
    exit;
}

// POST 처리 : 선택한 과목+우선순위를 세션에 저장 후 course_start.php로 이동
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $selected_courses = $_POST['course_code'] ?? [];
    $priorities = $_POST['priority'] ?? [];

    if (empty($selected_courses)) {
        echo "<script>alert('과목을 최소 1개 이상 선택하세요.'); history.back();</script>";
        exit;
    }

    // 우선순위 중복, 공백 체크 등 간단 검증 (필요하면 추가 가능)
    $used_priorities = [];
    foreach ($priorities as $p) {
        if (!is_numeric($p) || $p < 1) {
            echo "<script>alert('우선순위는 1 이상의 숫자여야 합니다.'); history.back();</script>";
            exit;
        }
        if (in_array($p, $used_priorities)) {
            echo "<script>alert('우선순위가 중복되었습니다.'); history.back();</script>";
            exit;
        }
        $used_priorities[] = $p;
    }

    // 세션에 저장
    $to_session = [];
    foreach ($selected_courses as $idx => $code) {
        $to_session[] = [
            'course_code' => $code,
            'priority' => (int)$priorities[$idx],
        ];
    }
    $_SESSION['selected_courses'] = $to_session;

    header('Location: course_start.php');
    exit;
}
?>

<h1>수강신청 과목 선택 및 우선순위 설정</h1>

<form method="post" id="courseForm">
    <table border="1" cellpadding="5" cellspacing="0">
        <thead>
            <tr>
                <th>선택</th>
                <th>강의코드</th>
                <th>강의명</th>
                <th>교수명</th>
                <th>최대인원</th>
                <th>우선순위</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><input type="checkbox" name="course_code[]" value="<?=htmlspecialchars($row['강의코드'])?>" class="course-checkbox"></td>
                <td><?=htmlspecialchars($row['강의코드'])?></td>
                <td><?=htmlspecialchars($row['강의명'])?></td>
                <td><?=htmlspecialchars($row['교수명'])?></td>
                <td><?=htmlspecialchars($row['최대인원'])?></td>
                <td><input type="number" name="priority[]" min="1" style="width: 60px;" disabled class="priority-input"></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <br>
    <button type="submit">다음 단계로</button>
</form>

<script>
    const checkboxes = document.querySelectorAll('.course-checkbox');
    const priorityInputs = document.querySelectorAll('.priority-input');

    checkboxes.forEach((checkbox, idx) => {
        checkbox.addEventListener('change', () => {
            if (checkbox.checked) {
                priorityInputs[idx].disabled = false;
                priorityInputs[idx].value = idx + 1; // 기본값 예시: 행 번호
            } else {
                priorityInputs[idx].disabled = true;
                priorityInputs[idx].value = '';
            }
        });
    });

    // 제출 전 간단한 체크
    document.getElementById('courseForm').addEventListener('submit', (e) => {
        let checkedCount = 0;
        const usedPriorities = new Set();
        for (let i = 0; i < checkboxes.length; i++) {
            if (checkboxes[i].checked) {
                checkedCount++;
                const p = priorityInputs[i].value.trim();
                if (!p || isNaN(p) || p < 1) {
                    alert('선택된 과목의 우선순위를 1 이상의 숫자로 입력하세요.');
                    e.preventDefault();
                    return;
                }
                if (usedPriorities.has(p)) {
                    alert('우선순위가 중복되었습니다.');
                    e.preventDefault();
                    return;
                }
                usedPriorities.add(p);
            }
        }
        if (checkedCount === 0) {
            alert('과목을 최소 1개 이상 선택하세요.');
            e.preventDefault();
        }
    });
</script>

<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/sugang/include/footer.php';
?>
