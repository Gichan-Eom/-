<?php
// deburg.php
// 목적: 하드코딩된 user_id('thswjdrl1212')의 nickname을 raw 바이트로 출력하고,
//       그 뒤 이어서 16진수(hex) 덤프를 출력합니다.

// 1) 브라우저가 텍스트 모드로 출력하도록 UTF-8 헤더
header('Content-Type: text/plain; charset=UTF-8');

// 2) 디버깅용으로 직접 조회할 user_id를 하드코딩합니다.
$userId = 'thswjdrl1212';

// 3) Oracle DB 연결 (클라이언트 문자셋 AL32UTF8 지정)
$conn = oci_connect(
    "dbuser202046",
    "ce1234",
    "(DESCRIPTION=
        (ADDRESS=(PROTOCOL=TCP)(HOST=earth.gwangju.ac.kr)(PORT=1521))
        (CONNECT_DATA=(SID=orcl))
     )",
    "AL32UTF8"
);
if (!$conn) {
    echo "DB 연결 실패: " . oci_error()['message'] . "\n";
    exit;
}

// 4) 바인드 변수 이름을 :user_id로 통일
$sql  = "SELECT nickname FROM USERS WHERE user_id = :user_id";
$stid = oci_parse($conn, $sql);
if (!$stid) {
    echo "oci_parse 실패: " . oci_error($conn)['message'] . "\n";
    oci_close($conn);
    exit;
}

// 5) :user_id 바인드
oci_bind_by_name($stid, ":user_id", $userId);

// 6) 쿼리 실행
$r = oci_execute($stid);
if (!$r) {
    echo "oci_execute 실패: " . oci_error($stid)['message'] . "\n";
    oci_free_statement($stid);
    oci_close($conn);
    exit;
}

// 7) 결과 페치
$row = oci_fetch_assoc($stid);
oci_free_statement($stid);
oci_close($conn);

if (!$row || !isset($row['NICKNAME'])) {
    echo "해당 user_id('{$userId}')의 닉네임을 가져올 수 없습니다.\n";
    exit;
}

// 8) rawNick 원본 바이트 출력
$rawNick = $row['NICKNAME'];
echo "---- rawNick (원본 바이트 출력 시도) ----\n";
echo $rawNick . "\n\n";

// 9) rawNick 16진수(hex) 덤프 출력
echo "---- rawNick (hex dump) ----\n";
echo bin2hex($rawNick) . "\n";
exit;
?>
