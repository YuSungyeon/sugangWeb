<?php
// select_course.php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'].'/sugang/include/db.php';

/* ---------- POST AJAX 분기: JSON만 반환 ---------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json; charset=utf-8');  // JSON MIME 지정

    $action      = $_POST['action'];
    $course_code = $_POST['course_code'] ?? '';

    if (!$course_code) {
        echo json_encode(['success' => false, 'msg' => '잘못된 요청입니다.']);
        exit;   // HTML 출력 차단
    }

    // 세션 배열 초기화
    $_SESSION['selected_courses'] ??= [];

    if ($action === 'add_course') {
        if (!in_array($course_code, $_SESSION['selected_courses'], true)) {
            $_SESSION['selected_courses'][] = $course_code;
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'msg' => '이미 담긴 과목입니다.']);
        }
    } elseif ($action === 'remove_course') {
        if (($idx = array_search($course_code, $_SESSION['selected_courses'], true)) !== false) {
            unset($_SESSION['selected_courses'][$idx]);
            $_SESSION['selected_courses'] = array_values($_SESSION['selected_courses']);
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'msg' => '해당 과목이 담겨 있지 않습니다.']);
        }
    }

    exit;
}

require_once $_SERVER['DOCUMENT_ROOT'].'/sugang/include/header.php';

if (!isset($_SESSION['userID'])) {
    echo "<script>alert('로그인이 필요합니다.'); location.href='/sugang/user/login.php';</script>";
    exit;
}

// GET 파라미터 처리
$limit_options = [10, 15, 20];
$limit = (isset($_GET['limit']) && in_array(intval($_GET['limit']), $limit_options)) ? intval($_GET['limit']) : 10;
$page = (isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] > 0 ) ? intval($_GET['page']) : 1;
$search_fields = ['강의명', '교수명', '강의코드'];
$search_field = isset($_GET['search_field']) && in_array($_GET['search_field'], $search_fields) ? $_GET['search_field'] : '';
$search_keyword = isset($_GET['search_keyword']) ? trim($_GET['search_keyword']) : '';


// 전체 강의 개수 조회
$where = "";
$params = [];
$types = "";

if ($search_field && $search_keyword) {
    $where = "WHERE $search_field LIKE ?";
    $params[] = '%' . $search_keyword . '%';
    $types .= 's';
}

$count_sql = "SELECT COUNT(*) FROM 강의 $where";
$stmt = $con->prepare($count_sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$stmt->bind_result($total_rows);
$stmt->fetch();
$stmt->close();

// 페이지네이션 계산
$total_pages = ceil($total_rows / $limit);
$offset = ($page - 1) * $limit;

// 강의 목록 조회
$sql = "SELECT 강의코드, 강의명, 교수명, 최대인원 FROM 강의 $where ORDER BY 강의코드 LIMIT ? OFFSET ?";
$params[] = $limit;
$params[] = $offset;
$types .= 'ii';

$stmt = $con->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

// 세션에 담긴 과목 상세 조회
$selected_courses = $_SESSION['selected_courses'] ?? [];
$selected_details = [];

if (!empty($selected_courses)) {
    $placeholders = implode(',', array_fill(0, count($selected_courses), '?'));
    $type_str = str_repeat('s', count($selected_courses));
    $stmt2 = $con->prepare("SELECT 강의코드, 강의명, 교수명, 최대인원 FROM 강의 WHERE 강의코드 IN ($placeholders)");
    $stmt2->bind_param($type_str, ...$selected_courses);
    $stmt2->execute();
    $res2 = $stmt2->get_result();
    while ($row = $res2->fetch_assoc()) {
        $selected_details[$row['강의코드']] = $row;
    }
    $stmt2->close();
}
?>

<h1>수강신청 과목 선택</h1>

<h2>담긴 과목 목록</h2>
<?php if (!empty($selected_details)): ?>
<table border="1" cellpadding="5" cellspacing="0">
    <thead>
        <tr>
            <th>강의코드</th>
            <th>강의명</th>
            <th>교수명</th>
            <th>최대인원</th>
            <th>취소</th>
        </tr>
    </thead>
    <tbody id="selectedCoursesTableBody">
        <?php foreach ($selected_details as $course): ?>
        <tr data-code="<?=htmlspecialchars($course['강의코드'])?>">
            <td><?=htmlspecialchars($course['강의코드'])?></td>
            <td><?=htmlspecialchars($course['강의명'])?></td>
            <td><?=htmlspecialchars($course['교수명'])?></td>
            <td><?=htmlspecialchars($course['최대인원'])?></td>
            <td><button class="remove-course-btn">취소</button></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php else: ?>
<table border="1" cellpadding="5" cellspacing="0">
    <thead>
        <tr>
            <th>강의코드</th><th>강의명</th><th>교수명</th><th>최대인원</th><th>취소</th>
        </tr>
    </thead>
    <!-- ★ 비어 있더라도 tbody는 항상 존재 ★ -->
    <tbody id="selectedCoursesTableBody"></tbody>
</table>
<p id="noCoursesParagraph">아직 담긴 과목이 없습니다.</p>
<?php endif; ?>

<hr>

<form method="get" id="searchForm">
    <label for="limit">페이지당 강의 수: </label>
    <select name="limit" id="limit" onchange="document.getElementById('searchForm').submit()">
        <?php foreach ($limit_options as $option): ?>
        <option value="<?= $option ?>" <?= $option == $limit ? 'selected' : '' ?>><?= $option ?></option>
        <?php endforeach; ?>
    </select>

    &nbsp;&nbsp;

    <label for="search_field">검색 필드: </label>
    <select name="search_field" id="search_field">
        <option value="">검색 필드를 선택하세요</option>
        <?php foreach ($search_fields as $field): ?>
        <option value="<?= htmlspecialchars($field) ?>" <?= $field == $search_field ? 'selected' : '' ?>><?= htmlspecialchars($field) ?></option>
        <?php endforeach; ?>
    </select>

    <input type="text" name="search_keyword" value="<?= htmlspecialchars($search_keyword) ?>" placeholder="검색어 입력">
    <button type="submit">검색</button>
    <a href="select_course.php">초기화</a>
</form>

<br>

<table border="1" cellpadding="5" cellspacing="0">
    <thead>
        <tr>
            <th>강의코드</th>
            <th>강의명</th>
            <th>교수명</th>
            <th>최대인원</th>
            <th>담기</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?=htmlspecialchars($row['강의코드'])?></td>
            <td><?=htmlspecialchars($row['강의명'])?></td>
            <td><?=htmlspecialchars($row['교수명'])?></td>
            <td><?=htmlspecialchars($row['최대인원'])?></td>
            <td><button type="button" class="add-course-btn" data-code="<?=htmlspecialchars($row['강의코드'])?>">담기</button></td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>

<br>
<!-- 페이지네이션 -->
<div>
    <?php if ($page > 1): ?>
        <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>">이전</a>
    <?php endif; ?>

    페이지 <?= $page ?> / <?= $total_pages ?>

    <?php if ($page < $total_pages): ?>
        <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>">다음</a>
    <?php endif; ?>
</div>

<br>
<form method="post" action="course_start.php" id="finalSubmitForm">
    <input type="hidden" name="selected_courses" id="selectedCoursesInput">
    <button type="submit">다음 단계로</button>
</form>

<script>
const addButtons = document.querySelectorAll('.add-course-btn');
const selectedCoursesTableBody = document.getElementById('selectedCoursesTableBody');
const selectedCoursesInput = document.getElementById('selectedCoursesInput');
const finalSubmitForm = document.getElementById('finalSubmitForm');
const noCoursesParagraph = document.getElementById('noCoursesParagraph');

let selectedCourses = <?= json_encode(array_values($selected_courses)) ?>;

function refreshNoCourseMessage() {
    if (selectedCourses.length === 0 && noCoursesParagraph) {
        noCoursesParagraph.style.display = 'block';
    } else if (noCoursesParagraph) {
        noCoursesParagraph.style.display = 'none';
    }
}

function addCourseToTable(course) {
    refreshNoCourseMessage();
    const tr = document.createElement('tr');
    tr.dataset.code = course.강의코드;
    tr.innerHTML = `
        <td>${course.강의코드}</td>
        <td>${course.강의명}</td>
        <td>${course.교수명}</td>
        <td>${course.최대인원}</td>
        <td><button class="remove-course-btn">취소</button></td>
    `;
    selectedCoursesTableBody.appendChild(tr);
    tr.querySelector('.remove-course-btn').addEventListener('click', () => removeCourse(course.강의코드, tr));
}

function removeCourse(code, rowElement) {
    const formData = new FormData();
    formData.append('action', 'remove_course');
    formData.append('course_code', code);

    fetch('', { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                rowElement.remove();
                selectedCourses = selectedCourses.filter(c => c !== code);
                refreshNoCourseMessage();
            } else {
                alert(data.msg || '담기 취소에 실패했습니다.');
            }
        });
}

addButtons.forEach(btn => {
    btn.addEventListener('click', () => {
        const courseCode = btn.dataset.code;
        if (selectedCourses.includes(courseCode)) {
            alert('이미 담긴 과목입니다.');
            return;
        }

        const formData = new FormData();
        formData.append('action', 'add_course');
        formData.append('course_code', courseCode);

        fetch('', { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    selectedCourses.push(courseCode);
                    const tr = btn.closest('tr');
                    const course = {
                        강의코드: courseCode,
                        강의명: tr.children[1].textContent,
                        교수명: tr.children[2].textContent,
                        최대인원: tr.children[3].textContent
                    };
                    addCourseToTable(course);
                } else {
                    alert(data.msg || '과목 담기에 실패했습니다.');
                }
            });
    });
});

document.querySelectorAll('.remove-course-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const tr = this.closest('tr');
        const courseCode = tr.dataset.code;
        removeCourse(courseCode, tr);
    });
});

finalSubmitForm.addEventListener('submit', e => {
    if (selectedCourses.length === 0) {
        alert('최소 1개 이상의 과목을 담아야 합니다.');
        e.preventDefault();
        return;
    }
    selectedCoursesInput.value = JSON.stringify(selectedCourses);
});
</script>

<?php require_once $_SERVER['DOCUMENT_ROOT'].'/sugang/include/footer.php'; ?>