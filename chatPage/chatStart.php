<?php
session_start();
include '../dbConnection.php';

header('Content-Type: application/json');

if (!isset($_SESSION['ses_nickname'])) {
    echo json_encode(['error' => '로그인이 필요합니다.']);
    exit;
}

$buyerNickname = $_SESSION['ses_nickname'];
$goodId = isset($_POST['good_id']) ? intval($_POST['good_id']) : 0;
if ($goodId <= 0) {
    echo json_encode(['error' => '잘못된 상품 ID입니다.']);
    exit;
}

$buyerId = '';
$sellerId = '';

// 구매자 user_id 가져오기
$sql = "SELECT user_id FROM users WHERE nickname = :nickname";
$stid = oci_parse($conn, $sql);
oci_bind_by_name($stid, ":nickname", $buyerNickname);
if (!oci_execute($stid)) {
    $e = oci_error($stid);
    echo json_encode(['error' => 'DB 쿼리 실패: ' . $e['message']]);
    exit;
}
$row = oci_fetch_assoc($stid);
oci_free_statement($stid);

if (!$row) {
    echo json_encode(['error' => '구매자 정보를 찾을 수 없습니다.']);
    exit;
}
$buyerId = $row['USER_ID'];

// 판매자 user_id 가져오기
$sql = "SELECT user_id FROM good WHERE good_id = :good_id";
$stid = oci_parse($conn, $sql);
oci_bind_by_name($stid, ":good_id", $goodId);
if (!oci_execute($stid)) {
    $e = oci_error($stid);
    echo json_encode(['error' => 'DB 쿼리 실패: ' . $e['message']]);
    exit;
}
$row = oci_fetch_assoc($stid);
oci_free_statement($stid);

if (!$row) {
    echo json_encode(['error' => '판매자 정보를 찾을 수 없습니다.']);
    exit;
}
$sellerId = $row['USER_ID'];

// 기존 채팅방 확인
$sql = "SELECT cr.chat_room_id
        FROM chatroom cr
        JOIN users_chatroom uc1 ON cr.chat_room_id = uc1.chat_room_id
        JOIN users_chatroom uc2 ON cr.chat_room_id = uc2.chat_room_id
        WHERE cr.good_id = :good_id
          AND uc1.user_id = :buyer_id
          AND uc2.user_id = :seller_id";

$stid = oci_parse($conn, $sql);
oci_bind_by_name($stid, ":good_id", $goodId);
oci_bind_by_name($stid, ":buyer_id", $buyerId);
oci_bind_by_name($stid, ":seller_id", $sellerId);
if (!oci_execute($stid)) {
    $e = oci_error($stid);
    echo json_encode(['error' => 'DB 쿼리 실패: ' . $e['message']]);
    exit;
}
$row = oci_fetch_assoc($stid);

if ($row) {
    // 기존 채팅방 있으면 ID 반환
    $chatRoomId = $row['CHAT_ROOM_ID'];
    oci_free_statement($stid);
} else {
    // 새 채팅방 생성
    $chatRoomId = "";
    $sql = "INSERT INTO chatroom (chat_room_id, chat_room_registration_date, good_id)
            VALUES (chatroom_seq.NEXTVAL, SYSDATE, :good_id)
            RETURNING chat_room_id INTO :chat_room_id";
    $stid = oci_parse($conn, $sql);
    oci_bind_by_name($stid, ":good_id", $goodId);
    oci_bind_by_name($stid, ":chat_room_id", $chatRoomId, 32);
    if (!oci_execute($stid, OCI_DEFAULT)) {
        $e = oci_error($stid);
        echo json_encode(['error' => '채팅방 생성 실패: ' . $e['message']]);
        exit;
    }
    oci_fetch($stid);
    oci_commit($conn);
    oci_free_statement($stid);

    // USERS_CHATROOM 삽입 - 구매자
    $sql = "INSERT INTO users_chatroom (user_id, chat_room_id) VALUES (:user_id, :chat_room_id)";
    $stid = oci_parse($conn, $sql);
    oci_bind_by_name($stid, ":user_id", $buyerId);
    oci_bind_by_name($stid, ":chat_room_id", $chatRoomId);
    oci_execute($stid);
    oci_free_statement($stid);

    // USERS_CHATROOM 삽입 - 판매자
    $stid = oci_parse($conn, $sql);
    oci_bind_by_name($stid, ":user_id", $sellerId);
    oci_bind_by_name($stid, ":chat_room_id", $chatRoomId);
    oci_execute($stid);
    oci_free_statement($stid);
}

oci_close($conn);

echo json_encode(['chat_room_id' => $chatRoomId]);
