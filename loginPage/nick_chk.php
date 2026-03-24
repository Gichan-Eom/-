<?php
include '../dbConnection.php';

$nickname = $_GET['nickname'] ?? '';

if (empty($nickname)) {
    echo "닉네임이 전달되지 않았습니다.";
    exit;
}

$sql = "SELECT COUNT(*) AS CNT FROM USERS WHERE nickname = :nickname";

$stid = oci_parse($conn, $sql);
oci_bind_by_name($stid, ':nickname', $nickname);
oci_execute($stid);

$row = oci_fetch_assoc($stid);

if ($row['CNT'] > 0) {
    echo "<script>alert('이미 존재하는 닉네임입니다.');</script>";
} else {
    echo "<script>alert('사용 가능한 닉네임입니다.');</script>";
}

oci_free_statement($stid);
oci_close($conn);
?>
