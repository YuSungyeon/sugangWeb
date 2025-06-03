<?php
// 데이터베이스 연결 설정 & 관리자 로그인 상태 확인 & 헤더
require_once $_SERVER['DOCUMENT_ROOT'].'/sugang/admin/include/admin_check.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/sugang/include/db.php';
include $_SERVER['DOCUMENT_ROOT'].'/sugang/include/header.php';

// 보여줄 개수 옵션과 기본값
$limit_options = [10, 15, 20];
$limit = (isset($_GET['limit']) && in_array(intval($_GET['limit']), $limit_options)) ? intval($_GET['limit']) : 10;

// 현재 페이지 (기본 : 1 page)
$page = (isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] > 0 ) ? intval($_GET['page']) : 1;

// 검색 필드와 키워드
$search_fields = ['강의명', '교수명', '강의코드'];
$search_field = isset($_GET['search_field']) && in_array($_GET['search_field'], $search_fields) ? $_GET['search_field'] : '';
$search_keyword = isset($_GET['search_keyword']) ? trim($_GET['search_keyword']) : '';

// 전체 강의 개수 구하기 (검색조건 포함) ────────────────
$count_sql = "SELECT COUNT(*) as cnt FROM 강의";
$count_params = [];

// 특정 필드에 대한 검색어가 있으면 WHERE 절 추가
if ($search_field && $search_keyword !== '') {
    $count_sql .= " WHERE `$search_field` LIKE ?";
    $count_params[] = "%$search_keyword%";
}
$count_stmt = $con->prepare($count_sql);

// 검색어가 존재할 경우만 파라미터 바인딩 수행
if ($search_field && $search_keyword !== '') {
    $count_stmt->bind_param("s", $count_params[0]);
}
$count_stmt->execute();
$count_result = $count_stmt->get_result();
$row_count = $count_result->fetch_assoc();
$total_count = $row_count['cnt'];
$count_stmt->close();
// ─────────────────────────────────────────────

// 전체 페이지 수 계산
$total_page = ceil($total_count / $limit);
if ($page > $total_page) $page = $total_page; // 페이지 번호 보정

// 강의 목록 쿼리 ────────────────────────────────
$sql = "SELECT 강의코드, 강의명, 교수명, 최대인원 FROM 강의";
$params = [];

// 검색 조건이 있을 경우 WHERE 절 추가
if ($search_field && $search_keyword !== '') {
    $sql .= " WHERE `$search_field` LIKE ?";
    $params[] = "%$search_keyword%";
}
$sql .= " ORDER BY 강의코드 LIMIT ?, ?"; // 정렬 및 페이징 적용

$stmt = $con->prepare($sql);
$offset = ($page - 1) * $limit;

if ($search_field && $search_keyword !== '') {
    // 검색어, offset, limit 바인딩
    $stmt->bind_param("sii", $params[0], $offset, $limit);
} else {
    // offset, limit 바인딩만
    $stmt->bind_param("ii", $offset, $limit);
}

$stmt->execute();
$result = $stmt->get_result();
// ──────────────────────────────────────────────
?>

<h2>강의 관리</h2>
<a href="add_lecture.php">강의 추가</a>

<form method="get" style="margin-bottom: 1em;">
    <label for="limit">페이지당 강의 수: </label>
    <select name="limit" id="limit" onchange="this.form.submit()">
        <?php foreach ($limit_options as $option): ?>
            <option value="<?= $option ?>" <?= $option == $limit ? 'selected' : '' ?>><?= $option ?></option>
        <?php endforeach; ?>
    </select>

    &nbsp;&nbsp;

    <label for="search_field">검색 필드: </label>
    <select name="search_field" id="search_field">
        <option value="">선택</option>
        <?php foreach ($search_fields as $field): ?>
            <option value="<?= htmlspecialchars($field) ?>" <?= $field == $search_field ? 'selected' : '' ?>><?= htmlspecialchars($field) ?></option>
        <?php endforeach; ?>
    </select>

    <input type="text" name="search_keyword" value="<?= htmlspecialchars($search_keyword) ?>" placeholder="검색어 입력">

    <button type="submit">검색</button>
    <a href="?" style="margin-left:10px;">초기화</a>

    <!-- 현재 페이지 정보 숨겨서 유지 -->
    <input type="hidden" name="page" value="1">
</form>

<table border="1" cellpadding="5" cellspacing="0">
    <tr>
        <th>강의코드</th>
        <th>강의명</th>
        <th>담당 교수</th>
        <th>최대인원</th>
        <th>수정</th>
        <th>삭제</th>
    </tr>
    <?php while ($row = $result->fetch_assoc()): ?>
    <tr>
        <td><?= htmlspecialchars($row['강의코드']) ?></td>
        <td><?= htmlspecialchars($row['강의명']) ?></td>
        <td><?= htmlspecialchars($row['교수명']) ?></td>
        <td><?= htmlspecialchars($row['최대인원']) ?></td>
        <td><a href="edit_lecture.php?code=<?= urlencode($row['강의코드']) ?>">수정</a></td>
        <td>
            <form action="delete_lecture.php" method="post" onsubmit="return confirm('정말 삭제하시겠습니까?');">
                <input type="hidden" name="강의코드" value="<?= htmlspecialchars($row['강의코드']) ?>">
                <input type="hidden" name="return_url" value="<?= htmlspecialchars($_SERVER['REQUEST_URI']) ?>">
                <button type="submit">삭제</button>
            </form>
        </td>
    </tr>
    <?php endwhile; ?>
</table>

<!-- 페이지 네비게이션 -->
<div style="margin-top: 1em;">
    <?php if ($page > 1): ?>
        <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>">이전</a>
    <?php endif; ?>

    페이지 <?= $page ?> / <?= $total_page ?>

    <?php if ($page < $total_page): ?>
        <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>">다음</a>
    <?php endif; ?>
</div>

<br>
<a href="/sugang/admin">-> 관리자 메인 페이지로</a>

<?php
include $_SERVER['DOCUMENT_ROOT'].'/sugang/include/footer.php';
$con->close();
?>
