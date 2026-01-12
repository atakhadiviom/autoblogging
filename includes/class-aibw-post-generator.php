<?php
/**
 * Post Generator Class
 * Handles blog post generation using AI APIs
 */

if (!defined('ABSPATH')) {
    exit;
}

class AIBW_Post_Generator {
    
    private $api_handler;
    
    public function __construct($api_handler) {
        $this->api_handler = $api_handler;
    }
    
    /**
     * Generate a complete blog post
     */
    public function generate_post($topic, $keywords = array(), $tone = 'professional') {
        // Step 1: Research with Perplexity
        $research = $this->research_topic($topic);
        if (is_wp_error($research)) {
            return $research;
        }
        
        // Step 2: Create outline with OpenRouter
        $outline = $this->create_outline($topic, $research, $keywords);
        if (is_wp_error($outline)) {
            return $outline;
        }
        
        // Step 3: Generate content with OpenRouter
        $content = $this->write_content($topic, $outline, $research, $tone);
        if (is_wp_error($content)) {
            return $content;
        }
        
        // Step 4: Format for WordPress
        $formatted_content = $this->format_for_wordpress($content, $topic);
        
        return array(
            'title' => $this->generate_title($topic, $keywords),
            'content' => $formatted_content,
            'research' => $research,
            'outline' => $outline
        );
    }
    
    /**
     * Research topic using Perplexity
     */
    private function research_topic($topic) {
        $prompt = "Research the topic: {$topic}. Provide key facts, statistics, trends, and important points to cover. Be concise but comprehensive.";
        
        return $this->api_handler->research_with_perplexity($prompt);
    }
    
    /**
     * Create content outline
     */
    private function create_outline($topic, $research, $keywords) {
        $prompt = "Create a detailed outline for a blog post about: {$topic}\n\n";
        $prompt .= "Research findings: {$research}\n\n";
        
        if (!empty($keywords)) {
            $prompt .= "Keywords to include: " . implode(", ", $keywords) . "\n\n";
        }
        
        $prompt .= "Structure the outline with:\n";
        $prompt .= "1. Introduction (hook, thesis)\n";
        $prompt .= "2. Main sections (3-5 key points)\n";
        $prompt .= "3. Subsections for each main point\n";
        $prompt .= "4. Conclusion (summary, call-to-action)\n\n";
        $prompt .= "Format as a numbered list with clear hierarchy.";
        
        return $this->api_handler->generate_with_openrouter($prompt);
    }
    
    /**
     * Write the actual content
     */
    private function write_content($topic, $outline, $research, $tone) {
        $prompt = "Write a comprehensive blog post about: {$topic}\n\n";
        $prompt .= "Tone: {$tone}\n\n";
        $prompt .= "Outline to follow:\n{$outline}\n\n";
        $prompt .= "Research to incorporate:\n{$research}\n\n";
        $prompt .= "Requirements:\n";
        $prompt .= "- Write in first or second person\n";
        $prompt .= "- Include engaging introductions\n";
        $prompt .= "- Use subheadings\n";
        $prompt .= "- Add bullet points where appropriate\n";
        $prompt .= "- Include examples and actionable advice\n";
        $prompt .= "- End with a conclusion\n";
        $prompt .= "- Aim for 800-1500 words\n";
        
        return $this->api_handler->generate_with_openrouter($prompt);
    }
    
    /**
     * Generate title
     */
    private function generate_title($topic, $keywords) {
        $prompt = "Generate 3 compelling blog post titles for: {$topic}\n\n";
        
        if (!empty($keywords)) {
            $prompt .= "Keywords to include: " . implode(", ", $keywords) . "\n\n";
        }
        
        $prompt .= "Format each title on a new line. Make them SEO-friendly and click-worthy.";
        
        $result = $this->api_handler->generate_with_openrouter($prompt);
        
        if (is_wp_error($result)) {
            return $topic; // Fallback
        }
        
        $titles = explode("\n", $result);
        return trim($titles[0]) ?: $topic;
    }
    
