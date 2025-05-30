<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/sugang/admin/include/admin_check.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/sugang/include/db.php';

$min = isset($_POST['min']) ? intval($_POST['min']) : null;
$max = isset($_POST['max']) ? intval($_POST['max']) : null;

if ($min === null || $max === null || $min > $max){
    echo "<script>alert('유효한 범위를 입력하세요.');history.back();</script>"; exit;
}

$stmt = $con->prepare("
    DELETE FROM 수강신청
      WHERE 시간차이 < ? OR 시간차이 > ?
");
$stmt->bind_param('ii', $min, $max);
$stmt->execute();
$cnt = $stmt->affected_rows;
$stmt->close();
$con->close();

echo "<script>alert('{$cnt}건이 삭제되었습니다.');location.href='manage_courses.php';</script>";
?>