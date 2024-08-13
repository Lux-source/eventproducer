<?php
require('../../config.php');

try {
    $rk = new RdKafka\Producer();
    $rk->addBrokers(get_config('local_eventproducer', 'kafka_server'));

    // Guardar el productor en la sesión 
    $_SESSION['kafka_producer'] = $rk;

    echo json_encode(['status' => 'connected']);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    http_response_code(500);
}
?>
