<?php
/**
 * Admin Interface Class
 * Handles plugin admin pages and AJAX functionality
 */

if (!defined('ABSPATH')) {
    exit;
}

class AIBW_Admin {
    
    private $api_handler;
    private $post_generator;
    private $pillar_analyzer;
    
    public function __construct($api_handler, $post_generator, $pillar_analyzer) {
        $this->api_handler = $api_handler;
        $this->post_generator = $post_generator;
        $this->pillar_analyzer = $pillar_analyzer;
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
            'dashicons-robot',
            85
        );
        
        add_submenu_page(
            'ai-blog-writer',
            'Generate Post',
            'Generate Post',
            'manage_options',
            'ai-blog-writer',
            array($this, 'render_main_page')
        );
        
        add_submenu_page(
            'ai-blog-writer',
            'Pillar Analysis',
            'Pillar Analysis',
            'manage_options',
            'ai-blog-writer-pillar',
            array($this, 'render_pillar_page')
        );
        
        add_submenu_page(
            'ai-blog-writer',
            'Settings',
            'Settings',
            'manage_options',
            'ai-blog-writer-settings',
            array($this, 'render_settings_page')
        );
    }
    
    /**
     * Enqueue admin scripts and styles
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
        
        // Pass AJAX data to JavaScript
        wp_localize_script('aibw-admin-js', 'aibw_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('aibw_nonce')
        ));
    }
    
    /**
     * Render main generation page
     */
    public function render_main_page() {
        if (!current_user_can('manage_options')) {
            wp_die('Insufficient permissions');
        }
        
        $settings = $this->api_handler->get_settings();
        $api_errors = $this->api_handler->validate_api_keys();
        ?>
        <div class="wrap">
            <h1>AI Blog Writer</h1>
            <?php if (is_array($api_errors)): ?>
                <div class="notice notice-error">
                    <p><strong>API Configuration Required:</strong></p>
                    <ul>
                        <?php foreach ($api_errors as $error): ?>
                            <li><?php echo esc_html($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                    <p>Please configure your API keys in the Settings tab.</p>
                </div>
            <?php endif; ?>
            
            <div class="card" style="max-width: 800px; margin-top: 20px;">
                <h2>Generate New Blog Post</h2>
                <form id="generate-post-form" style="margin-top: 15px;">
                    <table class="form-table">
                        <tr>
                            <th scope="row"><label for="post-topic">Topic</label></th>
                            <td>
                                <input type="text" id="post-topic" name="topic" class="large-text" required 
                                       placeholder="e.g., 'The Future of AI in Content Marketing'">
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="post-keywords">Keywords (comma-separated)</label></th>
                            <td>
                                <input type="text" id="post-keywords" name="keywords" class="large-text"
                                       placeholder="ai content, marketing automation, future trends">
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="post-tone">Tone</label></th>
                            <td>
                                <select id="post-tone" name="tone">
                                    <option value="professional">Professional</option>
                                    <option value="casual">Casual</option>
                                    <option value="enthusiastic">Enthusiastic</option>
                                    <option value="authoritative">Authoritative</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="post-status">Post Status</label></th>
                            <td>
                                <select id="post-status" name="status">
                                    <option value="draft">Draft</option>
                                    <option value="publish">Publish Immediately</option>
                                    <option value="pending">Pending Review</option>
                                </select>
                            </td>
                        </tr>
                    </table>
                    
                    <p class="submit">
                        <button type="submit" id="generate-btn" class="button button-primary button-large">
                            Generate Post
                        </button>
                        <span id="generate-loading" style="display: none; margin-left: 10px;">
                            Generating... (this may take 30-60 seconds)
                        </span>
                    </p>
                </form>
                
                <div id="generation-result" style="margin-top: 20px;"></div>
            </div>
            
            <div class="card" style="max-width: 800px; margin-top: 20px;">
                <h2>Generate Post Ideas</h2>
                <p>Get topic ideas for your content strategy</p>
                <form id="ideas-form" style="margin-top: 15px;">
                    <table class="form-table">
                        <tr>
                            <th scope="row"><label for="ideas-topic">Main Topic</label></th>
                            <td>
                                <input type="text" id="ideas-topic" class="large-text" required
                                       placeholder="e.g., 'Email Marketing'">
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="ideas-count">Number of Ideas</label></th>
                            <td>
                                <input type="number" id="ideas-count" value="5" min="3" max="10" style="width: 80px;">
                            </td>
                        </tr>
                    </table>
                    <p class="submit">
                        <button type="submit" id="ideas-btn" class="button button-secondary">
                            Generate Ideas
                        </button>
                    </p>
                </form>
                <div id="ideas-result" style="margin-top: 15px;"></div>
            </div>
        </div>
        <?php
    }
    
    /**
     * Render pillar analysis page
     */
    public function render_pillar_page() {
        if (!current_user_can('manage_options')) {
            wp_die('Insufficient permissions');
        }
        ?>
        <div class="wrap">
            <h1>Pillar Post Analysis</h1>
            <p>Analyze your existing posts to identify pillar content and get suggestions for related posts.</p>
            
            <div class="card" style="max-width: 800px; margin-top: 20px;">
                <h2>Analyze Single Post</h2>
                <div style="margin-top: 15px;">
                    <label for="analyze-post-id">Enter Post ID:</label>
                    <input type="number" id="analyze-post-id" min="1" style="width: 100px; margin: 0 10px;">
                    <button type="button" id="analyze-single-btn" class="button button-primary">Analyze Post</button>
                    <div id="single-analysis-result" style="margin-top: 15px;"></div>
                </div>
            </div>
            
            <div class="card" style="max-width: 800px; margin-top: 20px;">
                <h2>Get Related Post Suggestions</h2>
                <div style="margin-top: 15px;">
                    <label for="suggestion-post-id">Post ID:</label>
                    <input type="number" id="suggestion-post-id" min="1" style="width: 100px; margin: 0 10px;">
                    <label for="suggestion-limit">Limit:</label>
                    <input type="number" id="suggestion-limit" value="5" min="1" max="10" style="width: 60px; margin: 0 10px;">
                    <button type="button" id="get-suggestions-btn" class="button button-primary">Get Suggestions</button>
                    <div id="suggestions-result" style="margin-top: 15px;"></div>
                </div>
            </div>
            
            <div class="card" style="max-width: 800px; margin-top: 20px;">
                <h2>Find All Pillar Posts</h2>
                <p>Scan all published posts to identify pillar content</p>
                <button type="button" id="find-pillars-btn" class="button button-secondary">Find Pillar Posts</button>
                <div id="pillars-result" style="margin-top: 15px;"></div>
            </div>
            
            <div class="card" style="max-width: 800px; margin-top: 20px;">
                <h2>Bulk Analysis</h2>
                <p>Analyze multiple posts at once (comma-separated Post IDs)</p>
                <div style="margin-top: 15px;">
                    <input type="text" id="bulk-post-ids" class="large-text" placeholder="1, 5, 10, 15, 20">
                    <button type="button" id="bulk-analyze-btn" class="button button-secondary" style="margin-top: 10px;">Analyze Selected Posts</button>
                    <div id="bulk-result" style="margin-top: 15px;"></div>
                </div>
            </div>
        </div>
        <?php
    }
    
    /**
     * Render settings page
     */
    public function render_settings_page() {
        if (!current_user_can('manage_options')) {
            wp_die('Insufficient permissions');
        }
        
        $settings = $this->api_handler->get_settings();
        ?>
        <div class="wrap">
            <h1>AI Blog Writer Settings</h1>
            
            <div class="card" style="max-width: 800px; margin-top: 20px;">
                <h2>API Configuration</h2>
                <form id="settings-form" style="margin-top: 15px;">
                    <table class="form-table">
                        <tr>
                            <th scope="row"><label for="openrouter-key">OpenRouter API Key</label></th>
                            <td>
                                <input type="password" id="openrouter-key" name="openrouter_api_key" class="large-text" 
                                       value="<?php echo esc_attr($settings['openrouter_api_key']); ?>" 
                                       placeholder="sk-or-v1-...">
                                <p class="description">Get your key from <a href="https://openrouter.ai/keys" target="_blank">OpenRouter</a></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="perplexity-key">Perplexity API Key</label></th>
                            <td>
                                <input type="password" id="perplexity-key" name="perplexity_api_key" class="large-text" 
                                       value="<?php echo esc_attr($settings['perplexity_api_key']); ?>" 
                                       placeholder="pplx-...">
                                <p class="description">Get your key from <a href="https://www.perplexity.ai/settings" target="_blank">Perplexity</a></p>
                            </td>
                        </tr>
                    </table>
                    
                    <h3>Generation Settings</h3>
                    <table class="form-table">
                        <tr>
                            <th scope="row"><label for="default-model">Default Model (OpenRouter)</label></th>
                            <td>
                                <input type="text" id="default-model" name="default_model" class="large-text" 
                                       value="<?php echo esc_attr($settings['default_model']); ?>">
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="perplexity-model">Perplexity Model</label></th>
                            <td>
                                <input type="text" id="perplexity-model" name="perplexity_model" class="large-text" 
                                       value="<?php echo esc_attr($settings['perplexity_model']); ?>">
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="max-tokens">Max Tokens</label></th>
                            <td>
                                <input type="number" id="max-tokens" name="max_tokens" 
                                       value="<?php echo esc_attr($settings['max_tokens']); ?>" 
                                       style="width: 100px;">
                                <p class="description">Higher values allow longer responses (default: 2000)</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="temperature">Temperature</label></th>
                            <td>
                                <input type="number" id="temperature" name="temperature" step="0.1" min="0" max="2" 
                                       value="<?php echo esc_attr($settings['temperature']); ?>" 
                                       style="width: 100px;">
                                <p class="description">0.0-2.0 (higher = more creative, default: 0.7)</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="default-author">Default Author</label></th>
                            <td>
                                <input type="number" id="default-author" name="default_author" 
                                       value="<?php echo esc_attr($settings['default_author']); ?>" 
                                       style="width: 100px;">
                                <p class="description">User ID for generated posts (default: current user)</p>
                            </td>
                        </tr>
                    </table>
                    
                    <p class="submit">
                        <button type="submit" id="save-settings-btn" class="button button-primary button-large">
                            Save Settings
                        </button>
                        <span id="settings-loading" style="display: none; margin-left: 10px;">Saving...</span>
                    </p>
                </form>
                
                <div id="settings-result" style="margin-top: 15px;"></div>
            </div>
        </div>
        <?php
    }
    
    /**
     * AJAX: Generate post
     */
    public function ajax_generate_post() {
        check_ajax_referer('aibw_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        $topic = sanitize_text_field($_POST['topic']);
        $keywords = array_map('trim', explode(',', sanitize_text_field($_POST['keywords'])));
        $tone = sanitize_text_field($_POST['tone']);
        $status = sanitize_text_field($_POST['status']);
        
        if (empty($topic)) {
            wp_send_json_error('Topic is required');
        }
        
        // Generate post
        $result = $this->post_generator->generate_post($topic, $keywords, $tone);
        
        if (is_wp_error($result)) {
            wp_send_json_error($result->get_error_message());
        }
        
        // Create WordPress post
        $post_id = $this->post_generator->create_wordpress_post($result, $status);
        
        if (is_wp_error($post_id)) {
            wp_send_json_error($post_id->get_error_message());
        }
        
        $post_url = get_permalink($post_id);
        
        wp_send_json_success(array(
            'message' => 'Post generated successfully!',
            'post_id' => $post_id,
            'post_url' => $post_url,
            'title' => $result['title'],
            'preview' => substr(strip_tags($result['content']), 0, 200) . '...'
        ));
    }
    
    /**
     * AJAX: Save settings
     */
    public function ajax_save_settings() {
        check_ajax_referer('aibw_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        $settings = array(
            'openrouter_api_key' => sanitize_text_field($_POST['openrouter_api_key']),
            'perplexity_api_key' => sanitize_text_field($_POST['perplexity_api_key']),
            'default_model' => sanitize_text_field($_POST['default_model']),
            'perplexity_model' => sanitize_text_field($_POST['perplexity_model']),
            'max_tokens' => intval($_POST['max_tokens']),
            'temperature' => floatval($_POST['temperature']),
            'default_author' => intval($_POST['default_author'])
        );
        
        $result = $this->api_handler->update_settings($settings);
        
        if ($result) {
            wp_send_json_success('Settings saved successfully!');
        } else {
            wp_send_json_error('Failed to save settings');
        }
    }
    
    /**
     * AJAX: Analyze single post for pillar potential
     */
    public function ajax_analyze_pillar() {
        check_ajax_referer('aibw_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        $post_id = intval($_POST['post_id']);
        
        if (!$post_id) {
            wp_send_json_error('Post ID is required');
        }
        
        $analysis = $this->pillar_analyzer->analyze_pillar_potential($post_id);
        
        if (is_wp_error($analysis)) {
            wp_send_json_error($analysis->get_error_message());
        }
        
        wp_send_json_success($analysis);
    }
    
    /**
     * AJAX: Get related post suggestions
     */
    public function ajax_get_suggestions() {
        check_ajax_referer('aibw_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        $post_id = intval($_POST['post_id']);
        $limit = isset($_POST['limit']) ? intval($_POST['limit']) : 5;
        
        if (!$post_id) {
            wp_send_json_error('Post ID is required');
        }
        
        $suggestions = $this->pillar_analyzer->get_related_suggestions($post_id, $limit);
        
        if (is_wp_error($suggestions)) {
            wp_send_json_error($suggestions->get_error_message());
        }
        
        wp_send_json_success($suggestions);
    }
    
    /**
     * AJAX: Find all pillar posts
     */
    public function ajax_find_pillars() {
        check_ajax_referer('aibw_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        $pillars = $this->pillar_analyzer->find_pillar_posts(10);
        
        wp_send_json_success($pillars);
    }
    
    /**
     * AJAX: Bulk analyze posts
     */
    public function ajax_bulk_analyze() {
        check_ajax_referer('aibw_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        $post_ids_str = sanitize_text_field($_POST['post_ids']);
        $post_ids = array_map('intval', explode(',', $post_ids_str));
        $post_ids = array_filter($post_ids); // Remove zeros
        
        if (empty($post_ids)) {
            wp_send_json_error('No valid Post IDs provided');
        }
        
        $results = $this->pillar_analyzer->bulk_analyze($post_ids);
        
        wp_send_json_success($results);
    }
}