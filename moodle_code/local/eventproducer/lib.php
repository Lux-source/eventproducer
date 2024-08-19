<?php
defined('MOODLE_INTERNAL') || die();

function local_eventproducer_extend_navigation(global_navigation $nav)
{
    $node = $nav->add(
        get_string('pluginname', 'local_eventproducer'),
        new moodle_url('/local/eventproducer/index.php')
    );
    $node->showinflatnavigation = true;
}

?>