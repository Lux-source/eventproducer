<?php
require('../../config.php');
require_once($CFG->libdir . '/formslib.php');

$PAGE->set_url('/local/eventproducer/index.php');
$PAGE->set_title(get_string('pluginname', 'local_eventproducer'));
$PAGE->set_heading(get_string('pluginname', 'local_eventproducer'));
$PAGE->set_context(context_system::instance());

echo $OUTPUT->header();
?>


<form id="eventForm">
    <input type="hidden" name="id" />

    <label for="name"><?php echo get_string('event_name', 'local_eventproducer'); ?></label>
    <input type="text" id="name" name="name" required />

    <label for="description"><?php echo get_string('event_description', 'local_eventproducer'); ?></label>
    <textarea id="description" name="description"></textarea>

    <label for="type"><?php echo get_string('event_type', 'local_eventproducer'); ?></label>
    <input type="text" id="type" name="type" required />

    <label for="status"><?php echo get_string('event_status', 'local_eventproducer'); ?></label>
    <select id="status" name="status" required>
        <option value="1">Active</option>
        <option value="0">Inactive</option>
    </select>

    <button type="submit"><?php echo get_string('event_create', 'local_eventproducer'); ?></button>
    <button type="button" id="deleteButton"><?php echo get_string('event_delete', 'local_eventproducer'); ?></button>
</form>

<ul id="eventsList"></ul>

<!-- Formulario para Conexión con Kafka -->
<div>
    <h3>Kafka Connection</h3>
    <div id="connectionStatus">Not connected</div>
    <button type="button" id="closeButton">Close Connection</button>
</div>

<script src="events.js"></script>
<script src="eventproducer.js"></script>

<?php
echo $OUTPUT->footer();
?>