<?php
/**
 * Blog Post Generator
 */

if (!defined('ABSPATH')) {
    exit;
}

class AIBW_Post_Generator {
    
    private $api_handler;
    private $settings;
    
    public function __construct($api_handler, $settings) {
        $this->api_handler = $api_handler;
        $this->settings = $settings;
    }
    
    /**
     * Generate a complete blog post
     */
    public function generate_post($topic, $use_research = true) {
        // Validate topic
        if (empty(trim($topic))) {
            return new WP_Error('empty_topic', 'Topic cannot be empty');
        }
        
        $research_data = '';
        
        // Step 1: Research with Perplexity (if enabled)
        if ($use_research) {
            $research_result = $this->api_handler->research_with_perplexity($topic);
            
            if (!is_wp_error($research_result)) {
                $research_data = $research_result;
            } else {
                // Log error but continue without research
                error_log('AI Blog Writer: Research failed - ' . $research_result->get_error_message());
            }
        }
        
        // Step 2: Write blog post with OpenRouter
        $post_content = $this->api_handler->write_blog_post($topic, $research_data);
        
        if (is_wp_error($post_content)) {
            return $post_content;
        }
        
        // Step 3: Generate SEO title
        $seo_title = $this->generate_seo_title($topic);
        
        // Step 4: Generate excerpt
        $excerpt = $this->generate_excerpt($post_content);
        
        // Step 5: Create the post
        $post_id = $this->create_wordpress_post($topic, $post_content, $seo_title, $excerpt);
        
        return $post_id;
    }
    
    /**
     * Generate SEO-friendly title
     */
    private function generate_seo_title($topic) {
        $system_prompt = "Generate a compelling, SEO-friendly title for a blog post. Keep it under 60 characters. Make it catchy and include keywords.";
        
        $prompt = "Create a title for a blog post about: {$topic}";
        
        $title = $this->api_handler->generate_with_openrouter($prompt, $system_prompt);
        
        if (is_wp_error($title)) {
            // Fallback to simple title
            return substr($topic, 0, 60);
        }
        
        // Clean up the title
        $title = trim($title);
        $title = preg_replace('/^"(.*)"$/s', '$1', $title); // Remove quotes
        $title = substr($title, 0, 60);
        
        return $title;
    }
    
    /**
     * Generate excerpt from content
     */
    private function generate_excerpt($content) {
        $system_prompt = "Create a compelling 150-160 character excerpt that summarizes the main points and entices readers to click.";
        
        $prompt = "Summarize this content into a short excerpt:\n\n" . substr($content, 0, 1000);
        
        $excerpt = $this->api_handler->generate_with_openrouter($prompt, $system_prompt);
        
        if (is_wp_error($excerpt)) {
            // Fallback to manual excerpt
            $excerpt = substr(strip_tags($content), 0, 155) . '...';
        }
        
        // Clean up
        $excerpt = trim($excerpt);
        $excerpt = preg_replace('/^"(.*)"$/s', '$1', $excerpt);
        $excerpt = substr($excerpt, 0, 160);
        
        return $excerpt;
    }
    
    /**
     * Create WordPress post
     */
    private function create_wordpress_post($title, $content, $seo_title, $excerpt) {
        $category_id = $this->settings->get_option('post_category', 1);
        $post_status = $this->settings->get_option('post_status', 'draft');
        
        // Prepare post data
        $post_data = array(
            'post_title' => $seo_title,
            'post_content' => $content,
            'post_excerpt' => $excerpt,
            'post_status' => $post_status,
            'post_category' => array($category_id),
            'post_author' => get_current_user_id(),
            'post_type' => 'post'
        );
        
        // Add custom meta for tracking
        $post_id = wp_insert_post($post_data);
        
        if ($post_id && !is_wp_error($post_id)) {
            // Add meta data
            update_post_meta($post_id, '_aibw_generated', true);
            update_post_meta($post_id, '_aibw_topic', $topic);
            update_post_meta($post_id, '_aibw_generation_time', current_time('mysql'));
            update_post_meta($post_id, '_aibw_used_research', !empty($research_data));
            
            // Add AI disclaimer as HTML comment
            $ai_comment = "<!-- This post was generated using AI Blog Writer with OpenRouter and Perplexity APIs -->";
            wp_update_post(array(
                'ID' => $post_id,
                'post_content' => $ai_comment . "\n" . $content
            ));
        }
        
        return $post_id;
    }
    
    /**
     * Generate post from existing research
     */
    public function generate_from_research($topic, $research_text) {
        $post_content = $this->api_handler->write_blog_post($topic, $research_text);
        
        if (is_wp_error($post_content)) {
            return $post_content;
        }
        
        $seo_title = $this->generate_seo_title($topic);
        $excerpt = $this->generate_excerpt($post_content);
        
        return $this->create_wordpress_post($topic, $post_content, $seo_title, $excerpt);
    }
    
    /**
     * Generate multiple posts in batch
     */
    public function generate_batch($topics, $use_research = true) {
        $results = array();
        
        foreach ($topics as $topic) {
            $result = $this->generate_post($topic, $use_research);
            $results[] = array(
                'topic' => $topic,
                'result' => $result,
                'success' => !is_wp_error($result)
            );
            
            // Small delay to avoid rate limits
            sleep(1);
        }
        
        return $results;
    }
    
    /**
     * Generate post with specific API preference
     */
    public function generate_with_api($topic, $api = 'openrouter') {
        if ($api === 'perplexity') {
            // Use Perplexity for both research and writing
            $research = $this->api_handler->research_with_perplexity($topic);
            if (!is_wp_error($research)) {
                $content = $this->api_handler->generate_with_perplexity(
                    "Write a blog post about: {$topic}\n\nUse this research: {$research}"
                );
            } else {
                $content = $this->api_handler->generate_with_perplexity(
                    "Write a comprehensive blog post about: {$topic}"
                );
            }
        } else {
            // Default to OpenRouter
            return $this->generate_post($topic, true);
        }
        
        if (is_wp_error($content)) {
            return $content;
        }
        
        $seo_title = $this->generate_seo_title($topic);
        $excerpt = $this->generate_excerpt($content);
        
        return $this->create_wordpress_post($topic, $content, $seo_title, $excerpt);
    }
}