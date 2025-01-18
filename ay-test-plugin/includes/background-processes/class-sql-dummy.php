<?php
class SQL_Dummy_Processor extends WP_Async_Request {
    protected $prefix = 'ay';
    protected $action = 'sql_dummy';

    /**
     * Split and execute SQL commands properly
     */
    private function execute_sql_file($file_path) {
        global $wpdb;

        error_log('Reading SQL file: ' . $file_path);

        // Read the SQL file
        $sql_content = file_get_contents($file_path);
        if ($sql_content === false) {
            error_log('Failed to read SQL file');
            return false;
        }

        // Remove comments and empty lines
        $sql_content = preg_replace('/--.*$/m', '', $sql_content);
        $sql_content = preg_replace('/\/\*.*?\*\//s', '', $sql_content);

        // Split on DELIMITER statements
        $parts = preg_split('/DELIMITER\s+([^\n]+)/i', $sql_content, -1, PREG_SPLIT_DELIM_CAPTURE);

        $delimiter = ';';
        for ($i = 0; $i < count($parts); $i++) {
            if ($i % 2 === 1) {
                // This is a delimiter definition
                $delimiter = trim($parts[$i]);
                continue;
            }

            // Split the part into individual commands
            $commands = explode($delimiter, $parts[$i]);

            foreach ($commands as $command) {
                $command = trim($command);
                if (empty($command)) continue;

                error_log('Executing command: ' . substr($command, 0, 50) . '...');

                $result = $wpdb->query($command);
                if ($result === false) {
                    error_log('SQL Error: ' . $wpdb->last_error);
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Handle async request
     */
    protected function handle() {
        error_log('SQL_Dummy_Processor: Starting');

        update_option('background_dummy_is_processing', true);

        $sql_file = plugin_dir_path(__FILE__) . 'dummy.sql';

        if (!file_exists($sql_file)) {
            error_log('SQL_Dummy_Processor: dummy.sql not found');
            update_option('background_dummy_is_processing', false);
            return;
        }

        if (!$this->execute_sql_file($sql_file)) {
            error_log('SQL_Dummy_Processor: Failed to execute SQL file');
            update_option('background_dummy_is_processing', false);
            return;
        }

        error_log('SQL_Dummy_Processor: Completed successfully');
        update_option('background_dummy_is_processing', false);
    }
}