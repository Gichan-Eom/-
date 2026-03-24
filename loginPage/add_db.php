<?php
// Oracle DB 연결
include '../dbConnection.php';

// POST 요청인지 확인
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo "<script>alert('잘못된 접근입니다.'); history.back();</script>";
    exit;
}

// 입력값 받기
$fuserid = trim($_POST['fuserid'] ?? '');
$fnickname = trim($_POST['nickname'] ?? '');
$fpasswd = $_POST['fpasswd'] ?? '';
$fpasswd_re = $_POST['fpasswd_re'] ?? '';

// 전화번호 3개 합치기
$phone1 = trim($_POST['phone1'] ?? '');
$phone2 = trim($_POST['phone2'] ?? '');
$phone3 = trim($_POST['phone3'] ?? '');

$phone_num = '';
if ($phone1 !== '' && $phone2 !== '' && $phone3 !== '') {
    $phone_num = $phone1 . '-' . $phone2 . '-' . $phone3;
}

// 필수 입력 체크
if ($fuserid === "" || $fnickname === "" || $fpasswd === "" || $fpasswd_re === "" || $phone_num === "") {
    echo "<script>alert('필수 입력사항을 모두 입력하세요.'); history.back();</script>";
    exit;
}

// 비밀번호 일치 확인
if ($fpasswd !== $fpasswd_re) {
    echo "<script>alert('비밀번호가 일치하지 않습니다.'); history.back();</script>";
    exit;
}

// 닉네임 중복 확인
$sql_check_nick = "SELECT COUNT(*) AS CNT FROM USERS WHERE nickname = :nickname";
$stid = oci_parse($conn, $sql_check_nick);
oci_bind_by_name($stid, ":nickname", $fnickname);
oci_execute($stid);
$row = oci_fetch_assoc($stid);
if ($row && $row['CNT'] > 0) {
    echo "<script>alert('이미 사용 중인 닉네임입니다.'); history.back();</script>";
    oci_free_statement($stid);
    oci_close($conn);
    exit;
}
oci_free_statement($stid);

// 아이디 중복 확인
$sql_check_id = "SELECT COUNT(*) AS CNT FROM USERS WHERE user_id = :userid";
$stid = oci_parse($conn, $sql_check_id);
oci_bind_by_name($stid, ":userid", $fuserid);
oci_execute($stid);
$row = oci_fetch_assoc($stid);
if ($row && $row['CNT'] > 0) {
    echo "<script>alert('이미 사용 중인 아이디입니다.'); history.back();</script>";
    oci_free_statement($stid);
    oci_close($conn);
    exit;
}
oci_free_statement($stid);

// INSERT 쿼리 (user_id, user_pw, nickname, phone_num)
$sql_insert = "INSERT INTO USERS (user_id, user_pw, nickname, phone_num) 
               VALUES (:userid, :userpw, :nickname, :phone)";
$stid = oci_parse($conn, $sql_insert);
oci_bind_by_name($stid, ":userid", $fuserid);
oci_bind_by_name($stid, ":userpw", $fpasswd);
oci_bind_by_name($stid, ":nickname", $fnickname);
oci_bind_by_name($stid, ":phone", $phone_num);

$r = oci_execute($stid, OCI_COMMIT_ON_SUCCESS);

if ($r) {
    echo "<script>alert('회원가입이 완료되었습니다.'); location.replace('login_form.php');</script>";
} else {
    $e = oci_error($stid);
    $error_msg = $e ? $e['message'] : "알 수 없는 오류";
    echo "<script>alert('회원가입 중 오류가 발생했습니다.\\n$error_msg'); history.back();</script>";
}

oci_free_statement($stid);
oci_close($conn);
?>
