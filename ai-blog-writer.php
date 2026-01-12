<?php
/**
 * Plugin Name: AI Blog Writer
 * Plugin URI: https://github.com/atakhadivi/autoblogging
 * Description: Generate blog posts using OpenRouter and Perplexity APIs, with pillar post analysis and content suggestions.
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

// Include required files
require_once AIBW_PLUGIN_DIR . 'includes/class-aibw-api-handler.php';
require_once AIBW_PLUGIN_DIR . 'includes/class-aibw-post-generator.php';
require_once AIBW_PLUGIN_DIR . 'includes/class-aibw-admin.php';
require_once AIBW_PLUGIN_DIR . 'includes/class-aibw-pillar-analyzer.php';

/**
 * Main plugin class
 */
class AI_Blog_Writer {
    
    private static $instance = null;
    private $api_handler;
    private $post_generator;
    private $admin;
    private $pillar_analyzer;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('plugins_loaded', array($this, 'init'));
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
    }
    
    public function init() {
        // Initialize classes
        $this->api_handler = new AIBW_API_Handler();
        $this->post_generator = new AIBW_Post_Generator($this->api_handler);
        $this->pillar_analyzer = new AIBW_Pillar_Analyzer($this->api_handler);
        $this->admin = new AIBW_Admin($this->api_handler, $this->post_generator, $this->pillar_analyzer);
        
        // Load text domain
        load_plugin_textdomain('ai-blog-writer', false, dirname(AIBW_PLUGIN_BASENAME) . '/languages');
        
        // Add admin menu
        add_action('admin_menu', array($this->admin, 'add_admin_menu'));
        
        // Register AJAX handlers
        add_action('wp_ajax_aibw_generate_post', array($this->admin, 'ajax_generate_post'));
        add_action('wp_ajax_aibw_save_settings', array($this->admin, 'ajax_save_settings'));
        add_action('wp_ajax_aibw_generate_seo_content', array($this->admin, 'ajax_generate_seo_content'));
        add_action('wp_ajax_aibw_test_apis', array($this->admin, 'ajax_test_apis'));
        add_action('wp_ajax_aibw_generate_batch', array($this->admin, 'ajax_generate_batch'));
        add_action('wp_ajax_aibw_analyze_pillar', array($this->admin, 'ajax_analyze_pillar'));
        add_action('wp_ajax_aibw_get_suggestions', array($this->admin, 'ajax_get_suggestions'));
        add_action('wp_ajax_aibw_bulk_analyze', array($this->admin, 'ajax_bulk_analyze'));
        add_action('wp_ajax_aibw_find_pillars', array($this->admin, 'ajax_find_pillars'));
        
        // Enqueue admin scripts
        add_action('admin_enqueue_scripts', array($this->admin, 'enqueue_admin_scripts'));
    }
    
    public function activate() {
        // Set default options
        $settings = get_option('aibw_settings', array());
        if (empty($settings)) {
            $default_settings = array(
                'openrouter_api_key' => '',
                'perplexity_api_key' => '',
                'default_model' => 'openrouter/meta-llama/llama-3.1-8b-instruct',
                'perplexity_model' => 'sonar-small-online',
                'post_status' => 'draft',
                'default_author' => get_current_user_id(),
                'max_tokens' => 2000,
                'temperature' => 0.7
            );
            update_option('aibw_settings', $default_settings);
        }
        
        // Create plugin directory if needed
        $upload_dir = wp_upload_dir();
        $plugin_dir = $upload_dir['basedir'] . '/ai-blog-writer';
        if (!file_exists($plugin_dir)) {
            wp_mkdir_p($plugin_dir);
        }
        
        // Flush rewrite rules
        flush_rewrite_rules();
    }
    
    public function deactivate() {
        // Clean up
        flush_rewrite_rules();
    }
}

// Initialize plugin
AI_Blog_Writer::get_instance();