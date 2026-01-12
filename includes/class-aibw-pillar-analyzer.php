<?php
/**
 * Pillar Analyzer Class
 * Detects pillar posts and suggests related content
 */

if (!defined('ABSPATH')) {
    exit;
}

class AIBW_Pillar_Analyzer {
    
    private $api_handler;
    private $criteria_weights = array(
        'word_count' => 0.25,
        'links' => 0.20,
        'topics' => 0.20,
        'comprehensiveness' => 0.25,
        'structure' => 0.10
    );
    
    public function __construct($api_handler) {
        $this->api_handler = $api_handler;
    }
    
    /**
     * Analyze a single post for pillar potential
     */
    public function analyze_pillar_potential($post_id) {
        $post = get_post($post_id);
        
        if (!$post || $post->post_status !== 'publish') {
            return new WP_Error('invalid_post', 'Post not found or not published');
        }
        
        $analysis = array(
            'post_id' => $post_id,
            'post_title' => $post->post_title,
            'is_pillar' => false,
            'score' => 0,
            'factors' => array(),
            'recommendations' => array()
        );
        
        // Analyze each factor
        $analysis['factors']['word_count'] = $this->analyze_word_count($post);
        $analysis['factors']['structure'] = $this->analyze_structure($post);
        $analysis['factors']['links'] = $this->analyze_links($post);
        $analysis['factors']['topics'] = $this->analyze_topics($post);
        $analysis['factors']['comprehensiveness'] = $this->analyze_comprehensiveness($post);
        
        // Calculate total score
        $total_score = 0;
        foreach ($analysis['factors'] as $factor => $data) {
            if (isset($data['score']) && isset($this->criteria_weights[$factor])) {
                $total_score += $data['score'] * $this->criteria_weights[$factor];
            }
        }
        
        $analysis['score'] = round($total_score * 100, 2);
        $analysis['is_pillar'] = $analysis['score'] >= 70;
        
        // Generate recommendations
        $analysis['recommendations'] = $this->generate_recommendations($analysis['factors']);
        
        // Store analysis in post meta
        update_post_meta($post_id, '_aibw_pillar_analysis', $analysis);
        
        return $analysis;
    }
    
    /**
     * Analyze word count
     */
    private function analyze_word_count($post) {
        $word_count = str_word_count(strip_tags($post->post_content));
        
        $score = 0;
        if ($word_count >= 2000) {
            $score = 100;
        } elseif ($word_count >= 1500) {
            $score = 85;
        } elseif ($word_count >= 1000) {
            $score = 70;
        } elseif ($word_count >= 500) {
            $score = 50;
        } else {
            $score = 20;
        }
        
        return array(
            'score' => $score,
            'value' => $word_count,
            'target' => '1500+ words',
            'recommendation' => $word_count < 1500 ? 'Expand content to 1500+ words for better authority' : 'Good word count'
        );
    }
    
    /**
     * Analyze content structure
     */
    private function analyze_structure($post) {
        $content = $post->post_content;
        $score = 0;
        
        // Check for headings
        $h2_count = substr_count($content, '<h2');
        $h3_count = substr_count($content, '<h3');
        $has_h2 = $h2_count >= 3;
        $has_h3 = $h3_count >= 5;
        
        // Check for paragraphs
        $paragraph_count = substr_count($content, '<p');
        $has_paragraphs = $paragraph_count >= 5;
        
        // Check for lists
        $has_lists = (strpos($content, '<ul>') !== false || strpos($content, '<ol>') !== false);
        
        // Calculate score
        if ($has_h2 && $has_h3 && $has_paragraphs) {
            $score = 100;
        } elseif ($has_h2 && $has_paragraphs) {
            $score = 80;
        } elseif ($has_h2 || $has_paragraphs) {
            $score = 60;
        } else {
            $score = 30;
        }
        
        return array(
            'score' => $score,
            'h2_count' => $h2_count,
            'h3_count' => $h3_count,
            'paragraph_count' => $paragraph_count,
            'has_lists' => $has_lists,
            'recommendation' => $has_h2 && $has_h3 ? 'Good structure' : 'Add more subheadings (H2, H3) for better organization'
        );
    }
    
