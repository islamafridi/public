<?php
/**
 * Sync Manager class for App Sync Plugin
 */
class App_Sync_Manager {
    
    private $logger;
    
    public function __construct() {
        $this->logger = new App_Sync_Logger();
        
        // Admin menu for sync management
        add_action('admin_menu', array($this, 'add_sync_menu'));
        
        // AJAX handlers
        add_action('wp_ajax_app_sync_bulk_sync', array($this, 'ajax_bulk_sync'));
        add_action('wp_ajax_app_sync_export_logs', array($this, 'ajax_export_logs'));
        add_action('wp_ajax_app_sync_get_sync_status', array($this, 'ajax_get_sync_status'));
        
        // Add sync column to posts list
        add_filter('manage_posts_columns', array($this, 'add_sync_column'));
        add_action('manage_posts_custom_column', array($this, 'display_sync_column'), 10, 2);
        
        // Add bulk actions
        add_filter('bulk_actions-edit-post', array($this, 'add_bulk_sync_action'));
        add_filter('handle_bulk_actions-edit-post', array($this, 'handle_bulk_sync_action'), 10, 3);
    }
    
    public function add_sync_menu() {
        add_management_page(
            __('App Sync Manager', 'app-sync'),
            __('App Sync', 'app-sync'),
            'manage_options',
            'app-sync-manager',
            array($this, 'sync_manager_page')
        );
    }
    
