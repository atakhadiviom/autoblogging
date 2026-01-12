<?php
/**
 * Plugin Name: AI Blog Writer
 * Plugin URI: https://github.com/atakhadivi/autoblogging
 * Description: A WordPress plugin that generates blog posts using OpenRouter and Perplexity APIs
 * Version: 1.0.0
 * Author: Your Name
 * License: GPL v2 or later
 * Text Domain: ai-blog-writer
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('AIBW_VERSION', '1.0.0');
define('AIBW_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('AIBW_PLUGIN_URL', plugin_dir_url(__FILE__));
define('AIBW_PLUGIN_BASENAME', plugin_basename(__FILE__));

// Include main classes and functions
require_once AIBW_PLUGIN_DIR . 'includes/class-aibw-api-handler.php';
require_once AIBW_PLUGIN_DIR . 'includes/class-aibw-post-generator.php';
require_once AIBW_PLUGIN_DIR . 'includes/class-aibw-settings.php';
require_once AIBW_PLUGIN_DIR . 'includes/class-aibw-admin.php';

// Initialize the plugin
function aibw_init() {
    $settings = new AIBW_Settings();
    $api_handler = new AIBW_API_Handler($settings);
    $post_generator = new AIBW_Post_Generator($api_handler, $settings);
    $admin = new AIBW_Admin($settings, $post_generator);
    
    // Register activation hook
    register_activation_hook(__FILE__, 'aibw_activate');
    
    // Register deactivation hook
    register_deactivation_hook(__FILE__, 'aibw_deactivate');
}

// Activation function
function aibw_activate() {
    // Set default options
    $defaults = array(
        'openrouter_api_key' => '',
        'perplexity_api_key' => '',
        'default_model' => 'openrouter/meta-llama/llama-3.1-8b-instruct',
        'temperature' => 0.7,
        'max_tokens' => 2000,
        'post_category' => 1,
        'post_status' => 'draft'
    );
    
    update_option('aibw_settings', $defaults);
    
    // Flush rewrite rules
    flush_rewrite_rules();
}

// Deactivation function
function aibw_deactivate() {
    // Clean up
    flush_rewrite_rules();
}

// Initialize plugin when WordPress loads
add_action('plugins_loaded', 'aibw_init');