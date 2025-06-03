<?php
session_start();

// ───── POST 분기: JSON 반환
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json; charset=utf-8');

    $action      = $_POST['action']; // 수행할 액션 확인
    $course_code = $_POST['course_code'] ?? ''; // 처리할 강의코드

    // 동작에 필요한 강의 코드를 못받은 경우 
    if (!$course_code) {
        echo json_encode(['success' => false, 'msg' => '잘못된 요청입니다.']);
        exit;   // HTML 출력 차단
    }

    // 강의 선택 배열이 없으면 세션 배열 초기화 (빈 장바구니)
    $_SESSION['selected_courses'] ??= [];

    // ───── 과목 추가 요청 ─────
    if ($action === 'add_course') {
        // 중복 추가 확인 (아닌경우에 진행)
        if (!in_array($course_code, $_SESSION['selected_courses'], true)) {
            $_SESSION['selected_courses'][] = $course_code;
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'msg' => '이미 담긴 과목입니다.']);
        }
    }
    // ───── 과목 삭제 요청 ─────
    elseif ($action === 'remove_course') {
        // 배열에서 해당 과목이 존재하는지 확인 후 진행
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

// 데이터베이스 연결 설정 & 로그인 상태 확인 & 헤더
require_once $_SERVER['DOCUMENT_ROOT'].'/sugang/include/db.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/sugang/include/header.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/sugang/include/check_login.php';

// GET 파라미터 처리
$limit_options = [10, 15, 20];
$limit = (isset($_GET['limit']) && in_array(intval($_GET['limit']), $limit_options)) ? intval($_GET['limit']) : 10;
$page = (isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] > 0 ) ? intval($_GET['page']) : 1;
$search_fields = ['강의명', '교수명', '강의코드'];
$search_field = isset($_GET['search_field']) && in_array($_GET['search_field'], $search_fields) ? $_GET['search_field'] : '';
$search_keyword = isset($_GET['search_keyword']) ? trim($_GET['search_keyword']) : '';


// 전체 강의 개수 조회 위한 조건절 및 파라미터 준비
$where = "";
$params = [];
$types = "";

if ($search_field && $search_keyword) {
    $where = "WHERE $search_field LIKE ?";
    $params[] = '%' . $search_keyword . '%';
    $types .= 's';
}

