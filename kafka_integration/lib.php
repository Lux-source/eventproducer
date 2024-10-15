<?php

function send_event_to_kafka($message)
{
    // Creación de una nueva configuración de Kafka para el productor
    $conf = new RdKafka\Conf();

    // Configura la lista de brokers a los que se conectará el productor.
    $conf->set('metadata.broker.list', 'kafka:9092');

    // Creación de una instancia de productor Kafka usando la configuración anterior.
    $producer = new RdKafka\Producer($conf);

    // Crea o se conecta al topic llamado 'test_topic'.
    $topic = $producer->newTopic("test_topic");

    // Convertir el mensaje a JSON
    $message_json = json_encode($message);

    // Producir el mensaje en el topic 'test_topic'
    $topic->produce(RD_KAFKA_PARTITION_UA, 0, $message_json);

    // Poll es necesario para manejar los eventos de Kafka
    $producer->poll(0);

    // Después de enviar los mensajes, intentamos hacer un "flush"
    for ($flushRetries = 0; $flushRetries < 10; $flushRetries++) {
        $result = $producer->flush(10000);
        if (RD_KAFKA_RESP_ERR_NO_ERROR === $result) {
            break;
        }
    }

    if (RD_KAFKA_RESP_ERR_NO_ERROR !== $result) {
        throw new \RuntimeException('No se pudo enviar el mensaje a Kafka.');
    }

    error_log('Mensaje enviado a Kafka:' . json_encode($message));

    // Cierre ordenado (opcional si es necesario)
    unset($producer);
}

function consume_events_from_kafka($current_user_id, $course_id)
{
    $conf = new RdKafka\Conf();
    $conf->set('metadata.broker.list', 'kafka:9092');

    // Establecer un 'group.id' único
    $conf->set('group.id', 'consumer_' . $current_user_id);
    $conf->set('auto.offset.reset', 'earliest');

    $consumer = new RdKafka\KafkaConsumer($conf);

    // Suscribirse al topic
    $consumer->subscribe(['test_topic']);

    $messages = [];

    // Consumir mensajes
    $timeout = 1000; // 1000 ms
    $start = microtime(true);
    $maxDuration = 2;

    while (microtime(true) - $start < $maxDuration) {
        $message = $consumer->consume($timeout);
        switch ($message->err) {
            case RD_KAFKA_RESP_ERR_NO_ERROR:
                $msg = json_decode($message->payload, true);
                if ($msg['course_id'] == $course_id) { // Filter by course
                    $messages[] = $msg;
                }
                break;
            case RD_KAFKA_RESP_ERR__PARTITION_EOF:
                // No hay más mensajes en la partición
                break;
            case RD_KAFKA_RESP_ERR__TIMED_OUT:
                // Tiempo de espera agotado
                break;
            default:
                error_log('Error al consumir mensajes: ' . $message->errstr());
                throw new \Exception($message->errstr(), $message->err);
                break;
        }
    }

    return $messages;
}

// Agregar la función shouldLogEvent
function shouldLogEvent($eventType, $userRole)
{
    // Definir reglas de filtrado en el servidor
    $rules = [
        'student' => ['edit_code', 'join_session', 'leave_session'],
        'teacher' => ['edit_code', 'start_session', 'end_session', 'assign_role'],
        'admin' => ['manage_session', 'log_event']
    ];

    // Verificar si el rol existe en las reglas
    if (isset($rules[$userRole])) {
        return in_array($eventType, $rules[$userRole]);
    }

    return false;
}
