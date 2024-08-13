document.addEventListener('DOMContentLoaded', function () {
    let connectionStatus = document.getElementById('connectionStatus');
    let isConnected = false;

    // Conectar a Kafka
    function connectToKafka() {
        fetch('/local/eventproducer/connect.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            }
        }).then(response => {
            if (response.ok) {
                isConnected = true;
                connectionStatus.textContent = 'Connected to Kafka';
            } else {
                connectionStatus.textContent = 'Failed to connect to Kafka';
                console.error('Failed to connect to Kafka');
            }
        });
    }

    // Cerrar conexión con Kafka
    function closeKafkaConnection() {
        fetch('/local/eventproducer/close_connection.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            }
        }).then(response => {
            if (response.ok) {
                isConnected = false;
                connectionStatus.textContent = 'Connection closed';
            } else {
                connectionStatus.textContent = 'Failed to close connection';
                console.error('Failed to close connection');
            }
        });
    }

    // Conectar a Kafka al cargar la página
    connectToKafka();

    document.getElementById('eventForm').addEventListener('submit', function (event) {
        event.preventDefault();

        if (isConnected) {
            const data = {
                topic: document.getElementById('topicField').value,
                inputField: document.getElementById('inputField').value,
                timestamp: new Date().toISOString()
            };

            fetch('/local/eventproducer/send_event.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(data)
            }).then(response => {
                if (response.ok) {
                    console.log('Event sent successfully');
                } else {
                    console.error('Failed to send event');
                }
            });
        } else {
            console.error('Not connected to Kafka');
        }
    });

    document.getElementById('closeButton').addEventListener('click', function () {
        closeKafkaConnection();
    });
});
