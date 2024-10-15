<?php

// Creacion de nueva configuracion para kafka
$conf = new RdKafka\Conf();

// Configuracion del callback para el rebalance de las particiones 
// Sera importante cuando se añade o elimina consumidores, permitirá revocar o asignar esas particiones.
$conf->setRebalanceCb(function (RdKafka\KafkaConsumer $kafka, $err, array $partitions = null) {
    switch ($err) {
        // Caso cuando se asignan nuevas particiones al consumidor
        case RD_KAFKA_RESP_ERR__ASSIGN_PARTITIONS:
            echo "Assign: ";
            var_dump($partitions); // Muestra las particiones asignadas
            $kafka->assign($partitions); // Asigna las particiones al consumidor
            break;

        // Caso cuando revocan las particiones asignadas
        case RD_KAFKA_RESP_ERR__REVOKE_PARTITIONS:
            echo "Revoke: ";
            var_dump($partitions); // Muestra las particiones que se están revocando
            $kafka->assign(NULL); // Deasigna todas las particiones
            break;

        default:
            throw new \Exception($err);
    }
});

// Configuracion del 'group.id', para identificar al grupo de consumidores.
// Los consumidores que tengan el mismo group.id compartirán las particiones de un topic
// Cada consumidor del grupo leerá de particiones diferentes
$conf->set('group.id', 'myConsumerGroup');

// Configuracion de la lista de brokers de Kafka a los que los consumidores se conectará
// Este caso, broker corre en la dirección del localhost en el puerto por defecto de Kafka 9092
$conf->set('metadata.broker.list', 'kafka:9092');

$conf->set('auto.offset.reset', 'earliest');

$conf->set('enable.partition.eof', 'true');

$consumer = new RdKafka\KafkaConsumer($conf);
// El consumidor se subscribe al topic 'test_topic'
$consumer->subscribe(['test_topic']);

echo "Waiting for partition assignment... (may take some time when quickly re-joining the group after leaving it.)\n";

// Este es el bucle principal donde el consumidor seguirá esperando y procesando mensajes.
while (true) {
    //
    $message = $consumer->consume(120 * 1000);

    switch ($message->err) {
        // Dependiendo del error o evento que ocura cambia caso
        // Mensaje recibido correctamente
        case RD_KAFKA_RESP_ERR_NO_ERROR:
            var_dump($message); // Muestra el contenido completo del mensaje recibido
            break;

        // Caso en el que se ha alcanzado el final de la partición
        case RD_KAFKA_RESP_ERR__PARTITION_EOF:
            echo "No more messages; will wait for more\n";
            break;

        // Caso en el que se ha alcanzado el tiempo de espera (timeout) y no se recibieron mensajes.
        case RD_KAFKA_RESP_ERR__TIMED_OUT:
            echo "Timed out\n";
            break;

        default:
            throw new \Exception($message->errstr(), $message->err);
            break;
    }
}
