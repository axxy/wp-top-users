<?php

class Dummy_Process extends WP_Async_Request {

	protected $action = 'dummy_process';

	/**
	 * Handle async request to insert dummy data
	 */
	protected function handle($num_users = 10_000, $num_orders = 100_000) {
        global $wpdb;

        // Create dummy users
        for ($i = 0; $i < $num_users; $i++) {
            $username = 'user_' . uniqid();
            $email = $username . '@example.com';
            $user_id = wp_create_user($username, wp_generate_password(), $email);

            if (is_wp_error($user_id)) {
                error_log('Failed to create user: ' . $user_id->get_error_message());
                continue;
            } else {
                error_log('User created: ' . $user_id);
            }
        }

        error_log('Users creation loop ended');
        error_log('starting orders creation');

        // Get all user IDs
        $user_ids = $wpdb->get_col("SELECT ID FROM {$wpdb->users}");
        error_log('all user ids fetched: ' . count($user_ids));

        // Create dummy orders
        $orders_table = $wpdb->prefix . 'orders';
        $batch_size = 1_000;
        $orders_created = 0;

        while ($orders_created < $num_orders) {
            error_log('while loop hit');
            $values = array();
            $placeholders = array();

            for ($i = 0; $i < $batch_size && $orders_created < $num_orders; $i++) {
                $user_id = $user_ids[array_rand($user_ids)];
                $order_id = 'ORDER' . str_pad($orders_created + 1, 8, '0', STR_PAD_LEFT);
                $order_total = mt_rand(100, 10000) / 100; // Random total between 1.00 and 100.00
                $order_date = date('Y-m-d H:i:s', mt_rand(strtotime('-1 year'), time()));

                $values[] = $user_id;
                $values[] = $order_id;
                $values[] = $order_total;
                $values[] = $order_date;

                $placeholders[] = "(%d, %s, %f, %s)";
                $orders_created++;
            }

            $query = $wpdb->prepare(
                "INSERT INTO $orders_table (user_id, order_id, order_total, order_date) VALUES " . implode(', ', $placeholders),
                $values
            );

            $wpdb->query($query);
            error_log('Orders created #: ' . $orders_created);
        }

        return array(
            'users_created' => $num_users,
            'orders_created' => $orders_created
        );
    }

}