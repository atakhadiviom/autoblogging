<?php
/**
 * Settings Manager for AI Blog Writer
 */

if (!defined('ABSPATH')) {
    exit;
}

class AIBW_Settings {
    
    private $option_name = 'aibw_settings';
    
    /**
     * Get a specific option
     */
    public function get_option($key, $default = '') {
        $options = get_option($this->option_name, array());
        
        if (isset($options[$key])) {
            return $options[$key];
        }
        
        return $default;
    }
    
    /**
     * Update a specific option
     */
    public function update_option($key, $value) {
        $options = get_option($this->option_name, array());
        $options[$key] = $value;
        
        return update_option($this->option_name, $options);
    }
    
    /**
     * Update multiple options
     */
    public function update_options($updates) {
        $options = get_option($this->option_name, array());
        $options = array_merge($options, $updates);
        
        return update_option($this->option_name, $options);
    }
    
    /**
     * Get all settings
     */
    public function get_all_settings() {
        return get_option($this->option_name, array());
    }
    
    /**
     * Validate API key format
     */
    public function validate_api_key($key) {
        if (empty(trim($key))) {
            return false;
        }
        
        // Basic validation - keys should be reasonably long
        if (strlen($key) < 10) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Sanitize settings before saving
     */
    public function sanitize_settings($input) {
        $sanitized = array();
        
        if (isset($input['openrouter_api_key'])) {
            $sanitized['openrouter_api_key'] = sanitize_text_field($input['openrouter_api_key']);
        }
        
        if (isset($input['perplexity_api_key'])) {
            $sanitized['perplexity_api_key'] = sanitize_text_field($input['perplexity_api_key']);
        }
        
        if (isset($input['default_model'])) {
            $sanitized['default_model'] = sanitize_text_field($input['default_model']);
        }
        
        if (isset($input['temperature'])) {
            $sanitized['temperature'] = floatval($input['temperature']);
            // Ensure temperature is between 0 and 2
            if ($sanitized['temperature'] < 0) $sanitized['temperature'] = 0;
            if ($sanitized['temperature'] > 2) $sanitized['temperature'] = 2;
        }
        
        if (isset($input['max_tokens'])) {
            $sanitized['max_tokens'] = intval($input['max_tokens']);
            // Ensure max_tokens is reasonable
            if ($sanitized['max_tokens'] < 100) $sanitized['max_tokens'] = 100;
            if ($sanitized['max_tokens'] > 4000) $sanitized['max_tokens'] = 4000;
        }
        
        if (isset($input['post_category'])) {
            $sanitized['post_category'] = intval($input['post_category']);
        }
        
        if (isset($input['post_status'])) {
            $sanitized['post_status'] = in_array($input['post_status'], array('draft', 'publish', 'pending')) ? $input['post_status'] : 'draft';
        }
        
        return $sanitized;
    }
    
    /**
     * Get default settings
     */
    public function get_defaults() {
        return array(
            'openrouter_api_key' => '',
            'perplexity_api_key' => '',
            'default_model' => 'openrouter/meta-llama/llama-3.1-8b-instruct',
            'temperature' => 0.7,
            'max_tokens' => 2000,
            'post_category' => 1,
            'post_status' => 'draft'
        );
    }
    
    /**
     * Reset settings to defaults
     */
    public function reset_to_defaults() {
        $defaults = $this->get_defaults();
        return update_option($this->option_name, $defaults);
    }
}