    public function sync_manager_page() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'app-sync'));
        }
        
        $is_master = get_option('app_sync_is_master', 'no');
        $stats = $this->logger->get_sync_stats(30);
        $performance = $this->logger->get_performance_metrics();
        
        ?>
        <div class="wrap">
            <h1><?php _e('App Sync Manager', 'app-sync'); ?></h1>
            
            <div class="sync-overview">
                <div class="sync-stats-cards" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 30px;">
                    <div class="stats-card" style="background: #fff; border: 1px solid #ccd0d4; border-radius: 4px; padding: 20px;">
                        <h3 style="margin-top: 0; color: #1d2327;"><?php _e('30-Day Overview', 'app-sync'); ?></h3>
                        <div class="stat-item" style="margin-bottom: 10px;">
                            <strong style="color: #135e96;"><?php echo number_format($stats['total']); ?></strong>
                            <span><?php _e('Total Syncs', 'app-sync'); ?></span>
                        </div>
                        <div class="stat-item" style="margin-bottom: 10px;">
                            <strong style="color: #46b450;"><?php echo number_format($stats['successful']); ?></strong>
                            <span><?php _e('Successful', 'app-sync'); ?></span>
                        </div>
                        <div class="stat-item" style="margin-bottom: 10px;">
                            <strong style="color: #dc3232;"><?php echo number_format($stats['failed']); ?></strong>
                            <span><?php _e('Failed', 'app-sync'); ?></span>
                        </div>
                        <div class="stat-item">
                            <strong style="color: #50575e;"><?php echo $stats['success_rate']; ?>%</strong>
                            <span><?php _e('Success Rate', 'app-sync'); ?></span>
                        </div>
                    </div>
                    
                    <?php if ($is_master === 'yes'): ?>
                    <div class="stats-card" style="background: #fff; border: 1px solid #ccd0d4; border-radius: 4px; padding: 20px;">
                        <h3 style="margin-top: 0; color: #1d2327;"><?php _e('Quick Actions', 'app-sync'); ?></h3>
                        <p>
                            <button type="button" id="bulk-sync-all" class="button button-primary" style="width: 100%; margin-bottom: 10px;">
                                <?php _e('Sync All Posts', 'app-sync'); ?>
                            </button>
                        </p>
                        <p>
                            <button type="button" id="export-logs" class="button button-secondary" style="width: 100%; margin-bottom: 10px;">
                                <?php _e('Export Logs (CSV)', 'app-sync'); ?>
                            </button>
                        </p>
                        <p>
                            <button type="button" id="retry-failed" class="button button-secondary" style="width: 100%;">
                                <?php _e('Retry Failed Syncs', 'app-sync'); ?>
                            </button>
                        </p>
                    </div>
                    <?php else: ?>
                    <div class="stats-card" style="background: #fff; border: 1px solid #ccd0d4; border-radius: 4px; padding: 20px;">
                        <h3 style="margin-top: 0; color: #1d2327;"><?php _e('Child Site Actions', 'app-sync'); ?></h3>
                        <p>
                            <button type="button" id="export-logs" class="button button-secondary" style="width: 100%; margin-bottom: 10px;">
                                <?php _e('Export Logs (CSV)', 'app-sync'); ?>
                            </button>
                        </p>
                        <p>
                            <button type="button" id="clear-logs" class="button button-secondary" style="width: 100%;">
                                <?php _e('Clear Logs', 'app-sync'); ?>
                            </button>
                        </p>
                    </div>
                    
                    <div class="stats-card" style="background: #fff; border: 1px solid #ccd0d4; border-radius: 4px; padding: 20px;">
                        <h3 style="margin-top: 0; color: #1d2327;"><?php _e('System Status', 'app-sync'); ?></h3>
                        <div class="stat-item" style="margin-bottom: 10px;">
                            <span><?php _e('Plugin Version:', 'app-sync'); ?></span>
                            <strong><?php echo APP_SYNC_VERSION; ?></strong>
                        </div>
                        <div class="stat-item" style="margin-bottom: 10px;">
                            <span><?php _e('Site Type:', 'app-sync'); ?></span>
                            <strong><?php echo $is_master === 'yes' ? __('Master', 'app-sync') : __('Child', 'app-sync'); ?></strong>
                        </div>
                        <?php if ($is_master === 'yes'): ?>
                        <div class="stat-item" style="margin-bottom: 10px;">
                            <span><?php _e('Child Sites:', 'app-sync'); ?></span>
                            <strong><?php echo count(array_filter(explode("\n", get_option('app_sync_child_sites', '')))); ?></strong>
                        </div>
                        <div class="stat-item" style="margin-bottom: 10px;">
                            <span><?php _e('Sync Mode:', 'app-sync'); ?></span>
                            <strong><?php echo ucfirst(get_option('app_sync_sync_frequency', 'immediate')); ?></strong>
                        </div>
                        <?php endif; ?>
                        <div class="stat-item" style="margin-bottom: 10px;">
                            <span><?php _e('API Token:', 'app-sync'); ?></span>
                            <strong style="color: <?php echo !empty(get_option('app_sync_api_token')) ? '#46b450' : '#dc3232'; ?>;">
                                <?php echo !empty(get_option('app_sync_api_token')) ? __('Set', 'app-sync') : __('Not Set', 'app-sync'); ?>
                            </strong>
                        </div>
                        <div class="stat-item">
                            <span><?php _e('Falang Multi-lang:', 'app-sync'); ?></span>
                            <strong style="color: <?php echo $this->is_falang_active() ? '#46b450' : '#646970'; ?>;">
                                <?php echo $this->is_falang_active() ? __('Active', 'app-sync') : __('Not Active', 'app-sync'); ?>
                            </strong>
                        </div>
                    </div>
                </div>
                
                <?php if ($is_master === 'yes'): ?>
                <div id="bulk-sync-progress" style="display: none; margin-bottom: 20px;">
                    <div class="progress-container" style="background: #f0f0f1; border: 1px solid #c3c4c7; border-radius: 4px; padding: 20px;">
                        <h3><?php _e('Sync Progress', 'app-sync'); ?></h3>
                        <div class="progress-bar-container" style="background: #dcdcde; height: 20px; border-radius: 10px; overflow: hidden; margin-bottom: 10px;">
                            <div id="progress-bar" style="background: #0073aa; height: 100%; width: 0%; transition: width 0.3s ease;"></div>
                        </div>
                        <div id="progress-text">0 / 0</div>
                        <div id="progress-details" style="margin-top: 10px; font-size: 12px; color: #646970;"></div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            
            <div class="sync-tables-container" style="display: grid; grid-template-columns: <?php echo $is_master === 'yes' && !empty($performance['problematic_sites']) ? '1fr 1fr' : '1fr'; ?>; gap: 20px;">
                <?php if ($is_master === 'yes' && !empty($performance['problematic_sites'])): ?>
                <div class="problematic-sites">
                    <h2><?php _e('Sites with Issues', 'app-sync'); ?></h2>
                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th><?php _e('Site', 'app-sync'); ?></th>
                                <th><?php _e('Attempts', 'app-sync'); ?></th>
                                <th><?php _e('Failures', 'app-sync'); ?></th>
                                <th><?php _e('Failure Rate', 'app-sync'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($performance['problematic_sites'] as $site): ?>
                            <tr>
                                <td>
                                    <strong><?php echo esc_html(parse_url($site->site_url, PHP_URL_HOST)); ?></strong>
                                    <div style="font-size: 12px; color: #646970;"><?php echo esc_html($site->site_url); ?></div>
                                </td>
                                <td><?php echo number_format($site->total_attempts); ?></td>
                                <td style="color: #dc3232;"><?php echo number_format($site->failures); ?></td>
                                <td>
                                    <span style="color: <?php echo $site->failure_rate > 50 ? '#dc3232' : ($site->failure_rate > 25 ? '#dba617' : '#46b450'); ?>;">
                                        <?php echo $site->failure_rate; ?>%
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
                
                <div class="recent-activity">
                    <h2><?php _e('Recent Sync Activity', 'app-sync'); ?></h2>
                    <?php $this->display_recent_logs(20); ?>
                </div>
            </div> class="progress-bar-container" style="background: #dcdcde; height: 20px; border-radius: 10px; overflow: hidden; margin-bottom: 10px;">
                            <div id="progress-bar" style="background: #0073aa; height: 100%; width: 0%; transition: width 0.3s ease;"></div>
                        </div>
                        <div id="progress-text">0 / 0</div>
                        <div id="progress-details" style="margin-top: 10px; font-size: 12px; color: #646970;"></div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <div class="sync-tables-container" style="display: grid; grid-template-columns: <?php echo $is_master === 'yes' ? '1fr 1fr' : '1fr'; ?>; gap: 20px;">
                <?php if ($is_master === 'yes' && !empty($performance['problematic_sites'])): ?>
                <div class="problematic-sites">
                    <h2><?php _e('Sites with Issues', 'app-sync'); ?></h2>
                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th><?php _e('Site', 'app-sync'); ?></th>
                                <th><?php _e('Attempts', 'app-sync'); ?></th>
                                <th><?php _e('Failures', 'app-sync'); ?></th>
                                <th><?php _e('Failure Rate', 'app-sync'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($performance['problematic_sites'] as $site): ?>
                            <tr>
                                <td>
                                    <strong><?php echo esc_html(parse_url($site->site_url, PHP_URL_HOST)); ?></strong>
                                    <div style="font-size: 12px; color: #646970;"><?php echo esc_html($site->site_url); ?></div>
                                </td>
                                <td><?php echo number_format($site->total_attempts); ?></td>
                                <td style="color: #dc3232;"><?php echo number_format($site->failures); ?></td>
                                <td>
                                    <span style="color: <?php echo $site->failure_rate > 50 ? '#dc3232' : ($site->failure_rate > 25 ? '#dba617' : '#46b450'); ?>;">
                                        <?php echo $site->failure_rate; ?>%
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
                
                <div class="recent-activity">
                    <h2><?php _e('Recent Sync Activity', 'app-sync'); ?></h2>
                    <?php $this->display_recent_logs(20); ?>
                </div>
            </div>
            
            <?php if (!empty($performance['most_synced_posts'])): ?>
            <div class="most-synced-posts" style="margin-top: 30px;">
                <h2><?php _e('Most Frequently Synced Posts', 'app-sync'); ?></h2>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th><?php _e('Post', 'app-sync'); ?></th>
                            <th><?php _e('GP ID', 'app-sync'); ?></th>
                            <th><?php _e('Sync Count (30 days)', 'app-sync'); ?></th>
                            <th><?php _e('Actions', 'app-sync'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($performance['most_synced_posts'] as $post): ?>
                        <tr>
                            <td>
                                <a href="<?php echo get_edit_post_link($post->post_id); ?>" target="_blank">
                                    <?php echo esc_html($post->post_title ?: __('(No title)', 'app-sync')); ?>
                                </a>
                                <div style="font-size: 12px; color: #646970;">ID: <?php echo $post->post_id; ?></div>
                            </td>
                            <td><code><?php echo esc_html($post->gp_id); ?></code></td>
                            <td><?php echo number_format($post->sync_count); ?></td>
                            <td>
                                <button type="button" class="button button-small sync-single-post" data-post-id="<?php echo $post->post_id; ?>">
                                    <?php _e('Sync Now', 'app-sync'); ?>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
        
        <script>
        jQuery(document).ready(function($) {
            // Bulk sync all posts
            $('#bulk-sync-all').on('click', function() {
                if (!confirm('<?php _e('This will sync all posts with GP IDs to child sites. This may take a while. Continue?', 'app-sync'); ?>')) {
                    return;
                }
                
                var $btn = $(this);
                $btn.prop('disabled', true).text('<?php _e('Starting...', 'app-sync'); ?>');
                
                startBulkSync();
            });
            
            // Export logs
            $('#export-logs').on('click', function() {
                var $btn = $(this);
                $btn.prop('disabled', true).text('<?php _e('Exporting...', 'app-sync'); ?>');
                
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'app_sync_export_logs',
                        nonce: '<?php echo wp_create_nonce('app_sync_export_logs'); ?>',
                        days: 30
                    },
                    success: function(response) {
                        if (response.success) {
                            // Create download link
                            var blob = new Blob([response.data.csv], { type: 'text/csv' });
                            var url = window.URL.createObjectURL(blob);
                            var a = document.createElement('a');
                            a.href = url;
                            a.download = 'app-sync-logs-' + new Date().toISOString().slice(0, 10) + '.csv';
                            document.body.appendChild(a);
                            a.click();
                            window.URL.revokeObjectURL(url);
                            document.body.removeChild(a);
                        } else {
                            alert(response.data);
                        }
                    },
                    complete: function() {
                        $btn.prop('disabled', false).text('<?php _e('Export Logs (CSV)', 'app-sync'); ?>');
                    }
                });
            });
            
            // Retry failed syncs
            $('#retry-failed').on('click', function() {
                var $btn = $(this);
                $btn.prop('disabled', true).text('<?php _e('Retrying...', 'app-sync'); ?>');
                
                // Trigger retry hook
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'app_sync_retry_failed_manual',
                        nonce: '<?php echo wp_create_nonce('app_sync_retry_failed'); ?>'
                    },
                    success: function(response) {
                        if (response.success) {
                            alert('<?php _e('Retry process initiated. Check the activity log for results.', 'app-sync'); ?>');
                            setTimeout(function() { location.reload(); }, 2000);
                        } else {
                            alert(response.data);
                        }
                    },
                    complete: function() {
                        $btn.prop('disabled', false).text('<?php _e('Retry Failed Syncs', 'app-sync'); ?>');
                    }
                });
            });
            
            // Single post sync
            $('.sync-single-post').on('click', function() {
                var $btn = $(this);
                var postId = $btn.data('post-id');
                
                $btn.prop('disabled', true).text('<?php _e('Syncing...', 'app-sync'); ?>');
                
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'app_sync_manual_sync',
                        post_id: postId,
                        nonce: '<?php echo wp_create_nonce('app_sync_manual_sync'); ?>'
                    },
                    success: function(response) {
                        if (response.success) {
                            $btn.text('<?php _e('Synced!', 'app-sync'); ?>').css('color', '#46b450');
                            setTimeout(function() {
                                $btn.text('<?php _e('Sync Now', 'app-sync'); ?>').css('color', '');
                            }, 3000);
                        } else {
                            $btn.text('<?php _e('Failed', 'app-sync'); ?>').css('color', '#dc3232');
                            setTimeout(function() {
                                $btn.text('<?php _e('Sync Now', 'app-sync'); ?>').css('color', '');
                            }, 3000);
                        }
                    },
                    complete: function() {
                        $btn.prop('disabled', false);
                    }
                });
            });
            
            function startBulkSync() {
                $('#bulk-sync-progress').show();
                
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'app_sync_bulk_sync',
                        nonce: '<?php echo wp_create_nonce('app_sync_bulk_sync'); ?>'
                    },
                    success: function(response) {
                        if (response.success) {
                            updateBulkSyncProgress(response.data);
                        } else {
                            alert(response.data);
                            $('#bulk-sync-all').prop('disabled', false).text('<?php _e('Sync All Posts', 'app-sync'); ?>');
                        }
                    }
                });
            }
            
            function updateBulkSyncProgress(data) {
                var percentage = (data.completed / data.total) * 100;
                $('#progress-bar').css('width', percentage + '%');
                $('#progress-text').text(data.completed + ' / ' + data.total);
                $('#progress-details').text(data.message || '');
                
                if (data.completed < data.total) {
                    // Continue syncing
                    setTimeout(function() {
                        $.ajax({
                            url: ajaxurl,
                            type: 'POST',
                            data: {
                                action: 'app_sync_get_sync_status',
                                nonce: '<?php echo wp_create_nonce('app_sync_get_sync_status'); ?>'
                            },
                            success: function(response) {
                                if (response.success) {
                                    updateBulkSyncProgress(response.data);
                                }
                            }
                        });
                    }, 2000);
                } else {
                    // Sync complete
                    $('#progress-details').text('<?php _e('Sync completed!', 'app-sync'); ?>');
                    $('#bulk-sync-all').prop('disabled', false).text('<?php _e('Sync All Posts', 'app-sync'); ?>');
                    setTimeout(function() {
                        location.reload();
                    }, 3000);
                }
            }
        });
        </script>
        <?php
    }
    
    public function ajax_bulk_sync() {
        check_ajax_referer('app_sync_bulk_sync', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Permission denied', 'app-sync'));
        }
        
        // Get all posts with GP IDs
        $posts = get_posts(array(
            'post_type' => 'post',
            'posts_per_page' => -1,
            'meta_query' => array(
                array(
                    'key' => 'wp_GP_ID',
                    'compare' => 'EXISTS'
                )
            ),
            'fields' => 'ids'
        ));
        
        if (empty($posts)) {
            wp_send_json_error(__('No posts with GP ID found', 'app-sync'));
        }
        
        // Store bulk sync data in transient
        set_transient('app_sync_bulk_progress', array(
            'total' => count($posts),
            'completed' => 0,
            'posts' => $posts,
            'current_index' => 0,
            'started' => time()
        ), 3600);
        
        // Start syncing first batch
        $this->process_bulk_sync_batch();
        
        wp_send_json_success(array(
            'total' => count($posts),
            'completed' => 0,
            'message' => __('Starting bulk sync...', 'app-sync')
        ));
    }
    
    public function ajax_get_sync_status() {
        check_ajax_referer('app_sync_get_sync_status', 'nonce');
        
        $progress = get_transient('app_sync_bulk_progress');
        
        if (!$progress) {
            wp_send_json_error(__('No sync in progress', 'app-sync'));
        }
        
        // Process next batch
        if ($progress['completed'] < $progress['total']) {
            $this->process_bulk_sync_batch();
            $progress = get_transient('app_sync_bulk_progress');
        }
        
        wp_send_json_success(array(
            'total' => $progress['total'],
            'completed' => $progress['completed'],
            'message' => sprintf(__('Processed %d of %d posts', 'app-sync'), $progress['completed'], $progress['total'])
        ));
    }
    
    private function process_bulk_sync_batch($batch_size = 5) {
        $progress = get_transient('app_sync_bulk_progress');
        
        if (!$progress || $progress['completed'] >= $progress['total']) {
            return;
        }
        
        $end_index = min($progress['current_index'] + $batch_size, $progress['total']);
        
        for ($i = $progress['current_index']; $i < $end_index; $i++) {
            $post_id = $progress['posts'][$i];
            
            // Trigger sync for this post
            do_action('app_sync_manual_trigger', $post_id);
            
            $progress['completed']++;
        }
        
        $progress['current_index'] = $end_index;
        
        // Update progress
        set_transient('app_sync_bulk_progress', $progress, 3600);
        
        // Clean up if complete
        if ($progress['completed'] >= $progress['total']) {
            delete_transient('app_sync_bulk_progress');
        }
    }
    
    public function ajax_export_logs() {
        check_ajax_referer('app_sync_export_logs', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Permission denied', 'app-sync'));
        }
        
        $days = intval($_POST['days'] ?? 30);
        $status = sanitize_text_field($_POST['status'] ?? '');
        
        $csv_content = $this->logger->export_logs_csv($days, $status);
        
        if (empty($csv_content)) {
            wp_send_json_error(__('No logs found for the specified period', 'app-sync'));
        }
        
        wp_send_json_success(array(
            'csv' => $csv_content,
            'filename' => 'app-sync-logs-' . date('Y-m-d') . '.csv'
        ));
    }
    
    public function add_sync_column($columns) {
        // Only add on master sites
        if (get_option('app_sync_is_master', 'no') === 'yes') {
            $columns['app_sync_status'] = __('Sync Status', 'app-sync');
        }
        return $columns;
    }
    
    public function display_sync_column($column, $post_id) {
        if ($column === 'app_sync_status') {
            $gp_id = get_post_meta($post_id, 'wp_GP_ID', true);
            
            if (!$gp_id) {
                echo '<span style="color: #646970;">' . __('No GP ID', 'app-sync') . '</span>';
                return;
            }
            
            $recent_logs = $this->logger->get_logs($post_id, 1);
            
            if (empty($recent_logs)) {
                echo '<span style="color: #646970;">' . __('Never synced', 'app-sync') . '</span>';
                echo '<br><button type="button" class="button button-small sync-post-btn" data-post-id="' . $post_id . '">' . __('Sync Now', 'app-sync') . '</button>';
                return;
            }
            
            $last_log = $recent_logs[0];
            $time_ago = human_time_diff(strtotime($last_log->created_at), current_time('timestamp'));
            
            if ($last_log->status === 'success') {
                echo '<span style="color: #46b450;">✓ ' . __('Success', 'app-sync') . '</span>';
            } else {
                echo '<span style="color: #dc3232;">✗ ' . __('Failed', 'app-sync') . '</span>';
            }
            
            echo '<br><small>' . sprintf(__('%s ago', 'app-sync'), $time_ago) . '</small>';
            echo '<br><button type="button" class="button button-small sync-post-btn" data-post-id="' . $post_id . '">' . __('Sync Now', 'app-sync') . '</button>';
        }
    }
    
    public function add_bulk_sync_action($bulk_actions) {
        if (get_option('app_sync_is_master', 'no') === 'yes') {
            $bulk_actions['app_sync_selected'] = __('Sync Selected Posts', 'app-sync');
        }
        return $bulk_actions;
    }
    
    public function handle_bulk_sync_action($redirect_to, $action, $post_ids) {
        if ($action !== 'app_sync_selected') {
            return $redirect_to;
        }
        
        if (get_option('app_sync_is_master', 'no') !== 'yes') {
            return $redirect_to;
        }
        
        $synced_count = 0;
        
        foreach ($post_ids as $post_id) {
            $gp_id = get_post_meta($post_id, 'wp_GP_ID', true);
            if ($gp_id) {
                do_action('app_sync_manual_trigger', $post_id);
                $synced_count++;
            }
        }
        
        $redirect_to = add_query_arg('app_sync_bulk_result', $synced_count, $redirect_to);
        return $redirect_to;
    }
    
    private function display_recent_logs($limit = 20) {
        $logs = $this->logger->get_recent_logs($limit);
        
        if (empty($logs)) {
            echo '<p>' . __('No sync activity found.', 'app-sync') . '</p>';
            return;
        }
        
        echo '<table class="wp-list-table widefat fixed striped">';
        echo '<thead><tr>';
        echo '<th>' . __('Time', 'app-sync') . '</th>';
        echo '<th>' . __('Post', 'app-sync') . '</th>';
        echo '<th>' . __('Site', 'app-sync') . '</th>';
        echo '<th>' . __('Action', 'app-sync') . '</th>';
        echo '<th>' . __('Status', 'app-sync') . '</th>';
        echo '</tr></thead>';
        echo '<tbody>';
        
        foreach ($logs as $log) {
            $post_title = get_the_title($log->post_id) ?: __('(No title)', 'app-sync');
            $site_host = parse_url($log->site_url, PHP_URL_HOST) ?: $log->site_url;
            $time_ago = human_time_diff(strtotime($log->created_at), current_time('timestamp')) . ' ' . __('ago', 'app-sync');
            
            $status_color = $log->status === 'success' ? '#46b450' : '#dc3232';
            $status_icon = $log->status === 'success' ? '✓' : '✗';
            
            echo '<tr>';
            echo '<td><small>' . esc_html($time_ago) . '</small></td>';
            echo '<td>';
            echo '<a href="' . get_edit_post_link($log->post_id) . '" target="_blank">' . esc_html($post_title) . '</a>';
            if ($log->gp_id) {
                echo '<br><small>GP: ' . esc_html($log->gp_id) . '</small>';
            }
            echo '</td>';
            echo '<td>' . esc_html($site_host) . '</td>';
            echo '<td>' . esc_html(ucfirst(str_replace('_', ' ', $log->action))) . '</td>';
            echo '<td>';
            echo '<span style="color: ' . $status_color . ';">' . $status_icon . ' ' . esc_html(ucfirst($log->status)) . '</span>';
            if ($log->response_code) {
                echo '<br><small>HTTP ' . esc_html($log->response_code) . '</small>';
            }
            if ($log->status === 'failed' && $log->response_message) {
                echo '<br><small style="color: #dc3232;" title="' . esc_attr($log->response_message) . '">' . 
                     esc_html(wp_trim_words($log->response_message, 5)) . '</small>';
            }
            echo '</td>';
            echo '</tr>';
        }
        
        echo '</tbody></table>';
    }

    /**
     * Check if Falang is active and properly configured
     */
    private function is_falang_active() {
        return class_exists('Falang\Core\Falang_Core') && class_exists('Falang\Model\Falang_Model');
    }
}