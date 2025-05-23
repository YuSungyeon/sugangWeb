<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/sugang/admin/include/admin_check.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/sugang/include/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['강의코드'])) {
    $code = $_POST['강의코드'];

    $stmt = $con->prepare("DELETE FROM 강의 WHERE 강의코드 = ?");
    $stmt->bind_param("s", $code);
    $stmt->execute();

    $stmt->close();
}

header("Location: manage_lectures.php");
exit;
