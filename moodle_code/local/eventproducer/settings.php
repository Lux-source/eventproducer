<?php
defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    // Añade una nueva sección a la página de administración bajo 'localplugins'
    $settings = new admin_settingpage('local_eventproducer', get_string('pluginname', 'local_eventproducer'));


    $settings->add(
        new admin_setting_configtext(
            'local_eventproducer/kafka_server',
            get_string('kafka_server', 'local_eventproducer'),
            get_string('kafka_server_desc', 'local_eventproducer'),
            'localhost:9092', // Valor por defecto
            PARAM_TEXT
        )
    );

    // Añadir las configuraciones a la página de administración
    $ADMIN->add('localplugins', $settings);
}
?>