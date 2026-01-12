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
    
    /**
     * Generate SEO-optimized content using comprehensive prompt template
     * This uses the advanced prompt structure for RankMath optimization
     */
    public function generate_seo_content($topic, $focus_keyword, $secondary_keywords = array()) {
        // Step 1: Research with Perplexity (top 10 ranking pages analysis)
        $research_prompt = "Research the topic: {$topic}\n\n";
        $research_prompt .= "Focus keyword: {$focus_keyword}\n\n";
        $research_prompt .= "Analyze the top 10 ranking pages for this focus keyword. Provide:\n";
        $research_prompt .= "1. Key facts, statistics, and trends\n";
        $research_prompt .= "2. Content structure patterns\n";
        $research_prompt .= "3. User intent satisfaction approaches\n";
        $research_prompt .= "4. Important points and unique insights\n";
        $research_prompt .= "5. Semantic keywords and LSI terms used\n\n";
        $research_prompt .= "Be comprehensive but concise.";
        
        $research = $this->api_handler->research_with_perplexity($research_prompt);
        if (is_wp_error($research)) {
            return $research;
        }
        
        // Step 2: Generate SEO-optimized title
        $title_prompt = "Generate 3 SEO-optimized titles for: {$topic}\n\n";
        $title_prompt .= "Focus keyword: {$focus_keyword}\n\n";
        $title_prompt .= "Requirements:\n";
        $title_prompt .= "- Must start with the focus keyword (within first 50 characters)\n";
        $title_prompt .= "- Include a power word (e.g., ultimate, essential, proven, complete)\n";
        $title_prompt .= "- Include a number (e.g., 7, 10, 15)\n";
        $title_prompt .= "- Evoke positive or negative sentiment\n";
        $title_prompt .= "- Under 60 characters total\n";
        $title_prompt .= "- Click-worthy and SEO-friendly\n\n";
        $title_prompt .= "Return only the best title as a single line.";
        
        $title_result = $this->api_handler->generate_with_openrouter($title_prompt);
        $title = is_wp_error($title_result) ? $topic : trim($title_result);
        
        // Step 3: Create detailed outline with 15+ headings
        $outline_prompt = "Create a comprehensive content outline for: {$topic}\n\n";
        $outline_prompt .= "Focus keyword: {$focus_keyword}\n";
        $outline_prompt .= "Secondary keywords: " . implode(", ", $secondary_keywords) . "\n\n";
        $outline_prompt .= "Research findings: {$research}\n\n";
        $outline_prompt .= "Requirements:\n";
        $outline_prompt .= "- Use proper HTML heading hierarchy (H1, H2, H3, H4)\n";
        $outline_prompt .= "- Create at least 15 headings total\n";
        $outline_prompt .= "- Include unique IDs for anchor links (e.g., id=\"section-name\")\n";
        $outline_prompt .= "- Ensure logical progression and topic coverage\n";
        $outline_prompt .= "- Include at least one H2/H3 with the focus keyword naturally\n";
        $outline_prompt .= "- Cover related user queries and search intent\n\n";
        $outline_prompt .= "Format: H2: [Heading Text] {id=\"unique-id\"}\nH3: [Subheading] {id=\"sub-id\"}";
        
        $outline = $this->api_handler->generate_with_openrouter($outline_prompt);
        if (is_wp_error($outline)) {
            $outline = "H2: Introduction {id=\"introduction\"}\nH2: Main Content {id=\"main-content\"}\nH3: Key Points {id=\"key-points\"}\nH2: Conclusion {id=\"conclusion\"}";
        }
        
        // Step 4: Generate meta description
        $meta_prompt = "Create a meta description for: {$title}\n\n";
        $meta_prompt .= "Focus keyword: {$focus_keyword}\n\n";
        $meta_prompt .= "Requirements:\n";
        $meta_prompt .= "- 120-160 characters total\n";
        $meta_prompt .= "- Include focus keyword in first 50-80 characters\n";
        $meta_prompt .= "- Summarize article value\n";
        $meta_prompt .= "- Include call-to-action or hook\n";
        $meta_prompt .= "- Optimize for click-through rate\n\n";
        $meta_prompt .= "Return only the meta description text.";
        
        $meta_description = $this->api_handler->generate_with_openrouter($meta_prompt);
        if (is_wp_error($meta_description)) {
            $meta_description = "Learn about {$topic} with this comprehensive guide. Discover key insights and actionable tips for better results.";
        }
        
        // Step 5: Generate comprehensive body content
        $content_prompt = "Write a comprehensive, SEO-optimized article in English on the topic: {$topic}\n\n";
        $content_prompt .= "Focus keyword: {$focus_keyword}\n";
        $content_prompt .= "Secondary keywords: " . implode(", ", $secondary_keywords) . "\n\n";
        $content_prompt .= "Research data: {$research}\n\n";
        $content_prompt .= "Outline to follow:\n{$outline}\n\n";
        $content_prompt .= "Requirements:\n";
        $content_prompt .= "1. INTRODUCTION (150-200 words):\n";
        $content_prompt .= "   - Hook with question or startling fact\n";
        $content_prompt .= "   - Include focus keyword in first 10%\n";
        $content_prompt .= "   - Clear overview of what's covered\n\n";
        $content_prompt .= "2. BODY CONTENT:\n";
        $content_prompt .= "   - For each H2 section: 300-500 words\n";
        $content_prompt .= "   - Keep paragraphs short (under 120 words, 2-4 sentences)\n";
        $content_prompt .= "   - Use second person (\"you\") for engagement\n";
        $content_prompt .= "   - Include 1-2 secondary keywords per section naturally\n";
        $content_prompt .= "   - Add examples, data, case studies, unique insights\n";
        $content_prompt .= "   - Use transition words (however, additionally, therefore, etc.)\n";
        $content_prompt .= "   - Include 3-4 internal links (use placeholder URLs like /related-article)\n";
        $content_prompt .= "   - Include 3-5 authoritative external links (at least 1 dofollow)\n";
        $content_prompt .= "   - Focus keyword density: 1-1.5% throughout\n";
        $content_prompt .= "   - Each secondary keyword density: 1-1.5%\n";
        $content_prompt .= "   - Include focus keyword in at least one subheading per major section\n";
        $content_prompt .= "   - Use <strong> and <em> tags for key phrases\n\n";
        $content_prompt .= "3. VISUAL CONTENT:\n";
        $content_prompt .= "   - Describe 4+ custom images/diagrams/infographics/videos\n";
        $content_prompt .= "   - Use figure/figcaption tags with SEO-optimized alt text\n";
        $content_prompt .= "   - Include focus keyword in at least one alt text\n";
        $content_prompt .= "   - Suggest YouTube embeds where relevant\n\n";
        $content_prompt .= "4. QUICK TAKEAWAYS:\n";
        $content_prompt .= "   - Create a section with 5-7 key bullet points\n";
        $content_prompt .= "   - Include one secondary keyword per point\n\n";
        $content_prompt .= "5. CONCLUSION (200-250 words):\n";
        $content_prompt .= "   - Summarize main points\n";
        $content_prompt .= "   - Reinforce value proposition\n";
        $content_prompt .= "   - Include actionable call-to-action\n";
        $content_prompt .= "   - Reintroduce focus keyword naturally\n\n";
        $content_prompt .= "6. FAQs:\n";
        $content_prompt .= "   - 5 unique, relevant questions\n";
        $content_prompt .= "   - Each answer: 100-150 words\n";
        $content_prompt .= "   - Naturally incorporate secondary keywords\n\n";
        $content_prompt .= "7. ENGAGEMENT:\n";
        $content_prompt .= "   - Personalized message encouraging comments/shares\n";
        $content_prompt .= "   - Include a question to prompt interaction\n\n";
        $content_prompt .= "8. REFERENCES:\n";
        $content_prompt .= "   - 3-5 authoritative external sources\n";
        $content_prompt .= "   - Inline citations with <a> tags\n";
        $content_prompt .= "   - References section with full URLs and descriptions\n\n";
        $content_prompt .= "9. FINAL WRAPPING:\n";
        $content_prompt .= "   - Wrap entire article in <div class=\"seo-article\">\n";
        $content_prompt .= "   - Mark as pillar content with class and note\n";
        $content_prompt .= "   - Target word count: 2500+ words\n";
        $content_prompt .= "   - Flesch readability score: 60-70\n";
        $content_prompt .= "   - High perplexity and burstiness while maintaining readability\n\n";
        $content_prompt .= "Return the complete HTML article ready for WordPress.";
        
        $content = $this->api_handler->generate_with_openrouter($content_prompt);
        if (is_wp_error($content)) {
            // Fallback to simpler generation
            $content = $this->write_content($topic, $outline, $research, 'professional');
            if (is_wp_error($content)) {
                return $content;
            }
        }
        
        // Step 6: Format and enhance for WordPress
        $formatted_content = $this->format_seo_content($content, $title, $meta_description, $focus_keyword);
        
        return array(
            'title' => $title,
            'content' => $formatted_content,
            'research' => $research,
            'outline' => $outline,
            'meta_description' => $meta_description,
            'focus_keyword' => $focus_keyword,
            'secondary_keywords' => $secondary_keywords
        );
    }
    
    /**
     * Format SEO content with proper structure and metadata
     */
    private function format_seo_content($content, $title, $meta_description, $focus_keyword) {
        // Check if content already has proper HTML structure
        if (strpos($content, '<div class="seo-article">') !== false) {
            return $content;
        }
        
        // Build structured content
        $html = '<div class="seo-article">';
        
        // Add meta description as HTML comment for reference
        $html .= "\n<!-- Meta Description: " . esc_html($meta_description) . " -->\n";
        
        // Add focus keyword note
        $html .= "\n<!-- Focus Keyword: " . esc_html($focus_keyword) . " -->\n";
        
        // Add pillar content marker
        $html .= "\n<!-- This is pillar content for internal linking -->\n";
        
        // Add the content (assuming it's already well-structured from the AI)
        $html .= $content;
        
        // Ensure proper closing
        if (strpos($html, '</div>') === false) {
            $html .= '</div>';
        }
        
        return $html;
    }
}