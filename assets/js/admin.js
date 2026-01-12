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
});