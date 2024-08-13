<?php
require('../../config.php');

if (!extension_loaded('rdkafka')) {
    echo json_encode(['status' => 'error', 'message' => 'Kafka extension not loaded']);
    http_response_code(500);
    exit;
}

$eventData = json_decode(file_get_contents('php://input'), true);

if (empty($eventData['topic']) || empty($eventData['inputField'])) {
    echo json_encode(['status' => 'error', 'message' => 'Topic and inputField are required']);
    http_response_code(400);
    exit;
}

try {
    $rk = new RdKafka\Producer();
    $rk->setLogLevel(LOG_DEBUG);
    $rk->addBrokers(get_config('local_eventproducer', 'kafka_server'));

    $topic = $rk->newTopic($eventData['topic']);
    $topic->produce(RD_KAFKA_PARTITION_UA, 0, json_encode($eventData));

    // Asegúrate de que el mensaje haya sido enviado
    $rk->poll(0);
    while ($rk->getOutQLen() > 0) {
        $rk->poll(50);
    }

    echo json_encode(['status' => 'success']);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    http_response_code(500);
}
?>
