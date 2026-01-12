<?php
/**
 * API Handler for OpenRouter and Perplexity
 */

if (!defined('ABSPATH')) {
    exit;
}

class AIBW_API_Handler {
    
    private $settings;
    
    public function __construct($settings) {
        $this->settings = $settings;
    }
    
    /**
     * Generate content using OpenRouter API
     */
    public function generate_with_openrouter($prompt, $system_prompt = "You are a helpful assistant that writes engaging blog posts.") {
        $api_key = $this->settings->get_option('openrouter_api_key');
        
        if (empty($api_key)) {
            return new WP_Error('no_api_key', 'OpenRouter API key not configured');
        }
        
        $model = $this->settings->get_option('default_model', 'openrouter/meta-llama/llama-3.1-8b-instruct');
        $temperature = $this->settings->get_option('temperature', 0.7);
        $max_tokens = $this->settings->get_option('max_tokens', 2000);
        
        $payload = array(
            'model' => $model,
            'messages' => array(
                array(
                    'role' => 'system',
                    'content' => $system_prompt
                ),
                array(
                    'role' => 'user',
                    'content' => $prompt
                )
            ),
            'temperature' => $temperature,
            'max_tokens' => $max_tokens,
            'stream' => false
        );
        
        $response = $this->make_api_request(
            'https://openrouter.ai/api/v1/chat/completions',
            $api_key,
            $payload
        );
        
        if (is_wp_error($response)) {
            return $response;
        }
        
        if (isset($response['choices'][0]['message']['content'])) {
            return $response['choices'][0]['message']['content'];
        }
        
        return new WP_Error('api_error', 'Invalid response from OpenRouter API');
    }
    
    /**
     * Generate content using Perplexity API
     */
    public function generate_with_perplexity($prompt, $system_prompt = "You are a helpful assistant that provides accurate, up-to-date information.") {
        $api_key = $this->settings->get_option('perplexity_api_key');
        
        if (empty($api_key)) {
            return new WP_Error('no_api_key', 'Perplexity API key not configured');
        }
        
        // Perplexity uses similar format to OpenAI
        $model = 'llama-3.1-8b-instruct'; // Default Perplexity model
        $temperature = $this->settings->get_option('temperature', 0.7);
        $max_tokens = $this->settings->get_option('max_tokens', 2000);
        
        $payload = array(
            'model' => $model,
            'messages' => array(
                array(
                    'role' => 'system',
                    'content' => $system_prompt
                ),
                array(
                    'role' => 'user',
                    'content' => $prompt
                )
            ),
            'temperature' => $temperature,
            'max_tokens' => $max_tokens
        );
        
        $response = $this->make_api_request(
            'https://api.perplexity.ai/chat/completions',
            $api_key,
            $payload
        );
        
        if (is_wp_error($response)) {
            return $response;
        }
        
        if (isset($response['choices'][0]['message']['content'])) {
            return $response['choices'][0]['message']['content'];
        }
        
        return new WP_Error('api_error', 'Invalid response from Perplexity API');
    }
    
    /**
     * Research topic using Perplexity (for current information)
     */
    public function research_with_perplexity($topic) {
        $system_prompt = "You are a research assistant. Provide comprehensive, accurate, and up-to-date information about the given topic. Include key facts, statistics, and recent developments.";
        
        $prompt = "Research and provide detailed information about: {$topic}. Include relevant data, statistics, and current trends.";
        
        return $this->generate_with_perplexity($prompt, $system_prompt);
    }
    
    /**
     * Generate blog post using OpenRouter (for creative writing)
     */
    public function write_blog_post($topic, $research_data = '') {
        $system_prompt = "You are an expert blog writer. Write engaging, well-structured blog posts with a conversational tone. Use headings, paragraphs, and make the content SEO-friendly.";
        
        $prompt = "Write a comprehensive blog post about: {$topic}";
        
        if (!empty($research_data)) {
            $prompt .= "\n\nUse this research data as reference:\n" . $research_data;
        }
        
        $prompt .= "\n\nStructure the post with:\n- Introduction\n- Main body with subheadings\n- Conclusion\n- Call to action";
        
        return $this->generate_with_openrouter($prompt, $system_prompt);
    }
    
    /**
     * Make HTTP request to API
     */
    private function make_api_request($url, $api_key, $payload) {
        $args = array(
            'method' => 'POST',
            'headers' => array(
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $api_key,
                'HTTP-Referer' => home_url(),
                'X-Title' => 'AI Blog Writer WordPress Plugin'
            ),
            'body' => json_encode($payload),
            'timeout' => 60
        );
        
        $response = wp_remote_post($url, $args);
        
        if (is_wp_error($response)) {
            return $response;
        }
        
        $response_code = wp_remote_retrieve_response_code($response);
        $response_body = wp_remote_retrieve_body($response);
        
        if ($response_code !== 200) {
            return new WP_Error('api_error', "API request failed with code {$response_code}: {$response_body}");
        }
        
        $decoded_response = json_decode($response_body, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            return new WP_Error('json_error', 'Invalid JSON response from API');
        }
        
        return $decoded_response;
    }
    
    /**
     * Test API connections
     */
    public function test_openrouter_connection() {
        $test_payload = array(
            'model' => 'openrouter/meta-llama/llama-3.1-8b-instruct',
            'messages' => array(
                array('role' => 'user', 'content' => 'Hello')
            ),
            'max_tokens' => 5
        );
        
        $api_key = $this->settings->get_option('openrouter_api_key');
        
        if (empty($api_key)) {
            return new WP_Error('no_api_key', 'No API key configured');
        }
        
        $response = $this->make_api_request(
            'https://openrouter.ai/api/v1/chat/completions',
            $api_key,
            $test_payload
        );
        
        return !is_wp_error($response);
    }
    
    public function test_perplexity_connection() {
        $test_payload = array(
            'model' => 'llama-3.1-8b-instruct',
            'messages' => array(
                array('role' => 'user', 'content' => 'Hello')
            ),
            'max_tokens' => 5
        );
        
        $api_key = $this->settings->get_option('perplexity_api_key');
        
        if (empty($api_key)) {
            return new WP_Error('no_api_key', 'No API key configured');
        }
        
        $response = $this->make_api_request(
            'https://api.perplexity.ai/chat/completions',
            $api_key,
            $test_payload
        );
        
        return !is_wp_error($response);
    }
}