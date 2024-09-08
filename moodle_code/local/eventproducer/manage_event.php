<?php
require_once('../../config.php');
require_once($CFG->libdir . '/adminlib.php');

$action = required_param('action', PARAM_TEXT); // La acción que se va a realizar: create, read, update, delete.
$id = optional_param('id', 0, PARAM_INT); // El ID del evento, si existe.

switch ($action) {
    case 'create':
        $name = required_param('name', PARAM_TEXT);
        $description = optional_param('description', '', PARAM_TEXT);
        $type = required_param('type', PARAM_TEXT);
        $status = required_param('status', PARAM_INT);

        $record = new stdClass();
        $record->name = $name;
        $record->description = $description;
        $record->type = $type;
        $record->status = $status;

        $DB->insert_record('eventproducer_events', $record);
        echo json_encode(['status' => 'success', 'message' => 'Event created successfully']);
        break;

    case 'read':
        if ($id) {
            $event = $DB->get_record('eventproducer_events', ['id' => $id]);
            echo json_encode($event);
        } else {
            $events = $DB->get_records('eventproducer_events');
            echo json_encode(array_values($events));
        }
        break;

    case 'update':
        $name = required_param('name', PARAM_TEXT);
        $description = optional_param('description', '', PARAM_TEXT);
        $type = required_param('type', PARAM_TEXT);
        $status = required_param('status', PARAM_INT);

        $record = new stdClass();
        $record->id = $id;
        $record->name = $name;
        $record->description = $description;
        $record->type = $type;
        $record->status = $status;

        $DB->update_record('eventproducer_events', $record);
        echo json_encode(['status' => 'success', 'message' => 'Event updated successfully']);
        break;

    case 'delete':
        if ($id) {
            $DB->delete_records('eventproducer_events', ['id' => $id]);
            echo json_encode(['status' => 'success', 'message' => 'Event deleted successfully']);
        }
        break;

    default:
        echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
        break;
}
