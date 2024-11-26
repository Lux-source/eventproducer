<?php

require_once('../../config.php');
require_once('lib.php');

require_login();

// Configurar la respuesta como JSON
header('Content-Type: application/json');

try {
    $messages = consume_events_from_kafka($USER->id);
    echo json_encode(['status' => 'success', 'messages' => $messages]);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', $e->getMessage()]);
}