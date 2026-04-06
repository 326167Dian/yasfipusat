<?php
$logFile = __DIR__ . '/debug_apply_index_lapstok.log';
file_put_contents($logFile, "START\n");

$mysqli = @new mysqli('localhost', 'root', '', 'yasfi');
if ($mysqli->connect_error) {
    file_put_contents($logFile, "CONNECT_ERR: " . $mysqli->connect_error . "\n", FILE_APPEND);
    exit(1);
}
file_put_contents($logFile, "CONNECTED\n", FILE_APPEND);

$sql = "ALTER TABLE trkasir_detail ADD INDEX idx_trkasir_detail_id_barang (id_barang)";
$result = $mysqli->query($sql);
if ($result) {
    file_put_contents($logFile, "OK: $sql\n", FILE_APPEND);
} else {
    file_put_contents($logFile, "ERR: " . $mysqli->errno . " " . $mysqli->error . "\n", FILE_APPEND);
}

$check = $mysqli->query("SHOW INDEX FROM trkasir_detail WHERE Key_name='idx_trkasir_detail_id_barang'");
if ($check && $check->num_rows > 0) {
    file_put_contents($logFile, "INDEX_PRESENT\n", FILE_APPEND);
} else {
    file_put_contents($logFile, "INDEX_ABSENT\n", FILE_APPEND);
}

file_put_contents($logFile, "END\n", FILE_APPEND);
