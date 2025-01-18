<?php

class DB_Helper {
    protected $action = 'update_total_order_value';

    public function create_orders_table() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'orders';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name IF NOT EXISTS(
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            order_id varchar(255) NOT NULL,
            order_total decimal(10,2) NULL,
            order_date datetime NOT NULL,
            PRIMARY KEY  (id)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);

        // Check if user_id index exists and add it if it doesn't
        $indexes = $wpdb->get_results("SHOW INDEX FROM $table_name");
        $existing_indexes = array_column($indexes, 'Key_name');

        if (!in_array('user_id', $existing_indexes)) {
            $wpdb->query("ALTER TABLE $table_name ADD INDEX user_id (user_id)");
        }

        // Verify the table and index were created successfully
        if ($wpdb->last_error) {
            error_log('Error creating orders table or index: ' . $wpdb->last_error);
        }
    }

    public function get_top_users() {
        global $wpdb;
        $users_table = $wpdb->prefix . 'users';
        $usermeta_table = $wpdb->prefix . 'usermeta';
        $users_num = 5;

        $query = "
            SELECT u.user_login, um.meta_value as total_order_value
            FROM $users_table u
            JOIN $usermeta_table um ON u.ID = um.user_id
            WHERE um.meta_key = 'total_order_value'
            ORDER BY CAST(um.meta_value AS DECIMAL(10,2)) DESC
            LIMIT $users_num
        ";

        return $wpdb->get_results($query);
    }

        public function get_users_count() {
        global $wpdb;
        $users_table = $wpdb->prefix . 'users';
        return $wpdb->get_var("SELECT COUNT(ID) FROM $users_table");
    }

    public function get_orders_count() {
        global $wpdb;
        $orders_table = $wpdb->prefix . 'orders';
        return $wpdb->get_var("SELECT COUNT(ID) FROM $orders_table");
    }

    public function get_total_order_values_count() {
        global $wpdb;
        $usermeta_table = $wpdb->prefix . 'usermeta';
        return $wpdb->get_var("SELECT COUNT(user_id) FROM $usermeta_table WHERE meta_key = 'total_order_value'");
    }
}
