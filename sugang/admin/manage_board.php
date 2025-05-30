<?php
/* ─────────────────── 관리자 권한 & DB ─────────────────── */
require_once $_SERVER['DOCUMENT_ROOT'].'/sugang/admin/include/admin_check.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/sugang/include/db.php';
include   $_SERVER['DOCUMENT_ROOT'].'/sugang/include/header.php';

/* ────────── 1. 검색 파라미터 처리 ────────── */
$keyword  = trim($_GET['q']    ?? '');         // 제목·작성자
$fromDate = trim($_GET['from'] ?? '');         // 작성일 ≥
$toDate   = trim($_GET['to']   ?? '');         // 작성일 ≤
/* 오늘 날짜 기본값 (빈 문자열인 경우) */
date_default_timezone_set('Asia/Seoul');
if ($toDate === '') $toDate = date('Y-m-d');
/* ────────── 2. 동적 WHERE 절 빌드 ────────── */
$where = [];
$params = [];  $types = '';

if ($keyword!==''){
    $where[]='(게시글.제목 LIKE ? OR 사용자.이름 LIKE ?)';
    $p='%'.$keyword.'%'; $params[]=$p; $params[]=$p; $types.='ss';
}
if ($fromDate!==''){ $where[]='DATE(게시글.작성시간)>=?'; $params[]=$fromDate; $types.='s'; }
if ($toDate  !==''){ $where[]='DATE(게시글.작성시간)<=?'; $params[]=$toDate;   $types.='s'; }


$sql = "
  SELECT 게시글.게시글ID, 게시글.제목, 게시글.작성시간,
         사용자.이름 AS 작성자
    FROM 게시글
    JOIN 사용자 ON 게시글.작성자 = 사용자.학번
";
if ($where) $sql .= ' WHERE '.implode(' AND ', $where);
$sql .= ' ORDER BY 게시글.게시글ID DESC';

/* ────────── 3. 쿼리 실행 (프리페어드) ────────── */
if ($where) {
    $stmt = $con->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $con->query($sql);
}
?>
<!DOCTYPE html><html lang="ko"><head>
<meta charset="utf-8"><title>게시판 관리</title></head><body>

<h2>게시판 관리</h2>

<!-- ────────── 4. 검색 폼 ────────── -->
<form method="get" style="margin-bottom:12px;">
  <input type="text" name="q" value="<?=htmlspecialchars($keyword)?>"
         placeholder="제목 또는 작성자 검색" style="width:200px;">
  &nbsp;작성일:
  <input type="date" name="from" value="<?=htmlspecialchars($fromDate)?>">
  ~
  <input type="date" name="to"   value="<?=htmlspecialchars($toDate)?>">
  <button type="submit">검색</button>
  <?php if($keyword!==''||$fromDate!==''||($_GET['to']??'')!==''): ?>
    <a href="manage_board.php" style="margin-left:8px;">검색 초기화</a>
  <?php endif;?>
</form>

<table border="1" cellpadding="5">
 <tr>
   <th>번호</th><th>제목</th><th>작성자</th><th>작성일</th><th>관리</th>
 </tr>
<?php while($row=$result->fetch_assoc()): ?>
 <tr>
   <td><?=htmlspecialchars($row['게시글ID'])?></td>
   <td><a href="/sugang/board/board_view.php?id=<?=$row['게시글ID']?>"><?=htmlspecialchars($row['제목'])?></a></td>
   <td><?=htmlspecialchars($row['작성자'])?></td>
   <td><?=htmlspecialchars($row['작성시간'])?></td>
   <td>
     <form action="board_delete.php" method="post"
           style="display:inline;"
           onsubmit="return confirm('정말 삭제하시겠습니까?');">
       <input type="hidden" name="id" value="<?=$row['게시글ID']?>">
       <button type="submit">🗑 삭제</button>
     </form>
   </td>
 </tr>
<?php endwhile;?>
</table>

<br><a href="/sugang/admin">→ 관리자 메인 페이지로</a>

<?php
include $_SERVER['DOCUMENT_ROOT'].'/sugang/include/footer.php';
$con->close();
?>