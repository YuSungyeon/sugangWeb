<?php
session_start();

// 수강신청 종료
// finish 액션은 API 응답 용도이므로 HTML 출력되기 전에 처리
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $in = json_decode(file_get_contents('php://input'), true) ?? [];
    // action 파라미터가 'finish'인 경우 = 수강 신청 종료
    if (($in['action'] ?? '') === 'finish') {
    
      // 세션에서 선택한 과목 정보 삭제
      unset($_SESSION['selected_courses']);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success'=>true]);
        exit;
    }
}

// 데이터베이스 연결 설정 & 로그인 상태 확인 & 헤더
include $_SERVER['DOCUMENT_ROOT'].'/sugang/include/header.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/sugang/include/db.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/sugang/include/check_login.php';

// course_select.php 에서 수강신청 과목을 안 담고 넘어왔을때.
$sel = $_SESSION['selected_courses'] ?? [];
if (!$sel){
    echo "<script>alert('수강신청할 과목이 없습니다.'); location.href='course_select.php';</script>"; exit;
}

// 사용자 학번
$userID = $_SESSION['userID'];

// 선택 과목 상세 정보 조회
/* ──────────────────────────────── */
$ph = implode(',', array_fill(0,count($sel),'?'));
$stmt = $con->prepare("SELECT 강의코드,강의명,교수명,최대인원 FROM 강의 WHERE 강의코드 IN ($ph)");
$stmt->bind_param(str_repeat('s',count($sel)), ...$sel);
$stmt->execute(); $rs=$stmt->get_result();
$info=[]; while($r=$rs->fetch_assoc()) $info[$r['강의코드']]=$r;
$stmt->close();

// 기존 신청된 과목 조회
/* ──────────────────────────────── */
$stmt=$con->prepare("
  SELECT s.강의코드,c.강의명,c.교수명
  FROM 수강신청 s JOIN 강의 c ON s.강의코드=c.강의코드
  WHERE s.학번=?");
$stmt->bind_param("s",$userID);
$stmt->execute();
$applied=$stmt->get_result();
/* ──────────────────────────────── */
?>

<!-- 시뮬레이션 시작 (시간은 ms 단위 수정) -->
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
// 가상 시계
/* ──────────────────────────────── */
// 가상 시작 시간 지정 (09:59:30.00부터 시작)
const simStart = new Date();  
simStart.setHours(9,59,30,0);

// 현실 시간 차이 변화를 가상 시간에 적용하여 반환
const realStart = new Date();
function simNow(){return new Date(simStart.getTime()+(Date.now()-realStart));}

function updateClock(){
  const t=simNow();
  const p=n=>String(n).padStart(2,'0');
  // 출력 폼 설정 : "HH:MM:SS.mmm"
  document.getElementById('current-time').textContent=`${p(t.getHours())}:${p(t.getMinutes())}:${p(t.getSeconds())}.${String(t.getMilliseconds()).padStart(3,'0')}`;
}
// 1ms 단위로 업데이트 함수 반복 실행
setInterval(updateClock,1);
/* ──────────────────────────────── */

// 신청 예정 과목 영역과 이미 신청된 과목 영역을 변수에 저장해두기 (DOM 캐시)
const plannedBody = document.getElementById('plannedBody');  // 신청 예정 테이블 tbody
const appliedBody = document.getElementById('appliedBody');  // 이미 신청된 테이블 tbody

// 수강취소 ──────────
function cancelHandler(){
  const code=this.dataset.code; // 취소할 강의코드
  if(!confirm(`${code} 강의를 취소하시겠습니까?`)) return;

  // 서버에 취소 요청 전송 (강의코드 포함)
  fetch('cancel_course.php',{
    method:'POST',
    headers:{'Content-Type':'application/json'},
    body:JSON.stringify({code})
  })
   .then(r=>r.json())
   .then(d=>d.success?(appliedBody.removeChild(document.getElementById('applied-'+code)),alert('취소가 완료되었습니다.')):alert('취소 실패: '+d.msg))
   .catch(()=>alert('서버 오류로 취소 실패'));
}
document.querySelectorAll('.cancel-btn').forEach(btn=>btn.addEventListener('click',cancelHandler));

// 수강신청 ──────────
document.querySelectorAll('.apply-btn').forEach(btn=>{
  btn.addEventListener('click',()=>{
    const code=btn.dataset.code;
    const now=simNow(); // 시뮬레이션 시간
    const base=new Date(now);
    base.setHours(10,0,0,0); // 신청 기준 시간
    const diff=now-base; // 신청 시간차이 ms (+/-)

    // 서버에 수강신청 요청 전송 (강의코드, 시간차이 포함)
    fetch('submit_course.php',{
      method:'POST',
      headers:{'Content-Type':'application/json'},
      body:JSON.stringify({
        courses:[{code,time_diff:diff}]
      })
    })
      .then(r=>r.json())
      .then(d=>{
        if(!d.success){alert('신청 실패: '+d.msg);return;}

        // 신청 성공 시 화면에서 예정 과목 제거 + 신청 목록에 추가
        const old=document.getElementById('row-'+code);
        plannedBody.removeChild(old);
        const tr=document.createElement('tr');
        tr.id='applied-'+code;
        tr.innerHTML=`<td>${code}</td><td>${old.children[1].textContent}</td><td>${old.children[2].textContent}</td><td><button class=\"cancel-btn\" data-code=\"${code}\">수강취소</button></td>`;
        appliedBody.appendChild(tr);
        tr.querySelector('.cancel-btn').addEventListener('click',cancelHandler);
      })
      .catch(()=>alert('서버 오류로 신청 실패'));
  });
});

// 종료 버튼 ──────────
document.getElementById('finish-btn').addEventListener('click',()=>{
  if(!confirm('수강신청을 종료하시겠습니까?'))return;

  // 서버에 종료 요청 전송 (action: "finish")
  fetch('',{
    method:'POST',
    headers:{'Content-Type':'application/json'},
    body:JSON.stringify({action:'finish'})
  })
   .then(r=>r.json())
   .then(d=>d.success?(alert('수강신청이 종료되었습니다.'),location.href='/sugang/stats/stats_solo.php'):alert('종료 처리 실패'))
   .catch(()=>alert('서버 오류로 종료 실패'));
});
</script>

<?php require_once $_SERVER['DOCUMENT_ROOT'].'/sugang/include/footer.php'; ?>