<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once $_SERVER['DOCUMENT_ROOT'].'/sugang/include/db.php';

// 강의별 통계
$sql1 = "
SELECT 
  강의.강의명, 
  COUNT(*) AS 신청인원, 
  ROUND(AVG(중요도), 1) AS 평균우선순위,
  ROUND(AVG(시간차이)/1000, 5) AS 평균클릭차이
FROM 수강신청
JOIN 강의 ON 수강신청.강의코드 = 강의.강의코드
GROUP BY 수강신청.강의코드
ORDER BY 신청인원 DESC";

$result1 = $con->query($sql1);

// 학번별 통계 (최근 7년)
$current_year = date('Y');
$start_year = $current_year - 6;

$sql2 = "
SELECT
    SUBSTRING(학번, 1, 4) AS 입학년도,
    COUNT(DISTINCT 학번) AS 인원수,
    ROUND(AVG(시간차이) / 1000, 5) AS 평균최초클릭
FROM 수강신청
WHERE 중요도 = 1
    AND LENGTH(학번) >= 4
    AND SUBSTRING(학번, 1, 4) BETWEEN ? AND ?
GROUP BY 입학년도
ORDER BY 입학년도 DESC;
";

$stmt = $con->prepare($sql2);
$stmt->bind_param("ii", $start_year, $current_year);
$stmt->execute();
$result2 = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>전체 수강신청 통계</title>
  <style>
    table { border-collapse: collapse; width: 80%; margin-bottom: 40px; }
    th, td { border: 1px solid #ccc; padding: 8px; text-align: center; }
    h2 { margin-top: 50px; }
  </style>
</head>
<body>
  <h1>📊 전체 수강신청 통계</h1>

  <h2>1. 강의별 수강신청 통계</h2>
  <table>
    <tr>
        <th>강의명</th>
        <th>신청 인원</th>
        <th>평균 우선순위</th>
        <th>평균 클릭 시간 차이(s)</th>
    </tr>
    <?php while ($row = $result1->fetch_assoc()): ?>
    <tr>
        <td><?= htmlspecialchars($row['강의명']) ?></td>
        <td><?= $row['신청인원'] ?></td>
        <td><?= $row['평균우선순위'] ?></td>
        <td>
        <?= abs($row['평균클릭차이']) ?>
        <?php if ($row['평균클릭차이'] < 0): ?>
            <span style="color: red;">(전)</span>
        <?php else: ?>
            <span style="color: blue;">(후)</span>
        <?php endif; ?>
        </td>
    </tr>
    <?php endwhile; ?>
  </table>

  <h2>2. 학번별 수강신청 통계 (최근 7년)</h2>
  <table>
    <tr>
      <th>입학년도</th>
      <th>수강신청 인원 수</th>
      <th>평균 클릭 시간 차이 (s)</th>
    </tr>
    <?php if ($result2->num_rows === 0): ?>
      <tr><td colspan="3">데이터가 없습니다.</td></tr>
    <?php else: ?>
      <?php while ($row = $result2->fetch_assoc()): ?>
        <tr>
          <td><?= htmlspecialchars($row['입학년도']) ?></td>
          <td><?= $row['인원수'] ?></td>
          <td>
            <?= 
              ($row['평균최초클릭'] < 0 
                ? abs($row['평균최초클릭']) . ' (전)' 
                : $row['평균최초클릭'] . ' (후)') 
            ?>
          </td>
        </tr>
      <?php endwhile; ?>
    <?php endif; ?>
    </table>
</body>
</html>
