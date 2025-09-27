// App Sync Plugin Admin JavaScript
(function($) {
    'use strict';
    
    $(document).ready(function() {
        initSyncPostButtons();
        initBulkSyncNotices();
        initTooltips();
        initTemplatePreview();
        initConnectionTesting();
        initAdvancedFeatures();
    });
    
    // Initialize sync post buttons in posts list
    function initSyncPostButtons() {
        $(document).on('click', '.sync-post-btn', function(e) {
            e.preventDefault();
            
            var $btn = $(this);
            var postId = $btn.data('post-id');
            var originalText = $btn.text();
            
            if ($btn.prop('disabled')) {
                return;
            }
            
            $btn.prop('disabled', true)
                .addClass('loading')
                .text('Syncing...');
            
            $.ajax({
                url: appSyncAjax.ajaxurl,
                type: 'POST',
                data: {
                    action: 'app_sync_manual_sync',
                    post_id: postId,
                    nonce: appSyncAjax.manual_sync_nonce || appSyncAjax.nonce
                },
                timeout: 30000,
                success: function(response) {
                    if (response.success) {
                        $btn.removeClass('loading')
                            .text('✓ Synced!')
                            .css('color', '#46b450');
                        
                        // Show success message
                        showNotice('success', response.data.message || 'Post synced successfully');
                        
                        // Reset button after 3 seconds
                        setTimeout(function() {
                            $btn.text(originalText)
                                .css('color', '')
                                .prop('disabled', false);
                        }, 3000);
                        
                        // Refresh sync status if on posts page
                        if ($('.column-app_sync_status').length) {
                            setTimeout(function() {
                                location.reload();
                            }, 2000);
                        }
                    } else {
                        $btn.removeClass('loading')
                            .text('✗ Failed')
                            .css('color', '#dc3232');
                        
                        showNotice('error', response.data || 'Sync failed');
                        
                        // Reset button after 3 seconds
                        setTimeout(function() {
                            $btn.text(originalText)
                                .css('color', '')
                                .prop('disabled', false);
                        }, 3000);
                    }
                },
                error: function(xhr, status, error) {
                    $btn.removeClass('loading')
                        .text('✗ Error')
                        .css('color', '#dc3232');
                    
                    var errorMsg = 'Network error occurred';
                    if (status === 'timeout') {
                        errorMsg = 'Request timed out';
                    } else if (xhr.responseJSON && xhr.responseJSON.data) {
                        errorMsg = xhr.responseJSON.data;
                    }
                    
                    showNotice('error', errorMsg);
                    
                    // Reset button after 3 seconds
                    setTimeout(function() {
                        $btn.text(originalText)
                            .css('color', '')
                            .prop('disabled', false);
                    }, 3000);
                }
            });
        });
    }
    
    // Handle bulk sync result notices
    function initBulkSyncNotices() {
        var urlParams = new URLSearchParams(window.location.search);
        var bulkResult = urlParams.get('app_sync_bulk_result');
        
        if (bulkResult) {
            var message = bulkResult + ' posts were queued for synchronization.';
            showNotice('success', message);
            
            // Clean URL
            var newUrl = window.location.href.replace(/[?&]app_sync_bulk_result=\d+/, '');
            history.replaceState(null, '', newUrl);
        }
    }
    
    // Initialize tooltips
    function initTooltips() {
        $(document).on('mouseenter', '[data-tooltip]', function() {
            var $this = $(this);
            var tooltip = $this.attr('data-tooltip');
            
            if (!tooltip) return;
            
            var $tooltip = $('<div class="app-sync-tooltip-popup">')
                .text(tooltip)
                .css({
                    position: 'absolute',
                    background: '#1d2327',
                    color: '#fff',
                    padding: '6px 10px',
                    borderRadius: '4px',
                    fontSize: '12px',
                    whiteSpace: 'nowrap',
                    zIndex: 10000,
                    pointerEvents: 'none'
                });
            
            $('body').append($tooltip);
            
            var offset = $this.offset();
            var tooltipWidth = $tooltip.outerWidth();
            var tooltipHeight = $tooltip.outerHeight();
            
            $tooltip.css({
                top: offset.top - tooltipHeight - 8,
                left: offset.left + ($this.outerWidth() / 2) - (tooltipWidth / 2)
            });
            
            $tooltip.fadeIn(200);
        });
        
        $(document).on('mouseleave', '[data-tooltip]', function() {
            $('.app-sync-tooltip-popup').fadeOut(200, function() {
                $(this).remove();
            });
        });
    }
    
    // Initialize template preview functionality
    function initTemplatePreview() {
        var previewTimeout;
        
        $(document).on('input', '#title_template', function() {
            var $input = $(this);
            var $preview = $('#template-preview');
            
            // Clear existing timeout
            if (previewTimeout) {
                clearTimeout(previewTimeout);
            }
            
            // Hide preview while typing
            $preview.hide();
            
            // Show preview after user stops typing for 500ms
            previewTimeout = setTimeout(function() {
                var template = $input.val().trim();
                if (template) {
                    generatePreview(template);
                }
            }, 500);
        });
        
        function generatePreview(template) {
            var postId = $('#post_ID').val();
            if (!postId) return;
            
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'app_sync_test_template',
                    template: template,
                    post_id: postId,
                    nonce: appSyncAjax.nonce
                },
                success: function(response) {
                    if (response.success) {
                        $('#preview-text').text(response.data.preview);
                        $('#template-preview').fadeIn();
                    }
                }
            });
        }
    }
    
    // Initialize connection testing
    function initConnectionTesting() {
        $(document).on('click', '#test-single-connection', function() {
            var $btn = $(this);
            var siteUrl = $btn.data('site-url');
            
            if (!siteUrl) return;
            
            $btn.prop('disabled', true).text('Testing...');
            
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'app_sync_test_connection',
                    sites: [siteUrl],
                    nonce: appSyncAjax.nonce
                },
                timeout: 15000,
                success: function(response) {
                    if (response.success && response.data.results.length > 0) {
                        var result = response.data.results[0];
                        var status = result.success ? 'Connected' : 'Failed';
                        var color = result.success ? '#46b450' : '#dc3232';
                        
                        $btn.text(status).css('color', color);
                        
                        if (!result.success) {
                            showNotice('error', 'Connection failed: ' + result.message);
                        }
                    }
                },
                error: function() {
                    $btn.text('Error').css('color', '#dc3232');
                    showNotice('error', 'Connection test failed');
                },
                complete: function() {
                    setTimeout(function() {
                        $btn.prop('disabled', false)
                            .text('Test')
                            .css('color', '');
                    }, 3000);
                }
            });
        });
    }
    
    // Initialize advanced features
    function initAdvancedFeatures() {
        // Auto-refresh sync status
        if ($('.sync-status-container').length) {
            setInterval(function() {
                refreshSyncStatus();
            }, 30000); // Refresh every 30 seconds
        }
        
        // Real-time sync progress updates
        if ($('#bulk-sync-progress:visible').length) {
            monitorBulkSyncProgress();
        }
        
        // Keyboard shortcuts
        $(document).on('keydown', function(e) {
            // Ctrl/Cmd + Shift + S = Manual sync current post
            if ((e.ctrlKey || e.metaKey) && e.shiftKey && e.keyCode === 83) {
                e.preventDefault();
                var $syncBtn = $('.sync-post-btn:first');
                if ($syncBtn.length && !$syncBtn.prop('disabled')) {
                    $syncBtn.click();
                }
            }
        });
    }
    
    // Refresh sync status in metabox
    function refreshSyncStatus() {
        var postId = $('#post_ID').val();
        if (!postId) return;
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'app_sync_get_post_status',
                post_id: postId,
                nonce: appSyncAjax.nonce
            },
            success: function(response) {
                if (response.success) {
                    updateSyncStatusDisplay(response.data);
                }
            }
        });
    }
    
    // Update sync status display
    function updateSyncStatusDisplay(data) {
        var $container = $('.sync-status-container');
        if (!$container.length) return;
        
        // Update recent logs
        if (data.recent_logs && data.recent_logs.length > 0) {
            var html = '';
            data.recent_logs.forEach(function(log) {
                var statusClass = log.status === 'success' ? 'sync-success' : 'sync-failed';
                var timeAgo = formatTimeAgo(log.created_at);
                
                html += '<div class="sync-entry ' + statusClass + '">';
                html += '<div class="sync-site">' + extractHostname(log.site_url) + '</div>';
                html += '<div class="sync-status-badge">';
                html += '<span class="status-' + log.status + '">' + capitalizeFirst(log.status) + '</span>';
                html += '<span class="sync-time">' + timeAgo + '</span>';
                html += '</div>';
                if (log.status === 'failed' && log.response_message) {
                    html += '<div class="sync-error">' + escapeHtml(log.response_message) + '</div>';
                }
                html += '</div>';
            });
            
            $('.sync-log-entries').html(html);
        }
    }
    
    // Monitor bulk sync progress
    function monitorBulkSyncProgress() {
        var progressInterval = setInterval(function() {
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'app_sync_get_sync_status',
                    nonce: appSyncAjax.nonce
                },
                success: function(response) {
                    if (response.success) {
                        updateBulkProgress(response.data);
                        
                        if (response.data.completed >= response.data.total) {
                            clearInterval(progressInterval);
                            showNotice('success', 'Bulk sync completed successfully!');
                        }
                    } else {
                        clearInterval(progressInterval);
                        showNotice('error', 'Bulk sync monitoring failed');
                    }
                },
                error: function() {
                    clearInterval(progressInterval);
                }
            });
        }, 2000);
        
        // Stop monitoring after 30 minutes
        setTimeout(function() {
            clearInterval(progressInterval);
        }, 1800000);
    }
    
    // Update bulk sync progress display
    function updateBulkProgress(data) {
        var percentage = (data.completed / data.total) * 100;
        $('#progress-bar').css('width', percentage + '%');
        $('#progress-text').text(data.completed + ' / ' + data.total);
        $('#progress-details').text(data.message || '');
    }
    
    // Utility functions
    function showNotice(type, message) {
        var $notice = $('<div class="notice notice-' + type + ' is-dismissible"><p>' + escapeHtml(message) + '</p></div>');
        
        $('.wrap h1').after($notice);
        
        // Make notice dismissible
        $notice.find('.notice-dismiss').on('click', function() {
            $notice.fadeOut();
        });
        
        // Auto-dismiss after 5 seconds
        setTimeout(function() {
            $notice.fadeOut();
        }, 5000);
    }
    
    function escapeHtml(text) {
        var map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        
        return text.replace(/[&<>"']/g, function(m) { return map[m]; });
    }
    
    function capitalizeFirst(str) {
        return str.charAt(0).toUpperCase() + str.slice(1);
    }
    
    function extractHostname(url) {
        try {
            return new URL(url).hostname;
        } catch (e) {
            return url;
        }
    }
    
    function formatTimeAgo(dateString) {
        var now = new Date();
        var past = new Date(dateString);
        var diffMs = now - past;
        var diffMins = Math.round(diffMs / 60000);
        var diffHours = Math.round(diffMs / 3600000);
        var diffDays = Math.round(diffMs / 86400000);
        
        if (diffMins < 1) {
            return 'Just now';
        } else if (diffMins < 60) {
            return diffMins + ' min' + (diffMins !== 1 ? 's' : '') + ' ago';
        } else if (diffHours < 24) {
            return diffHours + ' hour' + (diffHours !== 1 ? 's' : '') + ' ago';
        } else {
            return diffDays + ' day' + (diffDays !== 1 ? 's' : '') + ' ago';
        }
    }
    
    // Export functionality
    window.AppSync = {
        syncPost: function(postId) {
            var $btn = $('.sync-post-btn[data-post-id="' + postId + '"]');
            if ($btn.length) {
                $btn.click();
            }
        },
        
        testTemplate: function(template, postId) {
            return $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'app_sync_test_template',
                    template: template,
                    post_id: postId,
                    nonce: appSyncAjax.nonce
                }
            });
        },
        
        showNotice: showNotice,
        
        refreshStatus: refreshSyncStatus
    };
    
})(jQuery);