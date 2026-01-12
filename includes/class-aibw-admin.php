<?php
/**
 * Admin Interface for AI Blog Writer
 */

if (!defined('ABSPATH')) {
    exit;
}

class AIBW_Admin {
    
    private $settings;
    private $post_generator;
    
    public function __construct($settings, $post_generator) {
        $this->settings = $settings;
        $this->post_generator = $post_generator;
        
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        add_action('wp_ajax_aibw_generate_post', array($this, 'ajax_generate_post'));
        add_action('wp_ajax_aibw_test_apis', array($this, 'ajax_test_apis'));
        add_action('wp_ajax_aibw_generate_batch', array($this, 'ajax_generate_batch'));
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_menu_page(
            'AI Blog Writer',
            'AI Blog Writer',
            'manage_options',
            'ai-blog-writer',
            array($this, 'render_main_page'),
            'dashicons-admin-site-alt3',
            30
        );
        
        add_submenu_page(
            'ai-blog-writer',
            'Settings',
            'Settings',
            'manage_options',
            'ai-blog-writer-settings',
            array($this, 'render_settings_page')
        );
        
        add_submenu_page(
            'ai-blog-writer',
            'Generate Post',
            'Generate Post',
            'manage_options',
            'ai-blog-writer-generate',
            array($this, 'render_generate_page')
        );
        
        add_submenu_page(
            'ai-blog-writer',
            'Batch Generator',
            'Batch Generator',
            'manage_options',
            'ai-blog-writer-batch',
            array($this, 'render_batch_page')
        );
    }
    
    /**
     * Register settings
     */
    public function register_settings() {
        register_setting(
            'aibw_settings_group',
            'aibw_settings',
            array($this->settings, 'sanitize_settings')
        );
        
        add_settings_section(
            'aibw_api_section',
            'API Configuration',
            array($this, 'api_section_callback'),
            'ai-blog-writer-settings'
        );
        
        add_settings_section(
            'aibw_content_section',
            'Content Settings',
            array($this, 'content_section_callback'),
            'ai-blog-writer-settings'
        );
        
        // API fields
        add_settings_field(
            'openrouter_api_key',
            'OpenRouter API Key',
            array($this, 'render_api_key_field'),
            'ai-blog-writer-settings',
            'aibw_api_section',
            array('key' => 'openrouter_api_key', 'label' => 'OpenRouter')
        );
        
        add_settings_field(
            'perplexity_api_key',
            'Perplexity API Key',
            array($this, 'render_api_key_field'),
            'ai-blog-writer-settings',
            'aibw_api_section',
            array('key' => 'perplexity_api_key', 'label' => 'Perplexity')
        );
        
        add_settings_field(
            'default_model',
            'Default Model',
            array($this, 'render_model_field'),
            'ai-blog-writer-settings',
            'aibw_api_section'
        );
        
        // Content fields
        add_settings_field(
            'temperature',
            'Temperature (0.1-2.0)',
            array($this, 'render_temperature_field'),
            'ai-blog-writer-settings',
            'aibw_content_section'
        );
        
        add_settings_field(
            'max_tokens',
            'Max Tokens (100-4000)',
            array($this, 'render_max_tokens_field'),
            'ai-blog-writer-settings',
            'aibw_content_section'
        );
        
        add_settings_field(
            'post_category',
            'Default Category',
            array($this, 'render_category_field'),
            'ai-blog-writer-settings',
            'aibw_content_section'
        );
        
        add_settings_field(
            'post_status',
            'Default Post Status',
            array($this, 'render_status_field'),
            'ai-blog-writer-settings',
            'aibw_content_section'
        );
    }
    
