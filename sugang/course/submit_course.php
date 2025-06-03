<?php
// 초기 설정
header('Content-Type: application/json; charset=utf-8');  // JSON 응답 설정
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT); // MySQLi 예외 모드 활성화
// MySQL 쿼리 오류가 발생하면 즉시 예외(Exception)를 던지게 함

session_start();
require_once $_SERVER['DOCUMENT_ROOT'].'/sugang/include/db.php'; // DB 연결

// 사용자 로그인 여부 확인
if (!isset($_SESSION['userID'])) {
    echo json_encode(['success' => false, 'msg' => '로그인이 필요합니다.']);
    exit;
}
$userID = $_SESSION['userID'];

// 요청 및 파라미터 검증 ─────
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'msg' => '잘못된 접근입니다.']);
    exit;
}
$data    = json_decode(file_get_contents('php://input'), true);
$courses = $data['courses'] ?? [];

if (!$courses) {
    echo json_encode(['success' => false, 'msg' => '수강 신청할 과목을 선택해주세요.']);
    exit;
}

// 트랜잭션 시작 ───── 문제 없이 진행되면 커밋 진행, 아니면 롤백
$con->begin_transaction();

try {
    // 수강신청 INSERT ─────
    $ins = $con->prepare(
        "INSERT INTO 수강신청(학번, 강의코드, 시간차이) VALUES (?, ?, ?)"
    );

    foreach ($courses as $c) {
        $code = $c['code']      ?? '';
        $diff = $c['time_diff'] ?? null; // 단위: ms

        if ($code === '' || !is_numeric($diff))
            throw new Exception('잘못된 데이터가 포함되어 있습니다.');

        $ins->bind_param('ssd', $userID, $code, $diff); // 학번(s), 강의코드(s), 시간차이(d)
        $ins->execute(); // 중복 신청 시 PK 충돌 발생 (에러 코드: 1062)
    }
    $ins->close();

    //중요도(우선순위) 재정렬 ─────
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
    $upd->bind_param('i', $userID);
    $upd->execute();
    $upd->close();

    // 수강 신청에 문제가 없었으므로 커밋 진행
    $con->commit();
    echo json_encode(['success' => true, 'msg' => '수강 신청이 완료되었습니다.']);

} catch (mysqli_sql_exception $e) {
    // DB 관련 예외 처리
    $con->rollback(); // 문제가 발생했으므로 롤백 (트랜잭션)

    // 문제가 발생한 이유를 문자 정보로 제공
    switch ($e->getCode()) {
        case 1062: $msg = '이미 신청한 강의입니다.'; break; // PK 중복
        case 1452: $msg = '존재하지 않는 강의 코드입니다.'; break; // FK 위반
        default:   $msg = '데이터베이스 오류가 발생했습니다.'; break;
    }
    echo json_encode(['success' => false, 'msg' => $msg]);

} catch (Exception $e) {
    // 일반 예외 처리
    $con->rollback(); // 문제가 발생했으므로 롤백 (트랜잭션)
    echo json_encode(['success' => false, 'msg' => $e->getMessage()]);
}

$con->close();
?>