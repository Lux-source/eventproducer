<?php
require('../../config.php');
require_once($CFG->libdir.'/formslib.php');

$PAGE->set_url('/local/eventproducer/index.php');
$PAGE->set_title(get_string('pluginname', 'local_eventproducer'));
$PAGE->set_heading(get_string('pluginname', 'local_eventproducer'));
$PAGE->set_context(context_system::instance());

echo $OUTPUT->header();

// Aquí va el formulario en HTML/PHP para capturar los eventos.
?>
<form id="eventForm">
    <label for="topicField"><?php echo get_string('kafka_server', 'local_eventproducer'); ?></label>
    <input type="text" id="topicField" name="topicField" />

    <label for="inputField">Input something:</label>
    <input type="text" id="inputField" name="inputField" />

    <button type="submit">Send Event</button>
    <button type="button" id="closeButton">Close Connection</button>

    <div id="connectionStatus">Not connected</div>
</form>

<script src="eventproducer.js"></script>

<?php
echo $OUTPUT->footer();
?>
