<?php
/* ─────────── 디버그 출력 ─────────── */
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
/* ─────────────────────────────────── */

session_start();

// 데이터베이스 연결 설정 & 로그인 상태 확인
require_once $_SERVER['DOCUMENT_ROOT'].'/sugang/include/db.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/sugang/include/check_login.php';

// 사용자 학번
$userID = $_SESSION['userID'];

// 프로필 확인 & 첫 클릭 시간
$sqlProf = "
 SELECT u.이름,
        COUNT(*) AS total_cnt,
        MIN(CASE WHEN 중요도=1 THEN 시간차이 END)/1000 AS first_click
   FROM 수강신청 s
   JOIN 사용자 u ON s.학번 = u.학번
  WHERE s.학번 = ?";
$stmt = $con->prepare($sqlProf);
$stmt->bind_param('s', $userID);
$stmt->execute();
$profile = $stmt->get_result()->fetch_assoc();
$stmt->close();

// 수강신청을 안한 사용자 진행 차단
if (!$profile || $profile['total_cnt'] == 0) {
    echo "<script>alert('수강 신청 정보가 없습니다.'); history.back();</script>";
    exit;
}

// 과목별 내 신청 정보 + 강의 최대인원
/* ─────────────────────────────────── */
// 시간차이(ms)를 초 단위로 변환
$sqlDtl = "
 SELECT sc.강의코드, k.강의명, k.교수명, k.최대인원,
        sc.중요도,
        ROUND(sc.시간차이/1000,5) AS click_diff
   FROM 수강신청 sc
   JOIN 강의 k ON sc.강의코드 = k.강의코드
  WHERE sc.학번 = ?
 ORDER BY sc.중요도";
$stmt = $con->prepare($sqlDtl);
$stmt->bind_param('s', $userID);
$stmt->execute();
$details = $stmt->get_result();
$stmt->close();
/* ─────────────────────────────────── */

