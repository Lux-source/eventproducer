<?php
require('../../config.php');

try {
    // Seteo el Kafka REST Proxy URL
    $kafkaRestProxyUrl = 'http://kafka-rest:8082';


    $response = file_get_contents($kafkaRestProxyUrl . '/topics');

    if ($response !== false) {
        //
        $_SESSION['kafka_connected'] = true;
        echo json_encode(['status' => 'connected']);
    } else {
        throw new Exception('Could not connect to Kafka REST Proxy');
    }
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    http_response_code(500);
}