    /**
     * Analyze internal/external links
     */
    private function analyze_links($post) {
        $content = $post->post_content;
        
        // Count internal links (links to same domain)
        $internal_links = 0;
        $external_links = 0;
        
        preg_match_all('/<a\s[^>]*href="([^"]*)"[^>]*>/i', $content, $matches);
        
        if (!empty($matches[1])) {
            $home_url = home_url();
            foreach ($matches[1] as $url) {
                if (strpos($url, $home_url) !== false) {
                    $internal_links++;
                } elseif (strpos($url, 'http') === 0) {
                    $external_links++;
                }
            }
        }
        
        $total_links = $internal_links + $external_links;
        $score = 0;
        
        if ($total_links >= 10 && $internal_links >= 3) {
            $score = 100;
        } elseif ($total_links >= 5 && $internal_links >= 2) {
            $score = 80;
        } elseif ($total_links >= 3) {
            $score = 60;
        } else {
            $score = 30;
        }
        
        return array(
            'score' => $score,
            'internal' => $internal_links,
            'external' => $external_links,
            'total' => $total_links,
            'recommendation' => $internal_links < 3 ? 'Add more internal links to connect related content' : 'Good linking strategy'
        );
    }
    
    /**
     * Analyze topic coverage
     */
    private function analyze_topics($post) {
        $content = strip_tags($post->post_content);
        $words = str_word_count($content, 1);
        $unique_words = array_count_values(array_map('strtolower', $words));
        
        // Get top 20 most frequent words (excluding common words)
        $common_words = array('the', 'and', 'to', 'of', 'a', 'in', 'is', 'it', 'you', 'that', 'was', 'for', 'on', 'are', 'with', 'as', 'I', 'his', 'they', 'at', 'be', 'this', 'have', 'from', 'or', 'one', 'had', 'by', 'word', 'but', 'not', 'what', 'all', 'were', 'we', 'when', 'your', 'can', 'said', 'there', 'use', 'an', 'each', 'which', 'she', 'do', 'how', 'their', 'if', 'will', 'up', 'other', 'about', 'out', 'many', 'then', 'them', 'these', 'so', 'some', 'her', 'would', 'make', 'like', 'him', 'into', 'time', 'has', 'look', 'two', 'more', 'write', 'go', 'see', 'number', 'no', 'way', 'could', 'people', 'my', 'than', 'first', 'water', 'been', 'call', 'who', 'oil', 'its', 'now', 'find', 'long', 'down', 'day', 'did', 'get', 'come', 'made', 'may', 'part');
        
        $topic_words = array();
        foreach ($unique_words as $word => $count) {
            if ($count >= 3 && !in_array($word, $common_words) && strlen($word) > 3) {
                $topic_words[$word] = $count;
            }
        }
        
        $topic_count = count($topic_words);
        $score = 0;
        
        if ($topic_count >= 10) {
            $score = 100;
        } elseif ($topic_count >= 7) {
            $score = 80;
        } elseif ($topic_count >= 5) {
            $score = 60;
        } else {
            $score = 40;
        }
        
        return array(
            'score' => $score,
            'topic_count' => $topic_count,
            'main_topics' => array_slice(array_keys($topic_words), 0, 5),
            'recommendation' => $topic_count < 7 ? 'Expand topic coverage with more related concepts' : 'Good topic diversity'
        );
    }
    
    /**
     * Analyze comprehensiveness using AI
     */
    private function analyze_comprehensiveness($post) {
        $content = $post->post_content;
        
        // Use API handler to analyze comprehensiveness
        $result = $this->api_handler->analyze_comprehensiveness($content);
        
        if (is_wp_error($result)) {
            return array(
                'score' => 50,
                'explanation' => 'AI analysis unavailable',
                'recommendation' => 'Consider adding more depth and examples'
            );
        }
        
        $score = $result['score'];
        $recommendation = $score >= 80 ? 'Excellent comprehensiveness' : 'Add more examples, data, and detailed explanations';
        
        return array(
            'score' => $score,
            'explanation' => $result['explanation'],
            'recommendation' => $recommendation
        );
    }
    
