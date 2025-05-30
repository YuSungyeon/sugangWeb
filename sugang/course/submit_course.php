<?php
header('Content-Type: application/json; charset=utf-8');
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);   // ① 예외 모드

session_start();
require_once $_SERVER['DOCUMENT_ROOT'].'/sugang/include/db.php';

/* ───── 로그인 체크 ───── */
if (!isset($_SESSION['userID'])) {
    echo json_encode(['success'=>false,'msg'=>'로그인이 필요합니다.']); exit;
}
$userID = $_SESSION['userID'];

/* ───── 요청·파라미터 검증 ───── */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success'=>false,'msg'=>'잘못된 접근입니다.']); exit;
}
$data = json_decode(file_get_contents('php://input'), true);
$courses = $data['courses'] ?? [];
if (!$courses) {
    echo json_encode(['success'=>false,'msg'=>'수강 신청할 과목을 선택해주세요.']); exit;
}

/* ───── 트랜잭션 시작 ───── */
$con->begin_transaction();

try {
    $stmt = $con->prepare(
        "INSERT INTO 수강신청(학번,강의코드,시간차이,중요도)
         VALUES(?,?,?,?)"
    );                                                       // ② 준비는 1회

    foreach ($courses as $c) {
        $code = $c['code']        ?? '';
        $diff = $c['time_diff']   ?? null;
        $prio = $c['priority']    ?? 0;
        if ($code==='' || $diff===null) {
            throw new Exception('데이터 형식이 잘못되었습니다.');
        }
        /* 학번이 문자열이면 첫 타입을 s 로 */
        $stmt->bind_param("ssii", $userID, $code, $diff, $prio);
        $stmt->execute();
    }

    $con->commit();
    echo json_encode(['success'=>true,'msg'=>'수강 신청이 완료되었습니다.']);

} catch (mysqli_sql_exception $e) {                          // ③ DB 예외 처리
    $con->rollback();
    $msg = '알 수 없는 오류가 발생했습니다.';
    switch ($e->getCode()) {
        case 1062: $msg = '이미 신청한 강의입니다.'; break;    // PK 충돌
        case 1452: $msg = '존재하지 않는 강의 코드입니다.'; break; // FK 위반
        /* 필요 시 추가 매핑:
           case 12345: $msg = '수강 정원이 초과되었습니다.'; break;
        */
    }
    echo json_encode(['success'=>false,'msg'=>$msg]);
} catch (Exception $e) {                                     // ④ 기타 예외
    $con->rollback();
    echo json_encode(['success'=>false,'msg'=>$e->getMessage()]);
}

$con->close();