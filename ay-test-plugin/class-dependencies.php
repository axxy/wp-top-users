<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

require_once plugin_dir_path(__FILE__) . 'includes/wp-background-processing/wp-background-processing.php';

require_once plugin_dir_path(__FILE__) . 'includes/class-db-helper.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-api-handler.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-settings-page.php';
require_once plugin_dir_path(__FILE__) . 'includes/background-processes/class-sql-dummy.php';
require_once plugin_dir_path(__FILE__) . 'includes/background-processes/class-sql-processor.php';

