<?php
/*
Plugin Name: Ahmed Yahya Test Plugin
Description: A test plugin with API integration, caching, and order value calculation
Version: 1.0
Author: Ahmed Yahya
Author URI: https://yahya.codes/
*/

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Include necessary files
require_once plugin_dir_path(__FILE__) . 'class-dependencies.php';

class ay_Test_Plugin {
    private $api_handler;
    private $settings_page;
    private $sql_dummy_processor;
    private $sql_tov_handler;
    private $db_helper;

    public function __construct() {
        $this->db_helper = new DB_Helper();
        $this->api_handler = new API_Handler();
        $this->settings_page = new Settings_Page();
        $this->sql_tov_handler = new SQL_Processor();
        $this->sql_dummy_processor = new SQL_Dummy_Processor();


        register_activation_hook(__FILE__, array($this, 'activate_plugin'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        add_action('admin_post_trigger_cache_clearing', array($this, 'clear_cache_handler'));
        add_action('admin_post_trigger_dummy_data_processing', array($this, 'insert_dummy_data_handler'));
        add_action('admin_post_trigger_background_processing', array($this, 'start_background_processing'));
        add_action('admin_post_stop_background_processing', array($this, 'stop_background_processes_handler'));

    }

    public function activate_plugin() {
        // on activation, create the orders tables with indexes
        $this->db_helper->create_orders_table();
    }

    public function enqueue_admin_scripts($hook) {
        if ('settings_page_ay-test-plugin' !== $hook) {
            return;
        }

        wp_enqueue_script('ay-test-plugin', plugin_dir_url(__FILE__) . 'assets/js/admin.js', array('jquery'), '1.0', true);
        wp_enqueue_style('ay-test-plugin-admin', plugin_dir_url(__FILE__) . 'assets/css/admin.css');
        wp_localize_script('ay-test-plugin', 'ay_test_plugin', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('ay_test_plugin_nonce')
        ));
    }

    public function start_background_processing() {
        if (!current_user_can('manage_options')) {
            return;
        }

        $this->sql_tov_handler->dispatch();

        wp_redirect(admin_url('options-general.php?page=ay-test-plugin'));
        exit;
    }

        public function insert_dummy_data_handler() {
        if (!current_user_can('manage_options')) {
            return;
        }

        $this->sql_dummy_processor->dispatch();

        wp_redirect(admin_url('options-general.php?page=ay-test-plugin'));
        exit;
    }

    public function clear_cache_handler() {
        if (!current_user_can('manage_options')) {
            wp_die('You do not have permission to perform this action.');
        }

        $this->api_handler->clear_cache();
        wp_redirect(admin_url('options-general.php?page=ay-test-plugin&status=cleared'));
        exit;
    }


}

new ay_Test_Plugin();
