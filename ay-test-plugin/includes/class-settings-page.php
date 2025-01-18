<?php
class Settings_Page {
    public function __construct() {
        add_action('admin_menu', array($this, 'add_settings_page'));
        add_action('admin_init', array($this, 'register_settings'));
    }

    public function add_settings_page() {
        add_options_page(
            'Plugin Test Options',
            'Plugin Test Options',
            'manage_options',
            'ay-test-plugin',
            array($this, 'render_settings_page')
        );
    }

    public function register_settings() {
        register_setting('ay-test-plugin-settings', 'ay_api_keyword');
    }


    public function render_settings_page() {
        $db_helper = new DB_Helper();
        $api_handler = new API_Handler();
        $top_users = $db_helper->get_top_users();
        $users_count = $db_helper->get_users_count();
        $orders_count = $db_helper->get_orders_count();
        $keyword = get_option('ay_api_keyword', '');
        $api_data = $api_handler->get_api_data($keyword);
        $tov_count = $db_helper->get_total_order_values_count();
        $tov_is_processing = get_option('background_tov_is_processing', false);
        $dummy_is_processing = get_option('background_dummy_is_processing', false);
        ?>
        <div class="wrap ay-test-plugin">
            <h1>Plugin Test Options</h1>

            <?php $this->render_notices(); ?>

            <div class="ay-card-container">

                <div class="ay-card">
                    <h2>Top 5 Users by Total Order Value</h2>
                    <p>Users #: <?php echo $users_count ?> &nbsp; Orders #: <?php echo $orders_count ?></p>
                    <p>Total Order Values #: <?php echo $tov_count ?></p>
                    <?php if ($top_users): ?>
                        <ul class="top-users-list">
                            <?php foreach ($top_users as $user): ?>
                                <li>
                                    <span class="user-name"><?php echo esc_html($user->user_login); ?></span>
                                    <span class="order-value">$<?php echo number_format($user->total_order_value, 2); ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p>No user data available.</p>
                    <?php endif; ?>
                </div>

                <div class="ay-card">
                    <h2>Background Management</h2>
                    <h3>Background Processing:</h3>
                    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="ay-form">
                        <input type="hidden" name="action" value="trigger_dummy_data_processing">
                        <?php submit_button('Insert Dummy Data', 'primary', 'submit', true); ?>
                    </form>

                    <h3>Process User total_order_value:</h3>
                    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="ay-form">
                        <input type="hidden" name="action" value="trigger_background_processing">
                        <?php submit_button('Process User Orders', 'primary', 'submit', true); ?>
                    </form>
                </div>

                <div class="ay-card">
                    <h2>Data Management</h2>
                    <h3>Clear The Api Cache</h3>
                    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="ay-form">
                        <input type="hidden" name="action" value="trigger_cache_clearing">
                        <?php submit_button('Clear Cache', 'secondary', 'submit', true); ?>
                    </form>

                        <?php if ($tov_is_processing == true): ?>
                            <div id="processing-status" class="notice notice-info">
                                <p>
                                    Background TOV processing is running...
                                    <a href="javascript:void(0);" onclick="window.location.reload();" class="refresh-status" style="text-decoration: none; color: #2271b1;">
                                        <span class="dashicons dashicons-update" style="font-size: 20px; width: 20px; height: 20px; cursor: pointer;" title="Refresh Status"></span>
                                    </a>
                                </p>
                            </div>
                        <?php endif; ?>

                        <?php if ($dummy_is_processing == true): ?>
                            <div id="processing-status" class="notice notice-info">
                                <p>
                                    Background dummy data processing is running...
                                    <a href="javascript:void(0);" onclick="window.location.reload();" class="refresh-status" style="text-decoration: none; color: #2271b1;">
                                        <span class="dashicons dashicons-update" style="font-size: 20px; width: 20px; height: 20px; cursor: pointer;" title="Refresh Status"></span>
                                    </a>
                                </p>

                            </div>
                        <?php endif; ?>

                </div>

                <div class="ay-card api-data full-width">
                    <h2>API Data</h2>
                    <form method="post" action="options.php" class="ay-form">
                        <?php settings_fields('ay-test-plugin-settings'); ?>
                        <?php do_settings_sections('ay-test-plugin-settings'); ?>
                        <div class="form-group">
                            <input type="text" placeholder="API Keyword" name="ay_api_keyword" id="ay_api_keyword" value="<?php echo esc_attr($keyword); ?>" />
                            <?php submit_button("Get Keyword", 'primary', 'submit', false); ?>
                        </div>
                    </form>
                    <?php if ($api_data): ?>
                        <div class="api-entries" id="apiEntries">
                            <?php foreach ($api_data as $entry): ?>
                                <div class="api-entry">
                                    <h3><?php echo esc_html($entry['title']); ?></h3>
                                    <p><?php echo esc_html($entry['body']); ?></p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="pagination">
                            <button id="prevPage" class="button">Previous</button>
                            <span id="pageInfo"></span>
                            <button id="nextPage" class="button">Next</button>
                        </div>
                    <?php else: ?>
                        <p>No API data available.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php
    }

    private function render_notices() {
        if (isset($_GET['status'])) {
            $status = esc_html($_GET['status']);
            $message = 'Cache has been cleared.';
            $notice_class = 'success';
            printf('<div class="notice %s is-dismissible"><p>%s</p></div>', esc_attr($notice_class), esc_html($message));
        }
    }
}
