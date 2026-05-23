<?php
/**
 * Plugin Name: Block Usage Tracker
 * Plugin URI: #
 * Description: Track Gutenberg block usage across WordPress posts and pages.
 * Version: 1.0.0
 * Author: Sachin
 * Text Domain: block-usage-tracker
 */

if (!defined('ABSPATH')) {
    exit;
}

final class BUT_Plugin {

    const VERSION = '1.0.0';

    public function __construct() {
        $this->define_constants();
        $this->includes();

        register_activation_hook(__FILE__, ['BUT_Database', 'activate']);

        add_action('plugins_loaded', [$this, 'init']);
    }

    private function define_constants() {
        define('BUT_PATH', plugin_dir_path(__FILE__));
        define('BUT_URL', plugin_dir_url(__FILE__));
        define('BUT_VERSION', self::VERSION);
    }

    private function includes() {
        require_once BUT_PATH . 'includes/class-but-database.php';
        require_once BUT_PATH . 'includes/class-but-helpers.php';
        require_once BUT_PATH . 'includes/class-but-scanner.php';
        require_once BUT_PATH . 'includes/class-but-export.php';
        require_once BUT_PATH . 'includes/class-but-admin.php';
    }

    public function init() {
        new BUT_Admin();
        new BUT_Export();
    }
}

new BUT_Plugin();
?>