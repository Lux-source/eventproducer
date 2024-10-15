<?php

require_once('../../config.php');
require_once('lib.php');

require_login();

// Get course id
$course_id = required_param('courseid', PARAM_INT);

// Configurar la respuesta como JSON
header('Content-Type: application/json');

try {
    $messages = consume_events_from_kafka($USER->id, $course_id);
    echo json_encode(['status' => 'success', 'messages' => $messages]);
} catch (Exception $e) {
    error_log('Error in receive.php: ' . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}