<?php
error_reporting(E_ALL);
ini_set('display_errors','1');
session_start();

// 로그인 체크
$userId = $_SESSION['ses_userid'] ?? '';
if (!$userId) {
    die('로그인이 필요합니다.');
}

// DB 연결
$conn = oci_connect("dbuser202046","ce1234","(DESCRIPTION=...)", "AL32UTF8");
if (!$conn) {
    $e = oci_error();
    die("DB 연결 실패: ".htmlspecialchars($e['message'],ENT_QUOTES,'UTF-8'));
}

if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['good_id'])) {
    $goodId = (int)$_POST['good_id'];

    // 1) CATEGORY 먼저 삭제 (FK 제약 대비)
    $sql1 = "DELETE FROM CATEGORY WHERE good_id = :id";
    $stid1 = oci_parse($conn, $sql1);
    oci_bind_by_name($stid1, ":id", $goodId);
    oci_execute($stid1, OCI_NO_AUTO_COMMIT);

    // 2) GOOD 테이블에서 삭제
    $sql2 = "DELETE FROM GOOD WHERE good_id = :id AND user_id = :uid";
    $stid2 = oci_parse($conn, $sql2);
    oci_bind_by_name($stid2, ":id",  $goodId);
    oci_bind_by_name($stid2, ":uid", $userId);
    oci_execute($stid2, OCI_NO_AUTO_COMMIT);

    // 커밋
    oci_commit($conn);

    // 리소스 해제
    oci_free_statement($stid1);
    oci_free_statement($stid2);
    oci_close($conn);

    // 다시 프로필 페이지로 리다이렉트
    header("Location: ProfilePage.php");
    exit;
} else {
    die('잘못된 접근입니다.');
}
