<?php
require ('../../config.php');

$eventData = json_decode(file_get_contents('php://input'), true);

if (empty($eventData['topic']) || empty($eventData['inputField'])) {
    echo json_encode(['status' => 'error', 'message' => 'Topic and inputField are required']);
    http_response_code(400);
    exit;
}

try {
    $kafkaServer = get_config('local_eventproducer', 'kafka_server');
    $topic = $eventData['topic'];
    $data = json_encode([
        "records" => [
            [
                "value" => $eventData
            ]
        ]
    ]);

    $url = "$kafkaServer/topics/$topic";
    $options = [
        'http' => [
            'header' => "Content-type: application/vnd.kafka.json.v2+json\r\n",
            'method' => 'POST',
            'content' => $data,
        ],
    ];
    $context = stream_context_create($options);
    $result = file_get_contents($url, false, $context);

    if ($result === false) {
        throw new Exception('Failed to send event to Kafka');
    }

    echo json_encode(['status' => 'success']);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    http_response_code(500);
}
?>