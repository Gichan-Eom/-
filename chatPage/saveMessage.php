<?php
session_start();
require_once '../dbConnection.php';

if (!isset($_SESSION['ses_nickname'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$nickname = $_SESSION['ses_nickname'];
$chat_room_id = $_POST['chat_room_id'] ?? null;
$message = $_POST['message'] ?? null;

if (!$chat_room_id || !$message) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid parameters']);
    exit;
}

// 사용자 ID 조회
$sql = "SELECT user_id FROM USERS WHERE nickname = :nickname";
$stid = oci_parse($conn, $sql);
oci_bind_by_name($stid, ":nickname", $nickname);
oci_execute($stid);
$row = oci_fetch_assoc($stid);
$user_id = $row['USER_ID'];

if (!$user_id) {
    http_response_code(400);
    echo json_encode(['error' => 'User not found']);
    exit;
}

// 메시지 저장
$insertSql = "INSERT INTO MESSAGE (message_id, message_sending_time, message_content, user_id, chat_room_id) 
              VALUES (message_seq.NEXTVAL, SYSDATE, :message_content, :user_id, :chat_room_id)";
$insertStid = oci_parse($conn, $insertSql);
oci_bind_by_name($insertStid, ':message_content', $message);
oci_bind_by_name($insertStid, ':user_id', $user_id);
oci_bind_by_name($insertStid, ':chat_room_id', $chat_room_id);

$result = oci_execute($insertStid, OCI_COMMIT_ON_SUCCESS);

if ($result) {
    echo json_encode(['success' => true]);
} else {
    $e = oci_error($insertStid);
    http_response_code(500);
    echo json_encode(['error' => $e['message']]);
}
?>
