<?php
error_log(print_r($_POST, true));
session_start();
require_once $_SERVER['DOCUMENT_ROOT'].'/sugang/include/db.php';

// 로그인 확인
if (!isset($_SESSION['userID'])) {
    echo json_encode(['success' => false, 'message' => '로그인이 필요합니다.']);
    exit;
}

$userID = $_SESSION['userID'];

// POST 데이터 확인
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => '잘못된 접근입니다.']);
    exit;
}

$json = file_get_contents('php://input');
$data = json_decode($json, true);
$courses = $data['courses'] ?? null;

if (!$courses || !is_array($courses) || count($courses) === 0) {
    echo json_encode(['success' => false, 'message' => '수강 신청할 과목을 선택해주세요.']);
    exit;
}

// 현재 시간(기준시간) : 오전 10시 00분 00초 000 으로 고정
// 수강신청 버튼을 누른 시각은 클라이언트가 보낸 값 사용 (밀리초 차이)
// 클라이언트가 'time_diff'를 보내야 함

// DB 연결 및 준비
// 트랜잭션 시작
$con->begin_transaction();

try {
    // 수강신청 정보 삽입
    $insert_sql = "INSERT INTO 수강신청 (학번, 강의코드, 시간차이, 중요도) VALUES (?, ?, ?, ?)";
    $insert_stmt = $con->prepare($insert_sql);

    foreach ($courses as $course) {
        // course: ['code' => 강의코드, 'time_diff' => 시간차이, 'priority' => 중요도]
        $code = $course['code'] ?? null;
        $time_diff = isset($course['time_diff']) ? intval($course['time_diff']) : null;
        $priority = isset($course['priority']) ? intval($course['priority']) : 0;

        if (!$code || $time_diff === null) {
            throw new Exception('잘못된 데이터가 포함되어 있습니다.');
        }

        $insert_stmt->bind_param("isii", $userID, $code, $time_diff, $priority);
        $insert_stmt->execute();
    }

    $insert_stmt->close();

    // 커밋
    $con->commit();

    echo json_encode(['success' => true, 'message' => '수강 신청이 완료되었습니다.']);
} catch (Exception $e) {
    $con->rollback();
    echo json_encode(['success' => false, 'message' => '수강 신청 중 오류가 발생했습니다: ' . $e->getMessage()]);
}

$con->close();
