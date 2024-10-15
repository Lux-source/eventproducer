<?php

// Configuración del productor Kafka
$conf = new RdKafka\Conf();

// Establecer un callback para asegurarse de que se entregan los mensajes
$conf->setDrMsgCb(function ($kafka, $message) {
    echo "Message delivered with status {$message->errstr()}\n";
});

$conf->set('metadata.broker.list', 'kafka:9092');
// Crear el productor

$producer = new RdKafka\Producer($conf);
//$producer->addBrokers("kafka:9092");

// Seleccionar el topic
$topic = $producer->newTopic("test_topic");

// Recibir datos del formulario

$userId = $_POST['user_id'];
$activityId = $_POST['activity_id'];
$eventContent = $_POST['event_content'];

// Crear el mensaje a enviar
$message = [
    'event_type' => 'coding_activity',
    'user_id' => $userId,
    'activity_id' => $activityId,
    'timestamp' => date(DATE_W3C),
    'content' => $eventContent,
];

// Convertir el mensaje en JSON
$payload = json_encode($message);

// Produce el mensaje: 
// - Usamos RD_KAFKA_PARTITION_UA para que Kafka decida la partición.
// - Pasamos $userId como clave para que mensajes del mismo usuario se envíen a la misma partición.
$topic->produce(RD_KAFKA_PARTITION_UA, 0, $payload, $userId);

echo "Mensaje preparado para enviar: " . $payload . "<br>";

// Asegurarse de que se envíen los mensajes antes de destruir el productor
$producer->flush(10000);

echo "Evento enviado a Kafka correctamente.";