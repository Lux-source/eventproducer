<?php

// Creación de una nueva configuración de Kafka para el productor
$conf = new RdKafka\Conf();

// Configura la lista de brokers a los que se conectará el productor.
// Aquí se establece que el broker está corriendo en 'localhost' en el puerto '9092'.
$conf->set('metadata.broker.list', 'kafka:9092');

// Opcional: Si necesitas garantizar que los mensajes se produzcan exactamente una vez y
// mantener el orden original de producción, puedes habilitar la "idempotencia".
// La idempotencia asegura que no se dupliquen los mensajes al producir.
// $conf->set('enable.idempotence', 'true');

// Creación de una instancia de productor Kafka usando la configuración anterior.
$producer = new RdKafka\Producer($conf);

// Crea o se conecta al topic llamado 'test'. Este es el tema al que se enviarán los mensajes.
// 'RD_KAFKA_PARTITION_UA' indica que se debe seleccionar automáticamente la partición.
$topic = $producer->newTopic("test_topic");

// Bucle para producir 10 mensajes y enviarlos al tema 'test'
for ($i = 0; $i < 10; $i++) {
    // Produce un mensaje en el tema 'test', en una partición asignada automáticamente.
    // El segundo parámetro (0) representa el nivel de mensaje (a menudo se deja como 0).
    // El mensaje enviado es "Message $i", donde $i es el número del mensaje.
    $topic->produce(RD_KAFKA_PARTITION_UA, 0, "Message $i");

    // Poll es necesario para manejar los eventos de Kafka, como confirmaciones de envío de mensajes.
    // El 0 indica que no se espera tiempo para la espera de eventos asíncronos.
    // Es necesario para asegurarse de que se procesen las colas internas del productor.
    $producer->poll(0);
}

// Después de enviar los mensajes, intentamos hacer un "flush" para asegurarnos de que
// todos los mensajes han sido efectivamente enviados a Kafka.
for ($flushRetries = 0; $flushRetries < 10; $flushRetries++) {
    // El 'flush' intenta limpiar las colas de mensajes pendientes.
    // Se espera hasta 10 segundos (10000 milisegundos) para que se envíen todos los mensajes.
    $result = $producer->flush(10000);

    // Si no hay errores y todos los mensajes fueron enviados con éxito, salimos del bucle.
    if (RD_KAFKA_RESP_ERR_NO_ERROR === $result) {
        break;
    }
}

// Si después de 10 intentos aún quedan mensajes sin enviar, lanzamos una excepción,
// lo que indica que hubo problemas al enviar algunos de los mensajes.
if (RD_KAFKA_RESP_ERR_NO_ERROR !== $result) {
    throw new \RuntimeException('Was unable to flush, messages might be lost!');
}