// 모든 강의 정보 (신청인원·평균우선순위·공동순위)
/* ─────────────────────────────────── */
$meta = [];
$res = $con->query("
  SELECT k.강의코드,
         COUNT(*) AS cnt,
         ROUND(AVG(s.중요도),1) AS avg_pri 
    FROM 수강신청 s
    JOIN 강의 k ON s.강의코드=k.강의코드
GROUP BY k.강의코드
ORDER BY cnt DESC, k.강의코드");
$rank = 0; $prevCnt = null;
foreach($res as $r){
    if($prevCnt !== $r['cnt']){ $rank++; $prevCnt = $r['cnt']; }
    $meta[$r['강의코드']] = ['cnt'=>$r['cnt'],'avg'=>$r['avg_pri'],'rank'=>$rank];
}
/* ─────────────────────────────────── */

// 과목별 내 속도 등수
/* ─────────────────────────────────── */
$myRanks = [];  // 각 강의코드별 신청 순위 저장용 배열

foreach ($details as $row) {
    $code = $row['강의코드']; // 현재 강의코드
    $myMs = $row['click_diff'] * 1000; // 클릭 시간차(sec)를 ms로 변환

    // 사용자보다 먼저 신청한 사람 수 파악 (사용자의 신청 순위 계산)
    $stmtR = $con->prepare("
        SELECT COUNT(*) + 1
          FROM 수강신청
         WHERE 강의코드 = ? AND 시간차이 < ?
    ");
    $stmtR->bind_param('sd', $code, $myMs);   // 강의코드, 사용자 시간차(ms) 바인딩
    $stmtR->execute();

    // 결과에서 순위 값 추출하여 순위가 없으면 '-' 처리
    $myRanks[$code] = $stmtR->get_result()->fetch_row()[0] ?? '-';

    $stmtR->close();  // 자원 해제
}

//포인터를 다시 처음으로 초기화
$details->data_seek(0);
/* ─────────────────────────────────── */

// '등급' 테이블로 첫 클릭 등급 구하기
/* ─────────────────────────────────── */
// 사용자의 첫 클릭 시간(first_click)을 초 단위로 가져와서 절댓값 처리 후 ms(밀리초) 단위로 변환
// 예: -1.234초 → 1234ms (부호에 관계없이 시간 차이 기준으로 등급 판단)
$absFirst = abs($profile['first_click'])*1000;

// 등급 테이블에서 최소시간 ~ 최대시간 구간에 해당하는 등급을 찾음
$stmt = $con->prepare("SELECT 등급 FROM 등급 WHERE ? BETWEEN 최소시간 AND 최대시간 LIMIT 1");
$stmt->bind_param('d', $absFirst);
$stmt->execute();

// 결과가 단일 행·열이므로 fetch_row()[0]으로 등급 문자열 바로 추출함
$grade = $stmt->get_result()->fetch_row()[0];
$stmt->close();

// 전체 평균 첫 클릭 (모든 학생)
/* ─────────────────────────────────── */
$sql = "
  SELECT ROUND(AVG(abs_first),5) AS avg_abs
    FROM (
          SELECT ABS(MIN(CASE WHEN 중요도=1 THEN 시간차이 END)/1000) AS abs_first
            FROM 수강신청
        GROUP BY 학번) tmp";

$result = $con->query($sql);
$avgFirst = $result->fetch_assoc()['avg_abs'];
/* ─────────────────────────────────── */

// 과목별 평가・속도 메시지 생성
/* ─────────────────────────────────── */
$courseMsgs = [];
while($row = $details->fetch_assoc()){
    $code     = $row['강의코드'];
    $title    = $row['강의명'].'('.$row['교수명'].')';
    $myRank   = $myRanks[$code];
    $maxCap   = $row['최대인원'];
    $ratio    = $myRank / $maxCap;

    // 안전도 문자열 (숫자는 임의로 부여함) 
    if ($ratio < 0.6)            $safe = '안전';
    elseif ($ratio < 0.9)        $safe = '평범';
    elseif ($ratio <= 1.0)       $safe = '위험';
    else                         $safe = '매우 위험';

    $courseMsgs[] = "<strong>'".htmlspecialchars($title)."'</strong> 과목 신청 속도는 <strong>{$safe}</strong>합니다.";

    // 테이블 표시 준비 : 스택에 넣어두기
    $row['my_rank'] = $myRank;
    $row['safe']  = $safe;
    $rows[] = $row;
}
/* ─────────────────────────────────── */
?>
<!DOCTYPE html>
<html lang="ko"><head>
<meta charset="UTF-8"><title>개인 수강신청 보고서</title>
<style>
 table{border-collapse:collapse;width:88%;margin-bottom:40px}
 th,td{border:1px solid #ccc;padding:8px;text-align:center}
 h2{margin-top:45px}
</style>
</head><body>
<h1>개인 수강신청 보고서</h1>

<p><strong>이름·학번</strong> : <?=htmlspecialchars($profile['이름'])?>
   (<?=htmlspecialchars($userID)?>)</p>
<p><strong>총 신청 과목</strong> : <?=$profile['total_cnt']?> 개</p>
<p><strong>첫 클릭 시간</strong> :
   <?php $fc = $profile['first_click']; echo $fc<0 ? abs($fc).'초 전' : '+'.$fc.'초 후'; ?>
</p>

<!-- 세부 표 -->
<h2>신청 세부 내역</h2>
<p><strong>* 우선순위</strong>란, <strong>수강신청을 완료한 시간 순서</strong>이며, 사용자가 어떤 과목을 중요하게 생각하는지 판단하는 기준입니다.</p>
<table>
 <tr>
  <th>우선<br>순위</th><th style="width:24%">강의명</th><th>교수명</th>
  <th>클릭<br>시간차(s)</th><th>신청인원<br>/최대인원</th>
  <th>내 속도 순위</th><th>평균<br>우선순위</th><th>강의<br>인기순위</th>
 </tr>
<?php foreach($rows as $r):
        $code=$r['강의코드'];
        $m=$meta[$code]; ?>
 <tr>
   <td><?=$r['중요도']?></td>
   <td><?=htmlspecialchars($r['강의명'])?></td>
   <td><?=htmlspecialchars($r['교수명'])?></td>
   <td>
     <?=abs($r['click_diff'])?>
     <?=$r['click_diff']<0?'<span style="color:red;">(전)</span>'
                         :'<span style="color:blue;">(후)</span>'?>
   </td>
   <td><?=$m['cnt']?>명 / <?=$r['최대인원']?>명</td>
   <td><?=$r['my_rank']?>등</td>
   <td><?=$m['avg']?></td>
   <td><?=$m['rank']?> 위</td>
 </tr>
<?php endforeach;
if(empty($rows)):?>
 <tr><td colspan="8">신청 내역이 없습니다.</td></tr>
<?php endif;?>
</table>

<!-- 최종 평가 메시지 -->
<h3>최종 평가</h3>
<p><strong><?=$profile['이름']?> (<?=$userID?>)</strong>님은 신청속도 등급은 <strong><?=$grade?></strong>입니다.
<br>(첫 강의 수강신청 완료 시간이 기준 시간에 가까울수록 높은 등급이 부여됩니다.) </p>
<!-- 과목별 메시지 -->
<p><strong>각 과목별 평가는</strong></p>
<ul>
<?php foreach($courseMsgs as $msg): ?>
 <li><?=$msg?></li>
<?php endforeach; ?>
</ul>

<?php if($profile['first_click'] < 0): ?>
<p style="color:red;">신청 시간보다 너무 빠르게 신청하면 위험 할 수 있습니다.</p>
<?php endif; ?>

<!-- 첫 클릭 비교 -->
<?php
  $speedMsg = ($profile['first_click'] < $avgFirst) ? '빠릅니다.' : '느립니다.';
?>
<p>첫 클릭 속도는 남들보다 <strong><?=$speedMsg?></strong></p>

<br>
<a href="/sugang">-> 홈으로</a>

</body></html>