    /**
     * Format content for WordPress
     */
    private function format_for_wordpress($content, $topic) {
        // Convert plain text to WordPress HTML
        $formatted = $content;
        
        // Convert double line breaks to paragraphs
        $formatted = preg_replace('/\n\n+/', '</p><p>', $formatted);
        $formatted = '<p>' . $formatted . '</p>';
        
        // Convert single line breaks to <br> within paragraphs
        $formatted = str_replace("\n", '<br>', $formatted);
        
        // Ensure proper heading structure
        $formatted = preg_replace('/^(\d+\.\s)(.+)$/m', '<h3>$2</h3>', $formatted);
        $formatted = preg_replace('/^##\s(.+)$/m', '<h2>$1</h2>', $formatted);
        $formatted = preg_replace('/^#\s(.+)$/m', '<h2>$1</h2>', $formatted);
        
        // Clean up any multiple <br> tags
        $formatted = preg_replace('/(<br>\s*)+/', '<br>', $formatted);
        
        return $formatted;
    }
    
    /**
     * Generate post with specific research focus
     */
    public function generate_researched_post($topic, $focus_areas = array()) {
        $research_prompt = "Research these specific aspects of {$topic}: " . implode(", ", $focus_areas);
        $research = $this->api_handler->research_with_perplexity($research_prompt);
        
        if (is_wp_error($research)) {
            return $research;
        }
        
        $content_prompt = "Write a detailed article about {$topic} focusing on: " . implode(", ", $focus_areas) . "\n\n";
        $content_prompt .= "Research: {$research}\n\n";
        $content_prompt .= "Include: definitions, examples, statistics, and actionable advice.";
        
        $content = $this->api_handler->generate_with_openrouter($content_prompt);
        
        if (is_wp_error($content)) {
            return $content;
        }
        
        return array(
            'title' => $this->generate_title($topic, $focus_areas),
            'content' => $this->format_for_wordpress($content, $topic),
            'research' => $research
        );
    }
    
    /**
     * Create WordPress post
     */
    public function create_wordpress_post($post_data, $status = 'draft') {
        $post_id = wp_insert_post(array(
            'post_title' => sanitize_text_field($post_data['title']),
            'post_content' => wp_kses_post($post_data['content']),
            'post_status' => $status,
            'post_author' => get_current_user_id(),
            'post_category' => array(1) // Default to uncategorized
        ));
        
        if (is_wp_error($post_id)) {
            return $post_id;
        }
        
        // Store metadata
        if (isset($post_data['research'])) {
            update_post_meta($post_id, '_aibw_research', sanitize_text_field($post_data['research']));
        }
        
        if (isset($post_data['outline'])) {
            update_post_meta($post_id, '_aibw_outline', sanitize_text_field($post_data['outline']));
        }
        
        update_post_meta($post_id, '_aibw_generated', true);
        update_post_meta($post_id, '_aibw_generated_date', current_time('mysql'));
        
        return $post_id;
    }
    
    /**
     * Generate multiple post ideas
     */
    public function generate_post_ideas($main_topic, $count = 5) {
        $prompt = "Generate {$count} blog post ideas around the main topic: {$main_topic}\n\n";
        $prompt .= "For each idea, provide:\n";
        $prompt .= "1. Title\n";
        $prompt .= "2. Brief description (1-2 sentences)\n";
        $prompt .= "3. Target keywords\n\n";
        $prompt .= "Format each idea with clear separation.";
        
        $result = $this->api_handler->generate_with_openrouter($prompt);
        
        if (is_wp_error($result)) {
            return array();
        }
        
        // Parse ideas
        $ideas = array();
        $sections = explode("\n\n", $result);
        
        foreach ($sections as $section) {
            if (strlen($section) > 20) {
                $ideas[] = trim($section);
            }
        }
        
        return array_slice($ideas, 0, $count);
    }
}