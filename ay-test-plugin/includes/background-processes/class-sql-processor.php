<?php


class SQL_Processor extends WP_Async_Request {
    protected $prefix = 'ay';
	protected $action = 'sql_tov';

	/**
	 * Handle async request to insert dummy data
	 */
	protected function handle() {
        error_log('SQL_Processor: Dispatching SQL_Processor');
        global $wpdb;

        update_option('background_tov_is_processing', true);

        $query = "INSERT INTO wp_usermeta (user_id, meta_key, meta_value)
            SELECT U.ID as user_id, 'total_order_value' as meta_key, SUM(O.order_total) as meta_value
            FROM wp_users U
            INNER JOIN wp_orders O ON U.ID = O.user_id
            GROUP BY U.ID";

        $wpdb->query($query);
        update_option('background_tov_is_processing', false);
        error_log('SQL_Processor: Inserted total_order_value meta entries');
    }

}


