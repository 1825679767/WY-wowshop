<?php
session_start();
session_destroy();
header('Content-Type: application/json; charset=utf-8');

// 假设注销成功
echo json_encode(['success' => true]);
?>