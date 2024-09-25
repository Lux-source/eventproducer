<?php

require_once('../../config.php');
require_once('lib.php');

require_login();

$PAGE->set_url(new moodle_url('/local/kafka_integration/index.php'));
$PAGE->set_context(context_system::instance());
$PAGE->set_title(get_string('pluginname', 'local_kafka_integration'));
$PAGE->set_heading(get_string('pluginname', 'local_kafka_integration'));

echo $OUTPUT->header();

echo '<h2>' . get_string('pluginname', 'local_kafka_integration') . '</h2>';

// Obtener el ID y nombre del usuario actual
$current_user_id = $USER->id;
$current_username = fullname($USER);
?>

<!-- Área de texto para editar código -->
<textarea id="code_editor" rows="15" cols="80" placeholder="Escribe tu código aquí..."></textarea>

<!-- Contenedor para mostrar el código compartido -->
<div id="shared_code_container" style="border: 1px solid #ccc; padding: 10px; margin-top: 20px;">
    <h3>Código compartido:</h3>
    <pre id="shared_code"></pre>
</div>

<script>
    // Capturar el ID del usuario actual desde PHP
    var currentUserId = <?php echo json_encode($current_user_id); ?>;

    // Función para enviar el contenido al servidor
    function sendContent(content) {
        var xhr = new XMLHttpRequest();
        xhr.open('POST', 'send.php', true);
        xhr.setRequestHeader('Content-Type', 'application/json');
        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4 && xhr.status !== 200) {
                console.error('Error al enviar el evento al servidor');
            }
        };
        var data = JSON.stringify({ content: content });
        xhr.send(data);
    }

    // Añadir un listener al campo de texto para capturar cambios
    var textarea = document.getElementById('code_editor');
    textarea.addEventListener('input', function () {
        var content = textarea.value;
        sendContent(content);
    });

    // Función para obtener mensajes del servidor
    function getMessages() {
        var xhr = new XMLHttpRequest();
        xhr.open('GET', 'receive.php', true);
        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4 && xhr.status === 200) {
                var response = JSON.parse(xhr.responseText);
                if (response.status === 'success') {
                    var messages = response.messages;
                    messages.forEach(function (msg) {
                        updateSharedCode(msg);
                    });
                } else {
                    console.error('Error al recibir mensajes:', response.message);
                }
            }
        };
        xhr.send();
    }

    // Función para actualizar el código compartido
    function updateSharedCode(msg) {
        console.log('Actualizando código compartido con: ', msg);
        var sharedCode = document.getElementById('shared_code');
        sharedCode.textContent = msg.content;
    }

    // Iniciar el polling cada 2 segundos
    setInterval(getMessages, 2000);
</script>

<?php
echo $OUTPUT->footer();
