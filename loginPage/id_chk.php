<?php
header('Content-Type: text/html; charset=utf-8');
include '../dbConnection.php';

$fuserid = $_GET['fuserid'] ?? '';
var_dump($fuserid);  // 넘어오는 아이디 확인

if (empty($fuserid)) {
    echo "<script>alert('아이디가 전달되지 않았습니다.');</script>";
    exit;
}

$sql = "SELECT COUNT(*) AS CNT FROM USERS WHERE user_id = :userid";
$stid = oci_parse($conn, $sql);
oci_bind_by_name($stid, ":userid", $fuserid);

$r = oci_execute($stid);
if (!$r) {
    $e = oci_error($stid);
    echo "<script>alert('쿼리 실행 오류: " . htmlentities($e['message']) . "');</script>";
    exit;
}

$row = oci_fetch_assoc($stid);
var_dump($row);  // 쿼리 결과 확인

if ($row && $row['CNT'] > 0) {
    echo "<script>alert('이미 존재하는 아이디입니다.');</script>";
} else {
    echo "<script>alert('사용 가능한 아이디입니다.');</script>";
}

oci_free_statement($stid);
oci_close($conn);
?>

