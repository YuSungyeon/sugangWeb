<?php
/* ────────── 초기 설정 ────────── */
header('Content-Type: application/json; charset=utf-8');
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);  // 예외 모드

session_start();
require_once $_SERVER['DOCUMENT_ROOT'].'/sugang/include/db.php';

/* ───── 1. 로그인 체크 ───── */
if (!isset($_SESSION['userID'])) {
    echo json_encode(['success'=>false,'msg'=>'로그인이 필요합니다.']); exit;
}
$userID = $_SESSION['userID'];

/* ───── 2. 요청·파라미터 검증 ───── */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success'=>false,'msg'=>'잘못된 접근입니다.']); exit;
}
$data    = json_decode(file_get_contents('php://input'), true);
$courses = $data['courses'] ?? [];
if (!$courses) {
    echo json_encode(['success'=>false,'msg'=>'수강 신청할 과목을 선택해주세요.']); exit;
}

/* ───── 3. 트랜잭션 시작 ───── */
$con->begin_transaction();

try {
    /* 3-1 ─ 과목 INSERT (priority 컬럼 제외) */
    $ins = $con->prepare(
        "INSERT INTO 수강신청(학번, 강의코드, 시간차이) VALUES (?,?,?)"
    );

    foreach ($courses as $c) {
        $code = $c['code']      ?? '';
        $diff = $c['time_diff'] ?? null;              // ms

        if ($code==='' || !is_numeric($diff))
            throw new Exception('잘못된 데이터가 포함되어 있습니다.');

        /* 학번이 문자열이면 첫 파라미터 타입은 s */
        $ins->bind_param('ssd', $userID, $code, $diff);
        $ins->execute();                              // 중복(PK) → 1062
    }
    $ins->close();

    /* 중요도 재정렬 */
    $con->query("SET @rank := 0");
    $upd = $con->prepare("
      UPDATE 수강신청 s
      JOIN (
          SELECT 학번, 강의코드, (@rank := @rank + 1) AS new_rank
            FROM 수강신청
           WHERE 학번 = ?
           ORDER BY 시간차이 ASC
      ) r ON s.학번 = r.학번 AND s.강의코드 = r.강의코드
      SET s.중요도 = r.new_rank
    ");
    $upd->bind_param('i', $userID);  // 학번이 INT이므로 'i'
    $upd->execute();
    $upd->close();

    /* 3-3 ─ 커밋 */
    $con->commit();
    echo json_encode(['success'=>true,'msg'=>'수강 신청이 완료되었습니다.']);

} catch (mysqli_sql_exception $e) {        // DB 예외
    $con->rollback();
    switch ($e->getCode()) {
        case 1062: $msg = '이미 신청한 강의입니다.';          break; // PK 충돌
        case 1452: $msg = '존재하지 않는 강의 코드입니다.';    break; // FK 위반
        default :  $msg = '데이터베이스 오류가 발생했습니다.'; break;
    }
    echo json_encode(['success'=>false,'msg'=>$msg]);

} catch (Exception $e) {                   // 일반 예외
    $con->rollback();
    echo json_encode(['success'=>false,'msg'=>$e->getMessage()]);
}

$con->close();
?>