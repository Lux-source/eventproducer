<?php

require_once('../../config.php');
require_once('lib.php');

require_login();

// Get the course ID from the URL (/local/kafka_integration/index.php?courseid=2)
$course_id = required_param('courseid', PARAM_INT);

// Get the course context
$context = context_course::instance($course_id);

// Set page properties
$PAGE->set_url(new moodle_url('/local/kafka_integration/index.php', ['courseid' => $course_id]));
$PAGE->set_context($context);
$PAGE->set_title(get_string('pluginname', 'local_kafka_integration'));
$PAGE->set_heading(get_string('pluginname', 'local_kafka_integration'));

// Get current user ID and full name
$current_user_id = $USER->id;
$current_username = fullname($USER);

// Obtain the role of the user in the course context
$user_roles = get_user_roles($context, $USER->id, false);
$user_role = '';
if (!empty($user_roles)) {
    $user_role = reset($user_roles)->shortname;
}

// Output page header
echo $OUTPUT->header();

echo '<h2>' . get_string('pluginname', 'local_kafka_integration') . '</h2>';

// Inject the role into JavaScript
echo "<script>var currentUserRole = '" . $user_role . "';</script>";
?>

<!-- Text area for code editing -->
<textarea id="code_editor" rows="15" cols="80" placeholder="Escribe tu código aquí..."></textarea>

<!-- Container to show shared code -->
<div id="shared_code_container" style="border: 1px solid #ccc; padding: 10px; margin-top: 20px;">
    <h3>Código compartido:</h3>
    <pre id="shared_code"></pre>
</div>

<script>
    // Capture the current user ID and role from PHP
    var currentUserId = <?php echo json_encode($current_user_id); ?>;
    var currentUserRole = <?php echo json_encode($user_role); ?>;
    var courseId = <?php echo json_encode($course_id); ?>;

    // Function to filter events
    function shouldSendEvent(eventType, userRole) {
        const rules = {
            'student': ['edit_code', 'join_session', 'leave_session'],
            'teacher': ['edit_code', 'start_session', 'end_session', 'assign_role'],
            'admin': ['manage_session', 'log_event']
        };

        // Check if the role can send that event
        return rules[userRole] && rules[userRole].includes(eventType);
    }

    // Function to send content to the server
    function sendContent(content, eventType) {
        var xhr = new XMLHttpRequest();
        xhr.open('POST', 'send.php', true);
        xhr.setRequestHeader('Content-Type', 'application/json');
        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4) {
                if (xhr.status === 200) {
                    console.log('Evento enviado al servidor');
                } else {
                    console.error('Error al enviar el evento al servidor');
                }
            }
        };
        var data = JSON.stringify({ content: content, type: eventType, courseid: courseId });
        xhr.send(data);
    }

    // Add a listener to the text area to capture changes
    var textarea = document.getElementById('code_editor');
    textarea.addEventListener('input', function () {
        var content = textarea.value;
        var eventType = 'edit_code';

        // Check if the event should be sent
        if (shouldSendEvent(eventType, currentUserRole)) {
            sendContent(content, eventType);
        }
    });

    // Function to get messages from the server
    function getMessages() {
        var xhr = new XMLHttpRequest();
        xhr.open('GET', 'receive.php?courseid=' + courseId, true);
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

    // Function to update the shared code container
    function updateSharedCode(msg) {
        console.log('Actualizando código compartido con: ', msg);
        var sharedCode = document.getElementById('shared_code');
        sharedCode.textContent = msg.content;
    }

    // Start polling every 2 seconds
    setInterval(getMessages, 2000);
</script>

<?php
echo $OUTPUT->footer();