    /**
     * Generate recommendations based on analysis
     */
    private function generate_recommendations($factors) {
        $recommendations = array();
        
        foreach ($factors as $factor => $data) {
            if (isset($data['recommendation']) && $data['score'] < 80) {
                $recommendations[] = $data['recommendation'];
            }
        }
        
        return $recommendations;
    }
    
    /**
     * Find pillar posts across all posts
     */
    public function find_pillar_posts($limit = 10) {
        $posts = get_posts(array(
            'post_type' => 'post',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'fields' => 'ids'
        ));
        
        $pillar_posts = array();
        
        foreach ($posts as $post_id) {
            $analysis = $this->analyze_pillar_potential($post_id);
            
            if (!is_wp_error($analysis) && $analysis['is_pillar']) {
                $pillar_posts[] = array(
                    'post_id' => $post_id,
                    'title' => $analysis['post_title'],
                    'score' => $analysis['score']
                );
            }
        }
        
        // Sort by score descending
        usort($pillar_posts, function($a, $b) {
            return $b['score'] <=> $a['score'];
        });
        
        return array_slice($pillar_posts, 0, $limit);
    }
    
    /**
     * Get related post suggestions for a pillar post
     */
    public function get_related_suggestions($post_id, $limit = 5) {
        $post = get_post($post_id);
        
        if (!$post || $post->post_status !== 'publish') {
            return new WP_Error('invalid_post', 'Post not found or not published');
        }
        
        // Extract main topics from the post
        $content = strip_tags($post->post_content);
        $words = str_word_count($content, 1);
        $unique_words = array_count_values(array_map('strtolower', $words));
        
        // Filter for significant words
        $significant_words = array();
        foreach ($unique_words as $word => $count) {
            if ($count >= 3 && strlen($word) > 3) {
                $significant_words[$word] = $count;
            }
        }
        
        // Get top 3 topics
        arsort($significant_words);
        $main_topics = array_slice(array_keys($significant_words), 0, 3);
        
        // Get existing post titles to avoid duplicates
        $existing_posts = get_posts(array(
            'post_type' => 'post',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'fields' => 'titles'
        ));
        
        // Generate related topics using AI
        $related_topics = $this->api_handler->generate_related_topics(
            implode(", ", $main_topics),
            $existing_posts
        );
        
        if (is_wp_error($related_topics) || empty($related_topics)) {
            // Fallback: generate suggestions manually
            $related_topics = array();
            foreach ($main_topics as $topic) {
                $related_topics[] = "Advanced techniques for " . $topic;
                $related_topics[] = $topic . " best practices";
                $related_topics[] = "Common mistakes in " . $topic;
                $related_topics[] = $topic . " case studies";
            }
        }
        
        // Get existing related posts
        $existing_related = array();
        if (!empty($main_topics)) {
            $existing_related = get_posts(array(
                'post_type' => 'post',
                'post_status' => 'publish',
                'posts_per_page' => $limit,
                's' => implode(" ", $main_topics),
                'exclude' => array($post_id),
                'fields' => 'ids'
            ));
        }
        
        return array(
            'pillar_post' => array(
                'id' => $post_id,
                'title' => $post->post_title,
                'topics' => $main_topics
            ),
            'new_suggestions' => array_slice($related_topics, 0, $limit),
            'existing_related' => $existing_related
        );
    }
    
    /**
     * Bulk analyze multiple posts
     */
    public function bulk_analyze($post_ids) {
        $results = array();
        
        foreach ($post_ids as $post_id) {
            $analysis = $this->analyze_pillar_potential($post_id);
            
            if (!is_wp_error($analysis)) {
                $results[] = $analysis;
            }
            
            // Small delay to avoid overwhelming the server
            usleep(100000); // 0.1 seconds
        }
        
        return $results;
    }
    
    /**
     * Get analysis from post meta
     */
    public function get_cached_analysis($post_id) {
        return get_post_meta($post_id, '_aibw_pillar_analysis', true);
    }
}