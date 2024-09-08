<?php
session_start();

if (isset($_SESSION['kafka_producer'])) {
    unset($_SESSION['kafka_producer']);
    echo json_encode(['status' => 'disconnected']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'No active connection found']);
    http_response_code(400);
}

