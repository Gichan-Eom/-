<?php
$conn = oci_connect('dbuser202046', 'ce1234', 'earth.gwangju.ac.kr/orcl', 'AL32UTF8');

if (!$conn) {
    $e = oci_error();
    echo "연결 실패: " . $e['message'];
    exit;
}
?>