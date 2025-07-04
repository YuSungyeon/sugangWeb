<?php
// 데이터베이스 연결 설정 & 관리자 로그인 상태 확인 & 헤더
require_once $_SERVER['DOCUMENT_ROOT'].'/sugang/admin/include/admin_check.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/sugang/include/db.php';
include $_SERVER['DOCUMENT_ROOT'].'/sugang/include/header.php';

// 검색 파라미터
$keyword = trim($_GET['q'] ?? '');

// 조회 쿼리 ────────────────────────────────────────
// 수강신청 + 사용자 + 강의 테이블을 조인하여 모든 수강내역을 조회
$baseSql = "
  SELECT s.학번, u.이름,
         s.강의코드, c.강의명, c.교수명,
         s.시간차이, s.중요도
    FROM 수강신청 s
    JOIN 사용자 u ON s.학번 = u.학번
    JOIN 강의   c ON s.강의코드 = c.강의코드
";

// 검색 조건이 있는 경우 WHERE 절 구성
$params = [];
$types = '';
if ($keyword !== '') {
    $baseSql .= " WHERE s.학번    LIKE ?
                   OR u.이름       LIKE ?
                   OR c.강의명     LIKE ?
                   OR c.교수명     LIKE ? ";
    $like = '%'.$keyword.'%';
    $params = [$like,$like,$like,$like];
    $types  = 'ssss';
}

// 정렬 기준: 강의코드 오름차순, 중요도 내림차순
$baseSql .= " ORDER BY s.강의코드 ASC, s.중요도 DESC";

// 검색 조건이 있는 경우 바인드 후 실행
if ($params) {
    $stmt = $con->prepare($baseSql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $con->query($baseSql);
}
?>

<!-- ───────────── HTML 출력 시작 ───────────── -->
<h2>수강신청 관리</h2>

<!-- ────────── 검색 폼 ────────── -->
<form method="get" style="margin-bottom:12px;">
  <input type="text" name="q" value="<?=htmlspecialchars($keyword)?>"
         placeholder="학번·이름·강의명·교수명 검색" style="width:200px;">
  <button type="submit">검색</button>
  <?php if ($keyword!==''): ?>
     <a href="manage_courses.php" style="margin-left:8px;">검색 초기화</a>
  <?php endif; ?>
</form>

<!-- ────────── 범위(시간차이) 외 일괄 삭제 폼 ────────── -->
<form action="courses_delete_range.php" method="post"
      onsubmit="return confirmRange();"
      style="margin-bottom:15px;">
  결과 <strong>보존 범위</strong> 입력:
  <input type="number" name="min" required style="width:80px;"> ms &nbsp;~&nbsp;
  <input type="number" name="max" required style="width:80px;"> ms
  <button type="submit">범위 제외 전체 삭제</button>
</form>

<!-- ────────── 수강 신청 목록 출력 ────────── -->
<table border="1" cellpadding="5">
 <tr>
   <th>학번</th><th>이름</th><th>강의코드</th><th>강의명</th>
   <th>교수명</th><th>시간차이(ms)</th><th>중요도</th><th>관리</th>
 </tr>

<?php while($row=$result->fetch_assoc()): ?>
<tr>
  <td><?=htmlspecialchars($row['학번'])?></td>
  <td><?=htmlspecialchars($row['이름'])?></td>
  <td><?=htmlspecialchars($row['강의코드'])?></td>
  <td><?=htmlspecialchars($row['강의명'])?></td>
  <td><?=htmlspecialchars($row['교수명'])?></td>
  <td><?=htmlspecialchars($row['시간차이'])?></td>
  <td><?=htmlspecialchars($row['중요도'])?></td>
  <td>
    <!-- 개별 삭제 버튼 -->
    <form action="courses_delete.php" method="post"
          style="display:inline;"
          onsubmit="return confirm('정말 삭제하시겠습니까?');">
      <input type="hidden" name="학번" value="<?=$row['학번']?>">
      <input type="hidden" name="강의코드" value="<?=$row['강의코드']?>">
      <button type="submit">🗑 삭제</button>
    </form>
  </td>
</tr>
<?php endwhile; ?>
</table>

<br><a href="/sugang/admin">→ 관리자 메인 페이지로</a>

<script>
// 삭제 범위 확인 로직
function confirmRange(){
  const f=this;
  const min=Number(f.min.value), max=Number(f.max.value);
  if(min>max){ //최소가 최대보다 큰 경우 배제
     alert('최소값이 최대값보다 클 수 없습니다.'); return false;
  }
  // 진행 여부
  return confirm(`${min}ms ~ ${max}ms 범위를 보존하고\n그 외 신청 내역을 모두 삭제합니다. 진행할까요?`);
}
</script>

<?php
include $_SERVER['DOCUMENT_ROOT'].'/sugang/include/footer.php';
$con->close();
?>