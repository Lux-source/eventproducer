<?php

require_once('../../config.php');
require_once('lib.php');

require_login();

// Respuesta como JSON
header('Content-Type: application/json');

// Leer los datos enviados por AJAX
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (isset($data['content'], $data['type'], $data['courseid'])) {
    $eventType = $data['type'];
    $eventContent = $data['content'];
    $course_id = $data['courseid'];

    // Obtener el rol del usuario
    $context = context_system::instance();
    $user_roles = get_user_roles($context, $USER->id, false);
    $user_role = '';
    if (!empty($user_roles)) {
        $user_role = reset($user_roles)->shortname;
    }

    // Verificar si se debe registrar este evento
    if (shouldLogEvent($eventType, $user_role)) {
        $message = [
            'user_id' => $USER->id,
            'username' => fullname($USER),
            'course_id' => $course_id,
            'content' => $eventContent,
            'type' => $eventType,
            'timestamp' => time(),
        ];

        try {
            // Envío de evento a Kafka
            send_event_to_kafka($message);
            echo json_encode(['status' => 'success']);
        } catch (Exception $e) {
            error_log('Error in send.php: ' . $e->getMessage());
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Evento no permitido']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'No content or type received']);
}
