<?php
/* ───────────────────────── 초기 설정 ───────────────────────── */
session_start();
require_once $_SERVER['DOCUMENT_ROOT'].'/sugang/include/db.php';

/* Ajax: ‘수강신청 종료’ */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $in = json_decode(file_get_contents('php://input'), true) ?? [];
    if (($in['action'] ?? '') === 'finish') {
        unset($_SESSION['selected_courses']);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success'=>true]);
        exit;
    }
}
require_once $_SERVER['DOCUMENT_ROOT'].'/sugang/include/header.php';

/* 세션·유효성 검사 */
if (!isset($_SESSION['userID'])) {
    echo "<script>alert('로그인이 필요합니다.'); location.href='/sugang/user/login.php';</script>"; exit;
}
$sel = $_SESSION['selected_courses'] ?? [];
if (!$sel){
    echo "<script>alert('수강신청할 과목이 없습니다.'); location.href='course_select.php';</script>"; exit;
}
$userID = $_SESSION['userID'];

/* 선택 과목 상세 */
$ph = implode(',', array_fill(0,count($sel),'?'));
$stmt = $con->prepare("SELECT 강의코드,강의명,교수명,최대인원 FROM 강의 WHERE 강의코드 IN ($ph)");
$stmt->bind_param(str_repeat('s',count($sel)), ...$sel);
$stmt->execute(); $rs=$stmt->get_result();
$info=[]; while($r=$rs->fetch_assoc()) $info[$r['강의코드']]=$r;
$stmt->close();

