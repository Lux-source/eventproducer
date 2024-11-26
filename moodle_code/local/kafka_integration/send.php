<?php

require_once('../../config.php');
require_once('lib.php');

require_login();

// Respuesta como JSON
header('Content-Type: application/json');

// Leer los datos enviados por AJAX
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (isset($data['content'])) {
    $event_content = $data['content'];
    $user_id = $USER->id;
    $username = fullname($USER);

    // Crear el mensaje del evento
    $message = [
        'user_id' => $user_id,
        'username' => $username,
        'content' => $event_content,
        'timestamp' => time(),
    ];

    try {
        // Envio de evento a Kafka
        send_event_to_kafka($message);
        echo json_encode(['status' => 'success']);
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'No content received']);
}