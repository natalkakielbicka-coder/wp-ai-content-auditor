<?php
/**
 * Plugin Name: WP AI Content Auditor
 * Description: Audyt treści WordPress z wykorzystaniem AI.
 * Version: 0.1.0
 * Requires at least: 6.5
 * Requires PHP: 8.2
 * Author: Natalia Kielbicka
 * Text Domain: wp-ai-content-auditor
 */

if (!defined('ABSPATH')) {
    exit;
}

require_once plugin_dir_path(__FILE__) . 'includes/class-plugin.php';

$plugin = new \WPAuditor\Plugin();

$plugin->run();