    /**
     * Enqueue admin scripts
     */
    public function enqueue_admin_scripts($hook) {
        if (strpos($hook, 'ai-blog-writer') === false) {
            return;
        }
        
        wp_enqueue_script(
            'aibw-admin-js',
            AIBW_PLUGIN_URL . 'assets/js/admin.js',
            array('jquery'),
            AIBW_VERSION,
            true
        );
        
        wp_enqueue_style(
            'aibw-admin-css',
            AIBW_PLUGIN_URL . 'assets/css/admin.css',
            array(),
            AIBW_VERSION
        );
        
        // Localize script for AJAX
        wp_localize_script('aibw-admin-js', 'aibw_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('aibw_nonce')
        ));
    }
    
    /**
     * Render main page
     */
    public function render_main_page() {
        ?>
        <div class="wrap">
            <h1>AI Blog Writer</h1>
            <p>Welcome to AI Blog Writer! Generate high-quality blog posts using OpenRouter and Perplexity APIs.</p>
            
            <div class="card" style="max-width: 600px; margin-top: 20px;">
                <h2>Quick Actions</h2>
                <div style="display: flex; gap: 10px; flex-wrap: wrap; margin-top: 15px;">
                    <a href="<?php echo admin_url('admin.php?page=ai-blog-writer-generate'); ?>" class="button button-primary">Generate Single Post</a>
                    <a href="<?php echo admin_url('admin.php?page=ai-blog-writer-batch'); ?>" class="button button-secondary">Batch Generate</a>
                    <a href="<?php echo admin_url('admin.php?page=ai-blog-writer-settings'); ?>" class="button">Settings</a>
                </div>
            </div>
            
            <div class="card" style="max-width: 600px; margin-top: 20px;">
                <h2>API Status</h2>
                <div id="api-status">
                    <p>Click below to test your API connections:</p>
                    <button type="button" id="test-apis-btn" class="button button-primary">Test All APIs</button>
                    <div id="api-results" style="margin-top: 10px;"></div>
                </div>
            </div>
            
            <div style="margin-top: 20px; max-width: 600px;">
                <h3>How to Use</h3>
                <ol>
                    <li>Go to <strong>Settings</strong> and configure your API keys</li>
                    <li>Choose your preferred AI models and content settings</li>
                    <li>Use <strong>Generate Post</strong> to create individual posts</li>
                    <li>Use <strong>Batch Generator</strong> to create multiple posts at once</li>
                </ol>
            </div>
        </div>
        <?php
    }
    
    /**
     * Render settings page
     */
    public function render_settings_page() {
        ?>
        <div class="wrap">
            <h1>AI Blog Writer Settings</h1>
            
            <form method="post" action="options.php">
                <?php
                settings_fields('aibw_settings_group');
                do_settings_sections('ai-blog-writer-settings');
                submit_button('Save Settings');
                ?>
            </form>
            
            <div style="margin-top: 20px;">
                <form method="post" action="">
                    <?php wp_nonce_field('aibw_reset_settings', 'aibw_nonce'); ?>
                    <input type="hidden" name="aibw_action" value="reset_settings">
                    <button type="submit" class="button button-secondary" onclick="return confirm('Are you sure you want to reset all settings to defaults?');">Reset to Defaults</button>
                </form>
            </div>
        </div>
        <?php
    }
    
    /**
     * Render generate page
     */
    public function render_generate_page() {
        $categories = get_categories(array('hide_empty' => false));
        ?>
        <div class="wrap">
            <h1>Generate Blog Post</h1>
            <p>Enter a topic and let AI generate a complete blog post for you.</p>
            
            <div class="card" style="max-width: 800px; margin-top: 20px;">
                <form id="generate-form">
                    <?php wp_nonce_field('aibw_generate_post', 'aibw_nonce'); ?>
                    
                    <div style="margin-bottom: 15px;">
                        <label for="post_topic"><strong>Topic:</strong></label>
                        <input type="text" id="post_topic" name="post_topic" required 
                               style="width: 100%; margin-top: 5px; padding: 8px;"
                               placeholder="e.g., The Future of Artificial Intelligence in Healthcare">
                    </div>
                    
                    <div style="margin-bottom: 15px;">
                        <label for="use_research">
                            <input type="checkbox" id="use_research" name="use_research" checked>
                            Use Perplexity for research (recommended for current topics)
                        </label>
                    </div>
                    
                    <div style="margin-bottom: 15px;">
                        <label for="api_choice"><strong>Primary API:</strong></label>
                        <select id="api_choice" name="api_choice" style="margin-top: 5px; padding: 8px;">
                            <option value="auto">Auto (Recommended)</option>
                            <option value="openrouter">OpenRouter Only</option>
                            <option value="perplexity">Perplexity Only</option>
                        </select>
                    </div>
                    
                    <div style="margin-bottom: 15px;">
                        <label for="target_category"><strong>Category:</strong></label>
                        <select id="target_category" name="target_category" style="margin-top: 5px; padding: 8px;">
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo $cat->term_id; ?>" <?php selected($this->settings->get_option('post_category', 1), $cat->term_id); ?>>
                                    <?php echo esc_html($cat->name); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <button type="submit" class="button button-primary" id="generate-btn">Generate Post</button>
                    <span id="generating-indicator" style="display: none; margin-left: 10px;">Generating...</span>
                </form>
                
                <div id="generation-result" style="margin-top: 20px; display: none;">
                    <h3>Result:</h3>
                    <div id="result-content"></div>
                </div>
            </div>
        </div>
        <?php
    }
    
    /**
     * Render batch page
     */
    public function render_batch_page() {
        ?>
        <div class="wrap">
            <h1>Batch Post Generator</h1>
            <p>Generate multiple blog posts at once. Enter one topic per line.</p>
            
            <div class="card" style="max-width: 800px; margin-top: 20px;">
                <form id="batch-form">
                    <?php wp_nonce_field('aibw_generate_batch', 'aibw_nonce'); ?>
                    
                    <div style="margin-bottom: 15px;">
                        <label for="batch_topics"><strong>Topics (one per line):</strong></label>
                        <textarea id="batch_topics" name="batch_topics" required 
                                  style="width: 100%; height: 200px; margin-top: 5px; padding: 8px;"
                                  placeholder="Topic 1
Topic 2
Topic 3"></textarea>
                    </div>
                    
                    <div style="margin-bottom: 15px;">
                        <label for="batch_research">
                            <input type="checkbox" id="batch_research" name="batch_research" checked>
                            Use Perplexity for research
                        </label>
                    </div>
                    
                    <div style="margin-bottom: 15px;">
                        <label for="batch_delay"><strong>Delay between posts (seconds):</strong></label>
                        <input type="number" id="batch_delay" name="batch_delay" value="2" min="1" max="10" 
                               style="width: 100px; margin-top: 5px; padding: 8px;">
                    </div>
                    
                    <button type="submit" class="button button-primary" id="batch-generate-btn">Generate Batch</button>
                    <span id="batch-indicator" style="display: none; margin-left: 10px;">Processing...</span>
                </form>
                
                <div id="batch-results" style="margin-top: 20px; display: none;">
                    <h3>Results:</h3>
                    <div id="batch-result-content"></div>
                </div>
            </div>
        </div>
        <?php
    }
    
    /**
     * Field rendering callbacks
     */
    public function render_api_key_field($args) {
        $key = $args['key'];
        $label = $args['label'];
        $value = $this->settings->get_option($key);
        
        echo '<input type="password" name="aibw_settings[' . esc_attr($key) . ']" value="' . esc_attr($value) . '" style="width: 300px; padding: 5px;">';
        echo '<p class="description">Enter your ' . esc_html($label) . ' API key</p>';
    }
    
    public function render_model_field() {
        $value = $this->settings->get_option('default_model', 'openrouter/meta-llama/llama-3.1-8b-instruct');
        echo '<select name="aibw_settings[default_model]" style="padding: 5px;">';
        
        $models = array(
            'openrouter/meta-llama/llama-3.1-8b-instruct' => 'Llama 3.1 8B (OpenRouter)',
            'openrouter/meta-llama/llama-3.1-70b-instruct' => 'Llama 3.1 70B (OpenRouter)',
            'openrouter/openai/gpt-4o' => 'GPT-4o (OpenRouter)',
            'openrouter/anthropic/claude-3-sonnet' => 'Claude 3 Sonnet (OpenRouter)',
            'llama-3.1-8b-instruct' => 'Llama 3.1 8B (Perplexity)'
        );
        
        foreach ($models as $model_val => $model_label) {
            echo '<option value="' . esc_attr($model_val) . '" ' . selected($value, $model_val, false) . '>' . esc_html($model_label) . '</option>';
        }
        
        echo '</select>';
    }
    
    public function render_temperature_field() {
        $value = $this->settings->get_option('temperature', 0.7);
        echo '<input type="number" name="aibw_settings[temperature]" value="' . esc_attr($value) . '" step="0.1" min="0.1" max="2.0" style="width: 100px; padding: 5px;">';
        echo '<p class="description">Higher = more creative, Lower = more focused</p>';
    }
    
    public function render_max_tokens_field() {
        $value = $this->settings->get_option('max_tokens', 2000);
        echo '<input type="number" name="aibw_settings[max_tokens]" value="' . esc_attr($value) . '" min="100" max="4000" style="width: 100px; padding: 5px;">';
    }
    
    public function render_category_field() {
        $value = $this->settings->get_option('post_category', 1);
        $categories = get_categories(array('hide_empty' => false));
        
        echo '<select name="aibw_settings[post_category]" style="padding: 5px;">';
        foreach ($categories as $cat) {
            echo '<option value="' . esc_attr($cat->term_id) . '" ' . selected($value, $cat->term_id, false) . '>' . esc_html($cat->name) . '</option>';
        }
        echo '</select>';
    }
    
    public function render_status_field() {
        $value = $this->settings->get_option('post_status', 'draft');
        echo '<select name="aibw_settings[post_status]" style="padding: 5px;">';
        echo '<option value="draft" ' . selected($value, 'draft', false) . '>Draft</option>';
        echo '<option value="pending" ' . selected($value, 'pending', false) . '>Pending Review</option>';
        echo '<option value="publish" ' . selected($value, 'publish', false) . '>Publish Immediately</option>';
        echo '</select>';
    }
    
    public function api_section_callback() {
        echo '<p>Configure your API keys for OpenRouter and Perplexity. You can get these from their respective websites.</p>';
    }
    
    public function content_section_callback() {
        echo '<p>Configure how your AI-generated content should be created.</p>';
    }
    
    /**
     * AJAX: Generate single post
     */
    public function ajax_generate_post() {
        check_ajax_referer('aibw_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permission denied');
        }
        
        $topic = sanitize_text_field($_POST['topic'] ?? '');
        $use_research = isset($_POST['use_research']) && $_POST['use_research'] === 'true';
        $api_choice = sanitize_text_field($_POST['api_choice'] ?? 'auto');
        
        if (empty($topic)) {
            wp_send_json_error('Topic is required');
        }
        
        if ($api_choice === 'auto') {
            $result = $this->post_generator->generate_post($topic, $use_research);
        } else {
            $result = $this->post_generator->generate_with_api($topic, $api_choice);
        }
        
        if (is_wp_error($result)) {
            wp_send_json_error($result->get_error_message());
        }
        
        $post_url = get_permalink($result);
        
        wp_send_json_success(array(
            'post_id' => $result,
            'post_url' => $post_url,
            'message' => 'Post generated successfully!'
        ));
    }
    
    /**
     * AJAX: Test APIs
     */
    public function ajax_test_apis() {
        check_ajax_referer('aibw_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permission denied');
        }
        
        $api_handler = new AIBW_API_Handler($this->settings);
        
        $results = array();
        
        // Test OpenRouter
        $openrouter_result = $api_handler->test_openrouter_connection();
        $results['openrouter'] = array(
            'success' => !is_wp_error($openrouter_result),
            'message' => is_wp_error($openrouter_result) ? $openrouter_result->get_error_message() : 'Connected successfully'
        );
        
        // Test Perplexity
        $perplexity_result = $api_handler->test_perplexity_connection();
        $results['perplexity'] = array(
            'success' => !is_wp_error($perplexity_result),
            'message' => is_wp_error($perplexity_result) ? $perplexity_result->get_error_message() : 'Connected successfully'
        );
        
        wp_send_json_success($results);
    }
    
    /**
     * AJAX: Batch generation
     */
    public function ajax_generate_batch() {
        check_ajax_referer('aibw_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permission denied');
        }
        
        $topics_text = sanitize_textarea_field($_POST['topics'] ?? '');
        $use_research = isset($_POST['use_research']) && $_POST['use_research'] === 'true';
        $delay = intval($_POST['delay'] ?? 2);
        
        if (empty($topics_text)) {
            wp_send_json_error('Topics are required');
        }
        
        $topics = array_filter(array_map('trim', explode("\n", $topics_text)));
        
        if (empty($topics)) {
            wp_send_json_error('No valid topics found');
        }
        
        $results = array();
        
        foreach ($topics as $index => $topic) {
            $result = $this->post_generator->generate_post($topic, $use_research);
            
            if (is_wp_error($result)) {
                $results[] = array(
                    'topic' => $topic,
                    'success' => false,
                    'error' => $result->get_error_message()
                );
            } else {
                $results[] = array(
                    'topic' => $topic,
                    'success' => true,
                    'post_id' => $result,
                    'post_url' => get_permalink($result)
                );
            }
            
            // Delay between requests
            if ($index < count($topics) - 1) {
                sleep($delay);
            }
        }
        
        wp_send_json_success($results);
    }
    
    /**
     * Handle settings reset
     */
    public function handle_settings_reset() {
        if (isset($_POST['aibw_action']) && $_POST['aibw_action'] === 'reset_settings') {
            if (!isset($_POST['aibw_nonce']) || !wp_verify_nonce($_POST['aibw_nonce'], 'aibw_reset_settings')) {
                wp_die('Security check failed');
            }
            
            if (!current_user_can('manage_options')) {
                wp_die('Permission denied');
            }
            
            $this->settings->reset_to_defaults();
            
            wp_redirect(admin_url('admin.php?page=ai-blog-writer-settings&reset=1'));
            exit;
        }
    }
}

// Add handler for settings reset
add_action('admin_init', function() {
    if (isset($_POST['aibw_action']) && $_POST['aibw_action'] === 'reset_settings') {
        if (!isset($_POST['aibw_nonce']) || !wp_verify_nonce($_POST['aibw_nonce'], 'aibw_reset_settings')) {
            wp_die('Security check failed');
        }
        
        if (!current_user_can('manage_options')) {
            wp_die('Permission denied');
        }
        
        $settings = new AIBW_Settings();
        $settings->reset_to_defaults();
        
        wp_redirect(admin_url('admin.php?page=ai-blog-writer-settings&reset=1'));
        exit;
    }
});