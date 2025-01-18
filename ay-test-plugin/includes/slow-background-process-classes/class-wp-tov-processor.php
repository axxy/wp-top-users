<?php

class WP_TOV_Processor extends WP_Background_Process {
    protected $action = 'update_total_order_value';
    protected $batch_size = 1000; // Process users in smaller batches

    protected function task($user_id) {
        try {
            global $wpdb;

            // Use more efficient query with INDEX hints
            $orders_table = $wpdb->prefix . 'orders';
            $total_order_value = $wpdb->get_var($wpdb->prepare(
                "SELECT SUM(order_total) FROM {$orders_table} USE INDEX (user_id)
                WHERE user_id = %d",
                $user_id
            ));

            update_user_meta($user_id, 'total_order_value', $total_order_value);

            return false;
        } catch (Exception $e) {
            error_log("Error processing user ID {$user_id}: " . $e->getMessage());
            return false; // Skip
        }
    }

    public function trigger_background_processing() {
        global $wpdb;

        try {
            // Check if already processing
            if (get_option('background_tov_is_processing')) {
                error_log('Background processing already running');
                return false;
            }

            $users_table = $wpdb->prefix . 'users';
            $offset = 0;

            // Process users in batches to prevent memory issues
            while (true) {
                $users = $wpdb->get_results($wpdb->prepare(
                    "SELECT ID FROM {$users_table} 
                    ORDER BY ID 
                    LIMIT %d OFFSET %d",
                    $this->batch_size,
                    $offset
                ));

                if (empty($users)) {
                    break;
                }

                foreach ($users as $user) {
                    $this->push_to_queue($user->ID);
                }

                error_log('Dispatching background process for users ' . $offset . ' to ' . ($offset + $this->batch_size));
                $offset += $this->batch_size;

                // Save batch to prevent memory buildup
                $this->save();
            }

            // Final save and dispatch
            if ($this->save()) {
                update_option('background_tov_is_processing', true);
                return $this->dispatch();
            }

        } catch (Exception $e) {
            error_log('Error triggering background process: ' . $e->getMessage());
            return false;
        }
    }

    protected function complete() {
        parent::complete();
        update_option('background_tov_is_processing', false);
        error_log('Background processing completed successfully');
    }
}