// 전체 강의 개수 조회
/* ─────────────────────────────────── */
$count_sql = "SELECT COUNT(*) FROM 강의 $where";
$stmt = $con->prepare($count_sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$stmt->bind_result($total_rows);
$stmt->fetch();
$stmt->close();
/* ─────────────────────────────────── */

// 페이지 계산
$total_pages = ceil($total_rows / $limit);
$offset = ($page - 1) * $limit;

// 강의 목록 조회
/* ─────────────────────────────────── */
$sql = "SELECT 강의코드, 강의명, 교수명, 최대인원 
        FROM 강의 $where 
        ORDER BY 강의코드 LIMIT ? OFFSET ?";

$params[] = $limit;
$params[] = $offset;
$types .= 'ii';

$stmt = $con->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();
/* ─────────────────────────────────── */

// 세션에 담긴 과목 상세 조회
/* ─────────────────────────────────── */
$selected_courses = $_SESSION['selected_courses'] ?? []; 
$selected_details = [];

if (!empty($selected_courses)) {
    $placeholders = implode(',', array_fill(0, count($selected_courses), '?'));
    $type_str = str_repeat('s', count($selected_courses));

    $sql2 = "SELECT 강의코드, 강의명, 교수명, 최대인원 
            FROM 강의 
            WHERE 강의코드 IN ($placeholders)";

    $stmt2 = $con->prepare($sql2);
    $stmt2->bind_param($type_str, ...$selected_courses);
    $stmt2->execute();
    $res2 = $stmt2->get_result();
    while ($row = $res2->fetch_assoc()) {
        $selected_details[$row['강의코드']] = $row;
    }
    $stmt2->close();
}
/* ─────────────────────────────────── */
?>

<h1>수강신청 과목 선택</h1>
<!-- 선택된 과목 출력 영역 -->
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

<!-- 검색/페이징 폼 (드롭다운 사용)-->
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

<!-- 강의 목록 테이블 -->
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
<!-- 페이지 네비게이션 -->
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
<!-- 신청 단계 이동 폼 -->
<form method="post" action="course_start.php" id="finalSubmitForm">
    <input type="hidden" name="selected_courses" id="selectedCoursesInput">
    <button type="submit">신청 단계로</button>
</form>

<!-- 스크립트: 과목 담기/취소/제출 로직 -->
<script> 
// ─────────────────────── DOM 요소 참조 ───────────────────────
const addButtons = document.querySelectorAll('.add-course-btn'); // 과목 담기 버튼들
const selectedCoursesTableBody = document.getElementById('selectedCoursesTableBody'); // 담은 과목 표의 tbody
const selectedCoursesInput = document.getElementById('selectedCoursesInput'); // 폼에 전송할 숨겨진 input
const finalSubmitForm = document.getElementById('finalSubmitForm'); // 최종 제출 form
const noCoursesParagraph = document.getElementById('noCoursesParagraph'); // "담긴 과목 없음" 문구

// 세션에서 불러온 담은 과목로 리스트 초기화
let selectedCourses = <?= json_encode(array_values($selected_courses)) ?>;


function refreshNoCourseMessage() {
    if (selectedCourses.length === 0 && noCoursesParagraph) {
        noCoursesParagraph.style.display = 'block'; // 아직 담긴 과목이 없습니다. 표시
    } else if (noCoursesParagraph) {
        noCoursesParagraph.style.display = 'none'; // 아직 담긴 과목이 없습니다. 표시 안하기
    }
}

// ─────────────────────── 과목을 표에 추가하는 로직 ───────────────────────
function addCourseToTable(course) {
    refreshNoCourseMessage(); // 문구 갱신

    const tr = document.createElement('tr'); // 새로운 tr 생성
    tr.dataset.code = course.강의코드;        // 과목코드를 data 속성에 저장

    // tr에 강의 정보와 '취소' 버튼 삽입
    tr.innerHTML = `
        <td>${course.강의코드}</td>
        <td>${course.강의명}</td>
        <td>${course.교수명}</td>
        <td>${course.최대인원}</td>
        <td><button class="remove-course-btn">취소</button></td>
    `;

    selectedCoursesTableBody.appendChild(tr); // tbody에 행(tr) 추가

    // '취소' 버튼에 클릭 이벤트 바인딩
    tr.querySelector('.remove-course-btn').addEventListener('click', () => removeCourse(course.강의코드, tr));
}

// ─────────────────────── 과목을 표에서 제거하는 로직 ───────────────────────
function removeCourse(code, rowElement) {
    const formData = new FormData();
    formData.append('action', 'remove_course');   // POST로 action 명시
    formData.append('course_code', code);         // 제거할 과목코드 전송

    fetch('', { method: 'POST', body: formData }) // 현재 페이지에 POST 전송
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                rowElement.remove(); // 테이블에서 행(tr) 제거
                selectedCourses = selectedCourses.filter(c => c !== code); // 배열에서도 제거
                refreshNoCourseMessage();
            } else {
                alert(data.msg || '담기 취소에 실패했습니다.');
            }
        });
}

// ─────────────────────── "담기" 버튼 클릭 이벤트 처리 ───────────────────────
addButtons.forEach(btn => {
    btn.addEventListener('click', () => {
        const courseCode = btn.dataset.code;

        if (selectedCourses.includes(courseCode)) {
            alert('이미 담긴 과목입니다.');
            return;
        }

        const formData = new FormData();
        formData.append('action', 'add_course');      // POST로 action 전송
        formData.append('course_code', courseCode);   // 담을 과목코드 전송

        fetch('', { method: 'POST', body: formData }) // 현재 페이지에 POST 전송
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    selectedCourses.push(courseCode); // 배열에 추가

                    // 클릭된 버튼의 부모 <tr>에서 강의 정보 추출
                    const tr = btn.closest('tr');
                    const course = {
                        강의코드: courseCode,
                        강의명: tr.children[1].textContent,
                        교수명: tr.children[2].textContent,
                        최대인원: tr.children[3].textContent
                    };

                    addCourseToTable(course); // 테이블에 반영
                } else {
                    alert(data.msg || '과목 담기에 실패했습니다.');
                }
            });
    });
});

// ─────────────────────── 페이지 생성시 기존 '취소' 버튼들에도 이벤트 바인딩 ───────────────────────
document.querySelectorAll('.remove-course-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const tr = this.closest('tr');              // 현재 버튼이 속한 <tr>
        const courseCode = tr.dataset.code;         // 과목 코드 추출
        removeCourse(courseCode, tr);               // 제거 요청
    });
});

// ─────────────────────── '신청 단계로' 클릭 시, 선택 과목 확인 및 전송 처리 ───────────────────────
finalSubmitForm.addEventListener('submit', e => {
    if (selectedCourses.length === 0) {
        alert('최소 1개 이상의 과목을 담아야 합니다.');
        e.preventDefault(); // 전송 중단
        return;
    }

    // JSON 문자열로 숨겨진 input에 저장 (서버에 POST로 전달)
    selectedCoursesInput.value = JSON.stringify(selectedCourses);
});
</script>

<?php require_once $_SERVER['DOCUMENT_ROOT'].'/sugang/include/footer.php'; ?>