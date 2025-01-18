<?php
class API_Handler {
    private $api_url = 'https://jsonplaceholder.typicode.com/posts';
    private $cache_key = 'ay_api_cache';
    private $cache_expiration = 600; // 10 minutes

    public function get_api_data($keyword = '') {
        $cached_data = get_transient($this->cache_key);

        if (false === $cached_data) {
            // sleep(5); // Simulate a slow API response
            $response = wp_remote_get($this->api_url);

            if (is_wp_error($response)) {
                return false;
            }

            $cached_data = json_decode(wp_remote_retrieve_body($response), true);
            set_transient($this->cache_key, $cached_data, $this->cache_expiration);
        }

        if (!empty($keyword)) {
            $filtered_data = array_filter($cached_data, function($item) use ($keyword) {
                return (stripos($item['title'], $keyword) !== false || stripos($item['body'], $keyword) !== false);
            });
            return array_values($filtered_data); // Reset array keys
        }

        return $cached_data;
    }

    public function clear_cache() {
        return delete_transient($this->cache_key);
    }
}
