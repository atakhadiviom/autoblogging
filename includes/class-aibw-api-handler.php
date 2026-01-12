<?php
/**
 * API Handler Class
 * Handles communication with OpenRouter and Perplexity APIs
 */

if (!defined('ABSPATH')) {
    exit;
}

class AIBW_API_Handler {
    
    private $settings;
    
    public function __construct() {
        $this->settings = get_option('aibw_settings', array());
    }
    
    /**
     * Generate content using OpenRouter API
     */
    public function generate_with_openrouter($prompt, $model = null) {
        if (empty($this->settings['openrouter_api_key'])) {
            return new WP_Error('no_api_key', 'OpenRouter API key not configured');
        }
        
        $api_key = $this->settings['openrouter_api_key'];
        $model = $model ?: $this->settings['default_model'];
        
        $payload = array(
            'model' => $model,
            'prompt' => $prompt,
            'max_tokens' => intval($this->settings['max_tokens'] ?: 2000),
            'temperature' => floatval($this->settings['temperature'] ?: 0.7)
        );
        
        $response = wp_remote_post('https://openrouter.ai/api/v1/completions', array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $api_key,
                'Content-Type' => 'application/json',
                'HTTP-Referer' => home_url(),
                'X-Title' => 'AI Blog Writer'
            ),
            'body' => json_encode($payload),
            'timeout' => 60
        ));
        
        if (is_wp_error($response)) {
            return $response;
        }
        
        $body = json_decode(wp_remote_retrieve_body($response), true);
        
        if (isset($body['error'])) {
            return new WP_Error('api_error', $body['error']['message']);
        }
        
        if (isset($body['choices'][0]['text'])) {
            return $body['choices'][0]['text'];
        }
        
        return new WP_Error('parse_error', 'Could not parse API response');
    }
    
    /**
     * Research using Perplexity API
     */
    public function research_with_perplexity($query, $model = null) {
        if (empty($this->settings['perplexity_api_key'])) {
            return new WP_Error('no_api_key', 'Perplexity API key not configured');
        }
        
        $api_key = $this->settings['perplexity_api_key'];
        $model = $model ?: $this->settings['perplexity_model'];
        
        $payload = array(
            'model' => $model,
            'query' => $query
        );
        
        $response = wp_remote_post('https://api.perplexity.ai/chat/completions', array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $api_key,
                'Content-Type' => 'application/json'
            ),
            'body' => json_encode($payload),
            'timeout' => 60
        ));
        
        if (is_wp_error($response)) {
            return $response;
        }
        
        $body = json_decode(wp_remote_retrieve_body($response), true);
        
        if (isset($body['error'])) {
            return new WP_Error('api_error', $body['error']['message']);
        }
        
        if (isset($body['choices'][0]['message']['content'])) {
            return $body['choices'][0]['message']['content'];
        }
        
        return new WP_Error('parse_error', 'Could not parse API response');
    }
    
    /**
     * Analyze comprehensiveness using OpenRouter
     */
    public function analyze_comprehensiveness($content) {
        $prompt = "Analyze the following content for comprehensiveness. Rate it on a scale of 0-100 based on depth, coverage, and detail. Provide a brief explanation.\n\nContent: " . substr($content, 0, 2000);
        
        $result = $this->generate_with_openrouter($prompt);
        
        if (is_wp_error($result)) {
            return array('score' => 0, 'explanation' => 'Analysis failed');
        }
        
        // Extract score from response
        preg_match('/(\d+)/', $result, $matches);
        $score = isset($matches[1]) ? intval($matches[1]) : 50;
        
        return array(
            'score' => min($score, 100),
            'explanation' => $result
        );
    }
    
    /**
     * Generate related topic suggestions using OpenRouter
     */
    public function generate_related_topics($main_topic, $existing_topics = array()) {
        $prompt = "Generate 5-8 related blog post topics that would complement the main topic: '{$main_topic}'.\n";
        
        if (!empty($existing_topics)) {
            $prompt .= "Avoid these existing topics: " . implode(", ", $existing_topics) . "\n";
        }
        
        $prompt .= "Format each topic as a single line. Focus on subtopics, related concepts, and complementary angles.";
        
        $result = $this->generate_with_openrouter($prompt);
        
        if (is_wp_error($result)) {
            return array();
        }
        
        // Parse topics from response
        $topics = array();
        $lines = explode("\n", $result);
        
        foreach ($lines as $line) {
            $line = trim($line);
            if (!empty($line) && strlen($line) > 5) {
                // Remove numbering
                $line = preg_replace('/^\d+[\.\)]\s*/', '', $line);
                $topics[] = $line;
            }
        }
        
        return array_slice($topics, 0, 8);
    }
    
    /**
     * Validate API keys
     */
    public function validate_api_keys() {
        $errors = array();
        
        if (empty($this->settings['openrouter_api_key'])) {
            $errors[] = 'OpenRouter API key is missing';
        }
        
        if (empty($this->settings['perplexity_api_key'])) {
            $errors[] = 'Perplexity API key is missing';
        }
        
        return empty($errors) ? true : $errors;
    }
    
    /**
     * Update settings
     */
    public function update_settings($new_settings) {
        $this->settings = array_merge($this->settings, $new_settings);
        update_option('aibw_settings', $this->settings);
        return true;
    }
    
    /**
     * Get current settings
     */
    public function get_settings() {
        return $this->settings;
    }
}