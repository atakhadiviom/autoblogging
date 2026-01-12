jQuery(document).ready(function($) {
    
    // Test APIs button
    $('#test-apis-btn').on('click', function() {
        var $btn = $(this);
        var $results = $('#api-results');
        
        $btn.prop('disabled', true).text('Testing...');
        $results.html('<p>Testing API connections...</p>');
        
        $.ajax({
            url: aibw_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'aibw_test_apis',
                nonce: aibw_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    var html = '';
                    
                    // OpenRouter
                    var openrouter = response.data.openrouter;
                    html += '<div style="padding: 10px; margin: 5px 0; background: ' + 
                           (openrouter.success ? '#d4edda' : '#f8d7da') + '; border: 1px solid ' + 
                           (openrouter.success ? '#c3e6cb' : '#f5c6cb') + '; border-radius: 4px;">';
                    html += '<strong>OpenRouter:</strong> ';
                    html += openrouter.success ? '✅ Connected' : '❌ Failed';
                    html += '<br><small>' + openrouter.message + '</small>';
                    html += '</div>';
                    
                    // Perplexity
                    var perplexity = response.data.perplexity;
                    html += '<div style="padding: 10px; margin: 5px 0; background: ' + 
                           (perplexity.success ? '#d4edda' : '#f8d7da') + '; border: 1px solid ' + 
                           (perplexity.success ? '#c3e6cb' : '#f5c6cb') + '; border-radius: 4px;">';
                    html += '<strong>Perplexity:</strong> ';
                    html += perplexity.success ? '✅ Connected' : '❌ Failed';
                    html += '<br><small>' + perplexity.message + '</small>';
                    html += '</div>';
                    
                    $results.html(html);
                } else {
                    $results.html('<div style="color: red;">Error: ' + response.data + '</div>');
                }
            },
            error: function() {
                $results.html('<div style="color: red;">AJAX request failed</div>');
            },
            complete: function() {
                $btn.prop('disabled', false).text('Test All APIs');
            }
        });
    });
    
    // Generate single post form
    $('#generate-form').on('submit', function(e) {
        e.preventDefault();
        
        var $form = $(this);
        var $btn = $('#generate-btn');
        var $indicator = $('#generating-indicator');
        var $result = $('#generation-result');
        var $resultContent = $('#result-content');
        
        var topic = $('#post_topic').val().trim();
        var useResearch = $('#use_research').is(':checked');
        var apiChoice = $('#api_choice').val();
        var category = $('#target_category').val();
        
        if (!topic) {
            alert('Please enter a topic');
            return;
        }
        
        $btn.prop('disabled', true);
        $indicator.show();
        $result.hide();
        $resultContent.html('');
        
        $.ajax({
            url: aibw_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'aibw_generate_post',
                nonce: aibw_ajax.nonce,
                topic: topic,
                use_research: useResearch,
                api_choice: apiChoice,
                category: category
            },
            success: function(response) {
                if (response.success) {
                    var html = '<div style="padding: 15px; background: #d4edda; border: 1px solid #c3e6cb; border-radius: 4px; margin-bottom: 10px;">';
                    html += '✅ ' + response.data.message;
                    html += '</div>';
                    
                    html += '<p><strong>Post ID:</strong> ' + response.data.post_id + '</p>';
                    html += '<p><strong>View Post:</strong> <a href="' + response.data.post_url + '" target="_blank">' + response.data.post_url + '</a></p>';
                    html += '<p><a href="' + response.data.post_url + '" class="button button-primary" target="_blank">View Post</a>';
                    html += ' <a href="' + aibw_ajax.admin_url + '?post=' + response.data.post_id + '&action=edit" class="button">Edit Post</a></p>';
                    
                    $resultContent.html(html);
                    $result.show();
                    
                    // Clear form
                    $('#post_topic').val('');
                } else {
                    var errorHtml = '<div style="padding: 15px; background: #f8d7da; border: 1px solid #f5c6cb; border-radius: 4px;">';
                    errorHtml += '❌ Error: ' + response.data;
                    errorHtml += '</div>';
                    $resultContent.html(errorHtml);
                    $result.show();
                }
            },
            error: function() {
                $resultContent.html('<div style="color: red;">AJAX request failed. Please check your connection.</div>');
                $result.show();
            },
            complete: function() {
                $btn.prop('disabled', false);
                $indicator.hide();
            }
        });
    });
    
    // Batch generation form
    $('#batch-form').on('submit', function(e) {
        e.preventDefault();
        
        var $form = $(this);
        var $btn = $('#batch-generate-btn');
        var $indicator = $('#batch-indicator');
        var $result = $('#batch-results');
        var $resultContent = $('#batch-result-content');
        
        var topics = $('#batch_topics').val().trim();
        var useResearch = $('#batch_research').is(':checked');
        var delay = $('#batch_delay').val();
        
        if (!topics) {
            alert('Please enter at least one topic');
            return;
        }
        
        var topicArray = topics.split('\n').filter(function(t) { return t.trim() !== ''; });
        
        if (topicArray.length === 0) {
            alert('Please enter valid topics (one per line)');
            return;
        }
        
        if (!confirm('This will generate ' + topicArray.length + ' posts. This may take some time. Continue?')) {
            return;
        }
        
        $btn.prop('disabled', true);
        $indicator.show();
        $result.hide();
        $resultContent.html('');
        
        $.ajax({
            url: aibw_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'aibw_generate_batch',
                nonce: aibw_ajax.nonce,
                topics: topics,
                use_research: useResearch,
                delay: delay
            },
            success: function(response) {
                if (response.success) {
                    var html = '<div style="margin-bottom: 15px; padding: 10px; background: #d4edda; border: 1px solid #c3e6cb; border-radius: 4px;">';
                    html += '✅ Batch processing completed!';
                    html += '</div>';
                    
                    html += '<table class="wp-list-table widefat fixed striped">';
                    html += '<thead><tr><th>Topic</th><th>Status</th><th>Post ID</th><th>Link</th></tr></thead>';
                    html += '<tbody>';
                    
                    var successCount = 0;
                    var failCount = 0;
                    
                    response.data.forEach(function(result) {
                        html += '<tr>';
                        html += '<td>' + result.topic + '</td>';
                        
                        if (result.success) {
                            successCount++;
                            html += '<td style="color: green;">✅ Success</td>';
                            html += '<td>' + result.post_id + '</td>';
                            html += '<td><a href="' + result.post_url + '" target="_blank">View</a> | <a href="' + aibw_ajax.admin_url + '?post=' + result.post_id + '&action=edit" target="_blank">Edit</a></td>';
                        } else {
                            failCount++;
                            html += '<td style="color: red;">❌ Failed</td>';
                            html += '<td>-</td>';
                            html += '<td>' + result.error + '</td>';
                        }
                        
                        html += '</tr>';
                    });
                    
                    html += '</tbody></table>';
                    
                    html += '<div style="margin-top: 10px; padding: 10px; background: #f8f9fa; border-radius: 4px;">';
                    html += '<strong>Summary:</strong> ' + successCount + ' successful, ' + failCount + ' failed';
                    html += '</div>';
                    
                    $resultContent.html(html);
                    $result.show();
                    
                    // Clear form
                    $('#batch_topics').val('');
                } else {
                    var errorHtml = '<div style="padding: 15px; background: #f8d7da; border: 1px solid #f5c6cb; border-radius: 4px;">';
                    errorHtml += '❌ Error: ' + response.data;
                    errorHtml += '</div>';
                    $resultContent.html(errorHtml);
                    $result.show();
                }
            },
            error: function() {
                $resultContent.html('<div style="color: red;">AJAX request failed. Please check your connection.</div>');
                $result.show();
            },
            complete: function() {
                $btn.prop('disabled', false);
                $indicator.hide();
            }
        });
    });
    
    // Analyze single post for pillar potential
    $('#analyze-single-btn').on('click', function() {
        var $btn = $(this);
        var $result = $('#single-analysis-result');
        var postId = $('#analyze-post-id').val().trim();
        
        if (!postId || postId < 1) {
            alert('Please enter a valid Post ID');
            return;
        }
        
        $btn.prop('disabled', true).text('Analyzing...');
        $result.html('<p>Analyzing post...</p>');
        
        $.ajax({
            url: aibw_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'aibw_analyze_pillar',
                nonce: aibw_ajax.nonce,
                post_id: postId
            },
            success: function(response) {
                if (response.success) {
                    var data = response.data;
                    var html = '<div style="padding: 15px; background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 4px;">';
                    
                    html += '<h3>' + data.post_title + '</h3>';
                    html += '<p><strong>Pillar Score:</strong> <span style="font-size: 24px; font-weight: bold; color: ' + 
                           (data.is_pillar ? '#28a745' : '#ffc107') + ';">' + data.score + '%</span> ';
                    html += data.is_pillar ? '✅ (Pillar Post)' : '⚠️ (Needs Improvement)'</p>';
                    
                    html += '<h4>Analysis Breakdown:</h4>';
                    html += '<table class="wp-list-table widefat fixed striped"><thead><tr><th>Factor</th><th>Score</th><th>Status</th><th>Details</th></tr></thead><tbody>';
                    
                    for (var factor in data.factors) {
                        var factorData = data.factors[factor];
                        var statusIcon = factorData.status === 'good' ? '✅' : '⚠️';
                        
                        html += '<tr>';
                        html += '<td><strong>' + factor.charAt(0).toUpperCase() + factor.slice(1) + '</strong></td>';
                        html += '<td>' + (factorData.score * 100).toFixed(0) + '%</td>';
                        html += '<td>' + statusIcon + ' ' + factorData.status + '</td>';
                        
                        var details = '';
                        if (factor === 'word_count') details = factorData.value + ' words';
                        else if (factor === 'structure') details = factorData.headings + ' headings, ' + factorData.paragraphs + ' paragraphs';
                        else if (factor === 'links') details = factorData.internal + ' internal, ' + factorData.external + ' external';
                        else if (factor === 'topics') details = factorData.unique_topics + ' topics';
                        
                        html += '<td>' + details + '</td>';
                        html += '</tr>';
                    }
                    
                    html += '</tbody></table>';
                    
                    if (data.recommendations.length > 0) {
                        html += '<h4>Recommendations:</h4><ul>';
                        data.recommendations.forEach(function(rec) {
                            html += '<li>' + rec + '</li>';
                        });
                        html += '</ul>';
                    }
                    
                    html += '</div>';
                    $result.html(html);
                } else {
                    $result.html('<div style="color: red;">Error: ' + response.data + '</div>');
                }
            },
            error: function() {
                $result.html('<div style="color: red;">AJAX request failed</div>');
            },
            complete: function() {
                $btn.prop('disabled', false).text('Analyze Post');
            }
        });
    });
    
    // Get related post suggestions
    $('#get-suggestions-btn').on('click', function() {
        var $btn = $(this);
        var $result = $('#suggestions-result');
        var postId = $('#suggestion-post-id').val().trim();
        var limit = $('#suggestion-limit').val().trim();
        
        if (!postId || postId < 1) {
            alert('Please enter a valid Post ID');
            return;
        }
        
        $btn.prop('disabled', true).text('Getting Suggestions...');
        $result.html('<p>Generating suggestions...</p>');
        
        $.ajax({
            url: aibw_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'aibw_get_suggestions',
                nonce: aibw_ajax.nonce,
                post_id: postId,
                limit: limit
            },
            success: function(response) {
                if (response.success) {
                    var data = response.data;
                    var html = '<div style="padding: 15px; background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 4px;">';
                    
                    html += '<h3>Related Post Suggestions for: ' + data.pillar_post.post_title + '</h3>';
                    
                    // Existing related posts
                    if (data.existing_related.length > 0) {
                        html += '<h4>Existing Related Posts:</h4>';
                        html += '<ul>';
                        data.existing_related.forEach(function(related) {
                            html += '<li><a href="' + related.post.guid + '" target="_blank">' + related.post.post_title + '</a> (' + related.relationship + ')</li>';
                        });
                        html += '</ul>';
                    }
                    
                    // New suggestions
                    if (data.new_suggestions.length > 0) {
                        html += '<h4>Suggested New Posts:</h4>';
                        html += '<ol>';
                        data.new_suggestions.forEach(function(suggestion) {
                            html += '<li>' + suggestion + '</li>';
                        });
                        html += '</ol>';
                    }
                    
                    // Key topics
                    if (data.all_topics.length > 0) {
                        html += '<h4>Key Topics Identified:</h4>';
                        html += '<div style="display: flex; flex-wrap: wrap; gap: 5px;">';
                        data.all_topics.slice(0, 10).forEach(function(topic) {
                            html += '<span style="background: #007cba; color: white; padding: 3px 8px; border-radius: 12px; font-size: 12px;">' + topic + '</span>';
                        });
                        html += '</div>';
                    }
                    
                    html += '</div>';
                    $result.html(html);
                } else {
                    $result.html('<div style="color: red;">Error: ' + response.data + '</div>');
                }
            },
            error: function() {
                $result.html('<div style="color: red;">AJAX request failed</div>');
            },
            complete: function() {
                $btn.prop('disabled', false).text('Get Suggestions');
            }
        });
    });
    
    // Bulk analyze posts
    $('#bulk-analyze-btn').on('click', function() {
        var $btn = $(this);
        var $result = $('#bulk-results');
        var limit = $('#bulk-limit').val().trim();
        
        if (!limit || limit < 1) {
            alert('Please enter a valid number');
            return;
        }
        
        if (!confirm('This will analyze up to ' + limit + ' posts. This may take a while. Continue?')) {
            return;
        }
        
        $btn.prop('disabled', true).text('Analyzing...');
        $result.html('<p>Analyzing posts... This may take several minutes.</p>');
        
        $.ajax({
            url: aibw_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'aibw_bulk_analyze',
                nonce: aibw_ajax.nonce,
                limit: limit
            },
            success: function(response) {
                if (response.success) {
                    var data = response.data;
                    var html = '<div style="padding: 15px; background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 4px;">';
                    
                    html += '<h3>Bulk Analysis Results</h3>';
                    html += '<table class="wp-list-table widefat fixed striped">';
                    html += '<thead><tr><th>Post</th><th>Score</th><th>Status</th><th>Actions</th></tr></thead>';
                    html += '<tbody>';
                    
                    var pillarCount = 0;
                    
                    data.forEach(function(item) {
                        var analysis = item.analysis;
                        var isPillar = analysis.is_pillar;
                        if (isPillar) pillarCount++;
                        
                        html += '<tr>';
                        html += '<td>' + analysis.post_title + '</td>';
                        html += '<td><strong>' + analysis.score + '%</strong></td>';
                        html += '<td>' + (isPillar ? '✅ Pillar' : '⚠️ Standard') + '</td>';
                        html += '<td><button class="button button-small view-analysis-btn" data-post-id="' + item.post_id + '">View Details</button></td>';
                        html += '</tr>';
                    });
                    
                    html += '</tbody></table>';
                    html += '<p><strong>Summary:</strong> ' + pillarCount + ' pillar posts found out of ' + data.length + ' analyzed</p>';
                    html += '</div>';
                    
                    $result.html(html);
                } else {
                    $result.html('<div style="color: red;">Error: ' + response.data + '</div>');
                }
            },
            error: function() {
                $result.html('<div style="color: red;">AJAX request failed</div>');
            },
            complete: function() {
                $btn.prop('disabled', false).text('Start Bulk Analysis');
            }
        });
    });
    
    // Find existing pillar posts
    $('#find-pillars-btn').on('click', function() {
        var $btn = $(this);
        var $result = $('#pillars-list');
        
        $btn.prop('disabled', true).text('Finding...');
        $result.html('<p>Searching for pillar posts...</p>');
        
        // This uses the existing AJAX handler for bulk analysis but with a different approach
        // Since we don't have a dedicated "find pillars" endpoint, we'll analyze recent posts
        $.ajax({
            url: aibw_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'aibw_bulk_analyze',
                nonce: aibw_ajax.nonce,
                limit: 20
            },
            success: function(response) {
                if (response.success) {
                    var data = response.data;
                    var pillarPosts = data.filter(function(item) {
                        return item.analysis.is_pillar;
                    });
                    
                    var html = '<div style="padding: 15px; background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 4px;">';
                    
                    if (pillarPosts.length === 0) {
                        html += '<p>No pillar posts found in the recent 20 posts. Try analyzing more posts or check if your existing content meets the criteria.</p>';
                    } else {
                        html += '<h4>Found ' + pillarPosts.length + ' Pillar Posts:</h4>';
                        html += '<ul>';
                        pillarPosts.forEach(function(item) {
                            html += '<li>';
                            html += '<strong>' + item.analysis.post_title + '</strong> ';
                            html += '(Score: ' + item.analysis.score + '%) ';
                            html += '<a href="' + item.analysis.post_url + '" target="_blank">View</a> | ';
                            html += '<a href="' + aibw_ajax.admin_url + '?post=' + item.post_id + '&action=edit" target="_blank">Edit</a> | ';
                            html += '<button class="button button-small get-suggestions-inline-btn" data-post-id="' + item.post_id + '">Get Suggestions</button>';
                            html += '</li>';
                        });
                        html += '</ul>';
                    }
                    
                    html += '</div>';
                    $result.html(html);
                    
                    // Add click handlers for inline suggestion buttons
                    $('.get-suggestions-inline-btn').on('click', function() {
                        var postId = $(this).data('post-id');
                        $('#suggestion-post-id').val(postId);
                        $('#get-suggestions-btn').click();
                        // Scroll to suggestions section
                        $('html, body').animate({
                            scrollTop: $("#suggestions-result").offset().top - 100
                        }, 500);
                    });
                } else {
                    $result.html('<div style="color: red;">Error: ' + response.data + '</div>');
                }
            },
            error: function() {
                $result.html('<div style="color: red;">AJAX request failed</div>');
            },
            complete: function() {
                $btn.prop('disabled', false).text('Find Pillar Posts');
            }
        });
    });
    
    // View analysis details (from bulk results)
    $(document).on('click', '.view-analysis-btn', function() {
        var postId = $(this).data('post-id');
        $('#analyze-post-id').val(postId);
        $('#analyze-single-btn').click();
        // Scroll to single analysis section
        $('html, body').animate({
            scrollTop: $("#single-analysis-result").offset().top - 100
        }, 500);
    });
    
    // SEO Content Generator
    $('#seo-generate-form').on('submit', function(e) {
        e.preventDefault();
        
        var $form = $(this);
        var $btn = $('#seo-generate-btn');
        var $loading = $('#seo-loading');
        var $result = $('#seo-result');
        
        var topic = $('#seo-topic').val().trim();
        var focusKeyword = $('#seo-focus-keyword').val().trim();
        var secondaryKeywords = $('#seo-secondary-keywords').val().trim();
        var status = $('#seo-status').val();
        
        if (!topic || !focusKeyword) {
            alert('Please fill in both Topic and Focus Keyword');
            return;
        }
        
        if (!confirm('This will generate a comprehensive 2500+ word article. This may take 2-3 minutes. Continue?')) {
            return;
        }
        
        $btn.prop('disabled', true);
        $loading.show();
        $result.html('<div class="notice notice-info"><p>Starting SEO content generation...</p></div>');
        
        $.ajax({
            url: aibw_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'aibw_generate_seo_content',
                nonce: aibw_ajax.nonce,
                topic: topic,
                focus_keyword: focusKeyword,
                secondary_keywords: secondaryKeywords,
                status: status
            },
            success: function(response) {
                if (response.success) {
                    var data = response.data;
                    var html = '<div style="padding: 15px; background: #d4edda; border: 1px solid #c3e6cb; border-radius: 4px;">';
                    html += '<h3>✅ ' + data.message + '</h3>';
                    html += '<p><strong>Title:</strong> ' + data.title + '</p>';
                    html += '<p><strong>Focus Keyword:</strong> ' + data.focus_keyword + '</p>';
                    html += '<p><strong>Meta Description:</strong> ' + data.meta_description + '</p>';
                    html += '<p><strong>Preview:</strong> ' + data.preview + '</p>';
                    html += '<p><strong>Post ID:</strong> ' + data.post_id + '</p>';
                    html += '<p>';
                    html += '<a href="' + data.post_url + '" target="_blank" class="button button-primary">View Post</a> ';
                    html += '<a href="' + aibw_ajax.admin_url + '?post=' + data.post_id + '&action=edit" target="_blank" class="button button-secondary">Edit in WordPress</a>';
                    html += '</p>';
                    html += '</div>';
                    $result.html(html);
                } else {
                    var errorHtml = '<div class="notice notice-error"><p><strong>Error:</strong> ' + response.data + '</p></div>';
                    $result.html(errorHtml);
                }
            },
            error: function() {
                $result.html('<div class="notice notice-error"><p>AJAX request failed. Please check your connection and try again.</p></div>');
            },
            complete: function() {
                $btn.prop('disabled', false);
                $loading.hide();
            }
        });
    });
    
    // Settings form handler
    $('#settings-form').on('submit', function(e) {
        e.preventDefault();
        
        var $form = $(this);
        var $btn = $('#save-settings-btn');
        var $loading = $('#settings-loading');
        var $result = $('#settings-result');
        
        var openrouterKey = $('#openrouter-key').val().trim();
        var perplexityKey = $('#perplexity-key').val().trim();
        var defaultModel = $('#default-model').val().trim();
        var perplexityModel = $('#perplexity-model').val().trim();
        var maxTokens = $('#max-tokens').val().trim();
        var temperature = $('#temperature').val().trim();
        var defaultAuthor = $('#default-author').val().trim();
        
        $btn.prop('disabled', true);
        $loading.show();
        $result.html('<div class="notice notice-info"><p>Saving settings...</p></div>');
        
        $.ajax({
            url: aibw_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'aibw_save_settings',
                nonce: aibw_ajax.nonce,
                openrouter_api_key: openrouterKey,
                perplexity_api_key: perplexityKey,
                default_model: defaultModel,
                perplexity_model: perplexityModel,
                max_tokens: maxTokens,
                temperature: temperature,
                default_author: defaultAuthor
            },
            success: function(response) {
                if (response.success) {
                    $result.html('<div class="notice notice-success"><p>✅ ' + response.data + '</p></div>');
                } else {
                    $result.html('<div class="notice notice-error"><p>❌ Error: ' + response.data + '</p></div>');
                }
            },
            error: function() {
                $result.html('<div class="notice notice-error"><p>❌ AJAX request failed. Please check your connection.</p></div>');
            },
            complete: function() {
                $btn.prop('disabled', false);
                $loading.hide();
            }
        });
    });
    
    // Generate post form handler (main page)
    $('#generate-post-form').on('submit', function(e) {
        e.preventDefault();
        
        var $form = $(this);
        var $btn = $('#generate-btn');
        var $loading = $('#generate-loading');
        var $result = $('#generation-result');
        
        var topic = $('#post-topic').val().trim();
        var keywords = $('#post-keywords').val().trim();
        var tone = $('#post-tone').val();
        var status = $('#post-status').val();
        
        if (!topic) {
            alert('Please enter a topic');
            return;
        }
        
        $btn.prop('disabled', true);
        $loading.show();
        $result.html('<div class="notice notice-info"><p>Generating post... This may take 30-60 seconds.</p></div>');
        
        $.ajax({
            url: aibw_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'aibw_generate_post',
                nonce: aibw_ajax.nonce,
                topic: topic,
                keywords: keywords,
                tone: tone,
                status: status
            },
            success: function(response) {
                if (response.success) {
                    var data = response.data;
                    var html = '<div style="padding: 15px; background: #d4edda; border: 1px solid #c3e6cb; border-radius: 4px;">';
                    html += '<h3>✅ ' + data.message + '</h3>';
                    html += '<p><strong>Title:</strong> ' + data.title + '</p>';
                    html += '<p><strong>Preview:</strong> ' + data.preview + '</p>';
                    html += '<p><strong>Post ID:</strong> ' + data.post_id + '</p>';
                    html += '<p>';
                    html += '<a href="' + data.post_url + '" target="_blank" class="button button-primary">View Post</a> ';
                    html += '<a href="' + aibw_ajax.admin_url + '?post=' + data.post_id + '&action=edit" target="_blank" class="button button-secondary">Edit in WordPress</a>';
                    html += '</p>';
                    html += '</div>';
                    $result.html(html);
                } else {
                    var errorHtml = '<div class="notice notice-error"><p><strong>Error:</strong> ' + response.data + '</p></div>';
                    $result.html(errorHtml);
                }
            },
            error: function() {
                $result.html('<div class="notice notice-error"><p>AJAX request failed. Please check your connection and try again.</p></div>');
            },
            complete: function() {
                $btn.prop('disabled', false);
                $loading.hide();
            }
        });
    });
    
    // Generate ideas form handler
    $('#ideas-form').on('submit', function(e) {
        e.preventDefault();
        
        var $form = $(this);
        var $btn = $('#ideas-btn');
        var $result = $('#ideas-result');
        
        var topic = $('#ideas-topic').val().trim();
        var count = $('#ideas-count').val().trim();
        
        if (!topic) {
            alert('Please enter a topic');
            return;
        }
        
        $btn.prop('disabled', true).text('Generating...');
        $result.html('<p>Generating ideas...</p>');
        
        $.ajax({
            url: aibw_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'aibw_generate_post', // Use existing handler for ideas
                nonce: aibw_ajax.nonce,
                topic: topic,
                keywords: '',
                tone: 'professional',
                status: 'draft'
            },
            success: function(response) {
                if (response.success) {
                    var data = response.data;
                    var html = '<div style="padding: 15px; background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 4px;">';
                    html += '<h4>Generated Idea:</h4>';
                    html += '<p><strong>' + data.title + '</strong></p>';
                    html += '<p>' + data.preview + '</p>';
                    html += '<p><a href="' + data.post_url + '" target="_blank" class="button button-small">View Draft</a></p>';
                    html += '</div>';
                    $result.html(html);
                } else {
                    $result.html('<div style="color: red;">Error: ' + response.data + '</div>');
                }
            },
            error: function() {
                $result.html('<div style="color: red;">AJAX request failed</div>');
            },
            complete: function() {
                $btn.prop('disabled', false).text('Generate Ideas');
            }
        });
    });
});