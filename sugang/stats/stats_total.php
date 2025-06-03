<?php
/* ─────────── 0. 디버그 출력 ─────────── */
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

/* ─────────── 1. DB 연결 ─────────── */
require_once $_SERVER['DOCUMENT_ROOT'].'/sugang/include/db.php';

/* ─────────── 2. 파라미터 ─────────── */
$perPage = 10;
$page    = max(1, intval($_GET['page'] ?? 1));
$offset  = ($page - 1) * $perPage;
$keyword = trim($_GET['lecture'] ?? '');               // 강의명 or 교수명

/* ─────────── 3. WHERE 절 ─────────── */
$where  = '';
$types  = '';
$params = [];

if ($keyword !== '') {
    $where  = ' WHERE (강의.강의명 LIKE ? OR 강의.교수명 LIKE ?) ';
    $like   = '%'.$keyword.'%';
    $params = [$like, $like];
    $types  = 'ss';
}

/* ─────────── 4-A. 총 행 수 ─────────── */
$sqlCnt = "
  SELECT COUNT(DISTINCT 수강신청.강의코드) AS cnt
    FROM 수강신청
    JOIN 강의 ON 수강신청.강의코드 = 강의.강의코드
    $where";
$stmtCnt = $con->prepare($sqlCnt);
if ($params) $stmtCnt->bind_param($types, ...$params);
$stmtCnt->execute();
$totalRows  = $stmtCnt->get_result()->fetch_assoc()['cnt'] ?? 0;
$totalPages = max(1, ceil($totalRows / $perPage));
$stmtCnt->close();

/* ─────────── 4-B. 실제 강의별 통계 ─────────── */
$sqlData = "
  SELECT  강의.강의명,
          강의.교수명,
          COUNT(*)                    AS 신청인원,
          ROUND(AVG(중요도),1)        AS 평균우선순위,
          ROUND(AVG(시간차이)/1000,5) AS 평균클릭차이
    FROM 수강신청
    JOIN 강의 ON 수강신청.강의코드 = 강의.강의코드
    $where
GROUP BY 수강신청.강의코드, 강의.교수명
ORDER BY 신청인원 DESC
LIMIT $perPage OFFSET $offset";

$stmt1 = $con->prepare($sqlData);
if ($params) $stmt1->bind_param($types, ...$params);
$stmt1->execute();
$result1 = $stmt1->get_result();

/* ─────────── 5. 학번별 통계 (최근 7년) ─────────── */
$curY = date('Y'); $startY = $curY - 6;
$sql2 = "
  SELECT SUBSTRING(학번,1,4) AS 입학년도,
         COUNT(DISTINCT 학번) AS 인원수,
         ROUND(AVG(시간차이)/1000,5) AS 평균최초클릭
    FROM 수강신청
   WHERE 중요도 = 1
     AND LENGTH(학번) >= 4
     AND SUBSTRING(학번,1,4) BETWEEN ? AND ?
GROUP BY 입학년도
ORDER BY 입학년도 DESC";
$stmt2 = $con->prepare($sql2);
$stmt2->bind_param('ii', $startY, $curY);
$stmt2->execute(); $result2 = $stmt2->get_result();
?>
<!DOCTYPE html>
<html lang="ko">
<head>
<meta charset="UTF-8">
<title>전체 수강신청 통계</title>
<style>
 table{border-collapse:collapse;width:80%;margin-bottom:40px}
 th,td{border:1px solid #ccc;padding:8px;text-align:center}
 h2{margin-top:50px}
 .pager a{margin:0 6px;text-decoration:none}
 .pager span{font-weight:bold}
</style>
</head>
<body>
<h1>전체 수강신청 통계</h1>

<!-- 1. 강의별 통계 -->
<h2>1. 강의별 수강신청 통계</h2>
<h4>(수강신청 인원 수 기준 정렬)</h4>

<!-- 검색 폼 (강의명·교수명) -->
<form method="get" style="margin-bottom:14px;">
  <input type="text" name="lecture" value="<?=htmlspecialchars($keyword)?>"
         placeholder="강의명 또는 교수명" style="width:220px;">
  <button type="submit">검색</button>
  <?php if($keyword!==''): ?>
    <a href="stats_total.php" style="margin-left:8px;">검색 초기화</a>
  <?php endif;?>
</form>

<table>
 <tr>
   <th>강의명</th><th>교수명</th><th>신청 인원</th>
   <th>평균 우선순위</th><th>평균 클릭 시간(s)</th>
 </tr>
 <?php while($row = $result1->fetch_assoc()): ?>
  <tr>
    <td><?=htmlspecialchars($row['강의명'])?></td>
    <td><?=htmlspecialchars($row['교수명'])?></td>
    <td><?=$row['신청인원']?></td>
    <td><?=$row['평균우선순위']?></td>
    <td>
      <?=abs($row['평균클릭차이'])?>
      <?=$row['평균클릭차이']<0
          ? '<span style="color:red;">(전)</span>'
          : '<span style="color:blue;">(후)</span>'?>
    </td>
  </tr>
 <?php endwhile; ?>
 <?php if($result1->num_rows == 0): ?>
  <tr><td colspan="5">데이터가 없습니다.</td></tr>
 <?php endif; ?>
</table>

<!-- 페이지 네비게이션 -->
<div class="pager">
 <?php if($page>1): ?>
   <a href="?lecture=<?=urlencode($keyword)?>&page=<?=$page-1?>">◀ 이전</a>
 <?php endif; ?>
 <span><?=$page?></span> / <?=$totalPages?>
 <?php if($page<$totalPages): ?>
   <a href="?lecture=<?=urlencode($keyword)?>&page=<?=$page+1?>">다음 ▶</a>
 <?php endif; ?>
</div>

<!-- 2. 학번별 통계 -->
<h2>2. 학번별 수강신청 통계</h2>
<h4>(최대 최근 7년)</h4>
<table>
 <tr><th>입학년도</th><th>수강신청 인원 수</th><th>평균 클릭 시간(s)</th></tr>
 <?php if($result2->num_rows==0): ?>
   <tr><td colspan="3">데이터가 없습니다.</td></tr>
 <?php else: ?>
   <?php while($r=$result2->fetch_assoc()): ?>
    <tr>
      <td><?=htmlspecialchars($r['입학년도'])?></td>
      <td><?=$r['인원수']?></td>
      <td>
      <?=abs($r['평균최초클릭'])?>
      <?=$r['평균최초클릭']<0
          ? '<span style="color:red;">(전)</span>'
          : '<span style="color:blue;">(후)</span>'?>
      </td>
    </tr>
   <?php endwhile; ?>
 <?php endif; ?>
</table>
<a href="/sugang/course/course_main.php">-> 수강신청 홈으로</a>
</body>
</html>