/* 이미 신청된 과목 */
$stmt=$con->prepare("
  SELECT s.강의코드,c.강의명,c.교수명
  FROM 수강신청 s JOIN 강의 c ON s.강의코드=c.강의코드
  WHERE s.학번=?");
$stmt->bind_param("s",$userID);
$stmt->execute(); $applied=$stmt->get_result();
?>
<!DOCTYPE html><html lang="ko"><head>
<meta charset="utf-8"><title>수강신청 시뮬레이션</title></head><body>

<h1>수강신청 시뮬레이션</h1>
<p>수강신청 시작 시각: <strong>10:00:00.000</strong></p>
<p>현재 시뮬레이션 시간: <span id="current-time">--:--:--.---</span></p>

<h2>이미 신청된 과목</h2>
<table border="1" cellpadding="5" cellspacing="0">
<thead><tr><th>강의코드</th><th>강의명</th><th>교수명</th><th>수강취소</th></tr></thead>
<tbody id="appliedBody">
<?php while($r=$applied->fetch_assoc()): ?>
<tr id="applied-<?=htmlspecialchars($r['강의코드'])?>">
  <td><?=htmlspecialchars($r['강의코드'])?></td>
  <td><?=htmlspecialchars($r['강의명'])?></td>
  <td><?=htmlspecialchars($r['교수명'])?></td>
  <td><button class="cancel-btn" data-code="<?=htmlspecialchars($r['강의코드'])?>">수강취소</button></td>
</tr>
<?php endwhile; ?>
</tbody></table>
<?php $stmt->close(); ?>

<hr>

<h2>신청 예정 과목</h2>
<table border="1" cellpadding="5" cellspacing="0">
<thead>
  <tr><th>강의코드</th><th>강의명</th><th>교수명</th><th>최대인원</th><th>수강신청</th></tr>
</thead>
<tbody id="plannedBody">
<?php foreach($sel as $code):
      $i=$info[$code]??['강의명'=>'-','교수명'=>'-','최대인원'=>'-']; ?>
<tr id="row-<?=htmlspecialchars($code)?>">
  <td><?=htmlspecialchars($code)?></td>
  <td><?=htmlspecialchars($i['강의명'])?></td>
  <td><?=htmlspecialchars($i['교수명'])?></td>
  <td><?=htmlspecialchars($i['최대인원'])?></td>
  <td><button class="apply-btn" data-code="<?=htmlspecialchars($code)?>">수강신청</button></td>
</tr>
<?php endforeach; ?>
</tbody></table>

<br><button id="finish-btn" style="padding:8px 20px;">수강신청 종료</button>

<script>
/* ────────── 가상 시계 ────────── */
const simStart = new Date();            // 09:59:30.000부터 시작
simStart.setHours(9,59,30,0);
const realStart = new Date();

function simNow() {
  return new Date(simStart.getTime() + (Date.now() - realStart));
}

function updateClock(){
  const t = simNow();
  const p=n=>String(n).padStart(2,'0');
  document.getElementById('current-time').textContent =
    `${p(t.getHours())}:${p(t.getMinutes())}:${p(t.getSeconds())}.${String(t.getMilliseconds()).padStart(3,'0')}`;
}
setInterval(updateClock,1);

/* ────────── DOM 캐시 ────────── */
const plannedBody = document.getElementById('plannedBody');
const appliedBody = document.getElementById('appliedBody');

/* ────────── 수강취소 ────────── */
function cancelHandler(){
  const code=this.dataset.code;
  if(!confirm(`${code} 강의를 취소하시겠습니까?`)) return;
  fetch('cancel_course.php',{
    method:'POST',headers:{'Content-Type':'application/json'},
    body:JSON.stringify({code})
  })
  .then(r=>r.json())
  .then(d=> d.success
        ? (appliedBody.removeChild(document.getElementById('applied-'+code)),
           alert('취소가 완료되었습니다.'))
        : alert('취소 실패: '+d.msg))
  .catch(()=>alert('서버 오류로 취소 실패'));
}
document.querySelectorAll('.cancel-btn').forEach(b=>b.addEventListener('click',cancelHandler));

/* ────────── 수강신청 ────────── */
let nextPriority = 1;

document.querySelectorAll('.apply-btn').forEach(btn=>{
  btn.addEventListener('click', ()=>{
    const code = btn.dataset.code;
    const priority = nextPriority;

    /* ① 현재 가상 시각과 ② 가상 기준 10:00 계산 */
    const now   = simNow();
    const base  = new Date(now);
    base.setHours(10,0,0,0);
    const diff  = now - base;                 // ms (+/-)

    fetch('submit_course.php',{
      method :'POST',
      headers:{'Content-Type':'application/json'},
      body   :JSON.stringify({courses:[{code,priority,time_diff:diff}]})
    })
    .then(r=>r.json())
    .then(d=>{
      if(!d.success){ alert('신청 실패: '+d.msg); return; }

      /* 성공 시 행 이동 */
      const old = document.getElementById('row-'+code);
      plannedBody.removeChild(old);
      const tr = document.createElement('tr');
      tr.id='applied-'+code;
      tr.innerHTML=`
         <td>${code}</td>
         <td>${old.children[1].textContent}</td>
         <td>${old.children[2].textContent}</td>
         <td><button class="cancel-btn" data-code="${code}">수강취소</button></td>`;
      appliedBody.appendChild(tr);
      tr.querySelector('.cancel-btn').addEventListener('click',cancelHandler);
      nextPriority++;
    })
    .catch(()=>alert('서버 오류로 신청 실패'));
  });
});

/* ────────── 종료 버튼 ────────── */
document.getElementById('finish-btn').addEventListener('click',()=>{
  if(!confirm('수강신청을 종료하시겠습니까?')) return;
  fetch('',{
    method:'POST',headers:{'Content-Type':'application/json'},
    body:JSON.stringify({action:'finish'})
  })
  .then(r=>r.json())
  .then(d=> d.success
        ? (alert('수강신청이 종료되었습니다.'), location.href='/sugang/index.php')
        : alert('종료 처리 실패'))
  .catch(()=>alert('서버 오류로 종료 실패'));
});
</script>

<?php require_once $_SERVER['DOCUMENT_ROOT'].'/sugang/include/footer.php'; ?>
</body></html>