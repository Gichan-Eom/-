<?php
session_start();

// 1) POST 방식인지 확인
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: login_form.php");
    exit;
}

// 2) POST 데이터 가져오기
$fuserid = trim($_POST['fuserid']  ?? '');
$fpasswd = trim($_POST['fpasswd'] ?? '');

// 3) 필수 입력 검증
if ($fuserid === '' || $fpasswd === '') {
    header("Location: login_form.php?error=1");
    exit;
}

// 4) Oracle DB 연결
include '../dbConnection.php';

// 5) DB에서 해당 아이디의 비밀번호 조회
$sql = "
    SELECT user_pw
      FROM USERS
     WHERE user_id = :userid
";
$stid = oci_parse($conn, $sql);
oci_bind_by_name($stid, ":userid", $fuserid);
oci_execute($stid);

// 6) 결과 가져오기
$row = oci_fetch_assoc($stid);
oci_free_statement($stid);

if (!$row) {
    // 아이디가 없거나 에러
    oci_close($conn);
    header("Location: login_form.php?error=1");
    exit;
}

$db_passwd = $row['USER_PW']; // Oracle은 컬럼명이 대문자로 넘어옴

// 7) 비밀번호 비교
if ($fpasswd !== $db_passwd) {
    oci_close($conn);
    header("Location: login_form.php?error=1");
    exit;
}

// 8) 로그인 성공 처리: 세션에 아이디와 닉네임 저장
$_SESSION['ses_userid'] = $fuserid;

// 닉네임 조회 후 세션에 저장
$sql2 = "SELECT nickname FROM USERS WHERE user_id = :userid";
$stid2 = oci_parse($conn, $sql2);
oci_bind_by_name($stid2, ":userid", $fuserid);
oci_execute($stid2);
$row2 = oci_fetch_assoc($stid2);
if ($row2) {
    $_SESSION['ses_nickname'] = $row2['NICKNAME'];
}
oci_free_statement($stid2);

oci_close($conn);

// 9) 로그인 성공 시 메인 페이지로 이동
header("Location: ../main.php");
exit;
?>

