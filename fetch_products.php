<?php
// ── ① Warning/Notice가 JSON에 섞이지 않도록 끄기 ──
ini_set('display_errors', 0);
error_reporting(0);

// ── ② 세션 시작 & 테스트용 로그인 세션 (실제 로그인 전 임시) ──
session_start();
// $_SESSION['user_id'] = 'thswjdrl1212';  // ← 테스트 시 주석 해제

$userId = $_SESSION['user_id'] ?? '';
if (!$userId) {
    http_response_code(401);
    exit(json_encode(['error'=>'로그인 필요']));
}

// ── ③ Oracle DB 연결 ──
$conn = oci_connect(
  'dbuser202046',
  'ce1234',
  "(DESCRIPTION=
     (ADDRESS=(PROTOCOL=TCP)(HOST=earth.gwangju.ac.kr)(PORT=1521))
     (CONNECT_DATA=(SID=orcl))
   )"
);
if (!$conn) {
    http_response_code(500);
    exit(json_encode(['error'=>'DB 연결 실패']));
}

// ── ④ SQL 준비 (새로운 플레이스홀더 :P_UID 사용) ──
$sql = "
  SELECT g.good_id,
         g.good_name,
         g.good_price,
         c.category_name,
         g.good_image
    FROM GOOD g
    LEFT JOIN CATEGORY c ON c.good_id = g.good_id
   WHERE g.user_id = :P_UID
ORDER BY g.good_registration_date DESC
";
$stid = oci_parse($conn, $sql);
if (!$stid) {
    http_response_code(500);
    exit(json_encode(['error'=>'SQL 파싱 실패']));
}

// ── ⑤ 바인드 (콜론 없이, 플레이스홀더 이름과 정확히 일치) ──
oci_bind_by_name($stid, 'P_UID', $userId);

// ── ⑥ 쿼리 실행 ──
if (!oci_execute($stid)) {
    $e = oci_error($stid);
    http_response_code(500);
    exit(json_encode(['error'=>'쿼리 실행 실패: '.$e['message']]));
}

// ── ⑦ 결과 수집 & JSON 변환 ──
$products = [];
while ($row = oci_fetch_assoc($stid)) {
    $lob  = $row['GOOD_IMAGE'];
    $raw  = $lob ? $lob->load() : '';
    $base = $raw ? base64_encode($raw) : '';
    $mime = 'image/jpeg';
    $products[] = [
        'good_id'       => $row['GOOD_ID'],
        'good_name'     => $row['GOOD_NAME'],
        'good_price'    => $row['GOOD_PRICE'],
        'category_name' => $row['CATEGORY_NAME'],
        'image'         => $base ? "data:{$mime};base64,{$base}" : null
    ];
}

// ── ⑧ 깨끗한 JSON 출력 ──
header('Content-Type: application/json; charset=utf-8');
echo json_encode($products, JSON_UNESCAPED_UNICODE);

oci_free_statement($stid);
oci_close($conn);
exit;
