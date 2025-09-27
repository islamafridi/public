<?php
/**
 * Logger class for App Sync Plugin
 */
class App_Sync_Logger {
    
    public function __construct() {
        add_action('app_sync_cleanup_logs', array($this, 'cleanup_old_logs'));
    }
    
    /**
     * Log sync events
     *
     * @param int $post_id Post ID
     * @param string $site_url Target site URL
     * @param string $action Action performed (sync_success, sync_failed, retry_sync, etc.)
     * @param string $status Status (success, failed, info)
     * @param int|null $response_code HTTP response code
     * @param string $message Response message
     */
    public function log($post_id, $site_url, $action, $status, $response_code = null, $message = '') {
        global $wpdb;

        $table_name = $wpdb->prefix . 'app_sync_logs';
        $gp_id = get_post_meta($post_id, 'wp_GP_ID', true);
        $post_title = get_the_title($post_id) ?: __('(No title)', 'app-sync');

        // Add Falang information if this is a multi-language update
        if (strpos($action, 'multilang') !== false || strpos($action, 'falang') !== false) {
            $message = '[FALANG] ' . $message;
        }

        $wpdb->insert(
            $table_name,
            array(
                'post_id' => $post_id,
                'post_title' => $post_title,
                'gp_id' => $gp_id,
                'site_url' => $site_url,
                'action' => $action,
                'status' => $status,
                'response_code' => $response_code,
                'response_message' => $message,
                'created_at' => current_time('mysql')
            ),
            array(
                '%d',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%d',
                '%s',
                '%s'
            )
        );
        
        // Also log to WordPress debug log if WP_DEBUG is enabled
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log(sprintf(
                'App Sync: [%s] Post: %s (ID: %d), GP ID: %s, Site: %s, Action: %s, Status: %s, Message: %s',
                current_time('Y-m-d H:i:s'),
                $post_title,
                $post_id,
                $gp_id,
                $site_url,
                $action,
                $status,
                $message
            ));
        }
    }
    
    /**
     * Get logs for a specific post
     *
     * @param int $post_id Post ID
     * @param int $limit Number of logs to retrieve
     * @param string $status Filter by status (optional)
     * @return array
     */
    public function get_logs($post_id, $limit = 50, $status = '') {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'app_sync_logs';
        
        $where_clause = $wpdb->prepare('WHERE post_id = %d', $post_id);
        
        if (!empty($status)) {
            $where_clause .= $wpdb->prepare(' AND status = %s', $status);
        }
        
        $query = $wpdb->prepare(
            "SELECT * FROM $table_name $where_clause ORDER BY created_at DESC LIMIT %d",
            $limit
        );
        
        return $wpdb->get_results($query);
    }
    
    /**
     * Get recent logs across all posts
     *
     * @param int $limit Number of logs to retrieve
     * @param string $status Filter by status (optional)
     * @return array
     */
    public function get_recent_logs($limit = 100, $status = '') {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'app_sync_logs';
        
        $where_clause = '';
        if (!empty($status)) {
            $where_clause = $wpdb->prepare('WHERE status = %s', $status);
        }
        
        $query = $wpdb->prepare(
            "SELECT * FROM $table_name $where_clause ORDER BY created_at DESC LIMIT %d",
            $limit
        );
        
        return $wpdb->get_results($query);
    }
    
    /**
     * Get sync statistics
     *
     * @param int $days Number of days to look back
     * @return array
     */
    public function get_sync_stats($days = 30) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'app_sync_logs';
        
        $stats = array();
        
        // Total syncs in period
        $stats['total'] = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table_name WHERE created_at > DATE_SUB(NOW(), INTERVAL %d DAY)",
            $days
        ));
        
        // Successful syncs
        $stats['successful'] = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table_name WHERE status = 'success' AND created_at > DATE_SUB(NOW(), INTERVAL %d DAY)",
            $days
        ));
        
        // Failed syncs
        $stats['failed'] = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table_name WHERE status = 'failed' AND created_at > DATE_SUB(NOW(), INTERVAL %d DAY)",
            $days
        ));
        
        // Success rate
        $stats['success_rate'] = $stats['total'] > 0 ? round(($stats['successful'] / $stats['total']) * 100, 2) : 0;
        
        // Most active sites
        $stats['top_sites'] = $wpdb->get_results($wpdb->prepare(
            "SELECT site_url, COUNT(*) as sync_count FROM $table_name 
             WHERE created_at > DATE_SUB(NOW(), INTERVAL %d DAY) 
             GROUP BY site_url ORDER BY sync_count DESC LIMIT 10",
            $days
        ));
        
        // Daily sync counts
        $stats['daily_counts'] = $wpdb->get_results($wpdb->prepare(
            "SELECT DATE(created_at) as sync_date, 
                    COUNT(*) as total_syncs,
                    SUM(CASE WHEN status = 'success' THEN 1 ELSE 0 END) as successful_syncs,
                    SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed_syncs
             FROM $table_name 
             WHERE created_at > DATE_SUB(NOW(), INTERVAL %d DAY)
             GROUP BY DATE(created_at) 
             ORDER BY sync_date DESC",
            $days
        ));
        
        return $stats;
    }
    
    /**
     * Get failed syncs that need retry
     *
     * @param int $max_retries Maximum retry attempts
     * @param int $hours_back Hours to look back for failures
     * @return array
     */
    public function get_failed_syncs_for_retry($max_retries = 3, $hours_back = 24) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'app_sync_logs';
        
        return $wpdb->get_results($wpdb->prepare(
            "SELECT DISTINCT post_id, site_url, gp_id
             FROM $table_name 
             WHERE status = 'failed' 
             AND action != 'retry_sync'
             AND created_at > DATE_SUB(NOW(), INTERVAL %d HOUR)
             AND (SELECT COUNT(*) FROM $table_name t2 
                  WHERE t2.post_id = $table_name.post_id 
                  AND t2.site_url = $table_name.site_url 
                  AND t2.action = 'retry_sync') < %d
             LIMIT 20",
            $hours_back,
            $max_retries
        ));
    }
    
    /**
     * Clean up old log entries
     */
    public function cleanup_old_logs() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'app_sync_logs';
        $retention_days = get_option('app_sync_log_retention', 30);
        
        $deleted = $wpdb->query($wpdb->prepare(
            "DELETE FROM $table_name WHERE created_at < DATE_SUB(NOW(), INTERVAL %d DAY)",
            $retention_days
        ));
        
        if ($deleted !== false) {
            error_log("App Sync: Cleaned up $deleted old log entries older than $retention_days days");
        }
        
        return $deleted;
    }
    
    /**
     * Get log counts by status
     *
     * @param int $days Number of days to look back
     * @return array
     */
    public function get_log_counts($days = 7) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'app_sync_logs';
        
        return $wpdb->get_results($wpdb->prepare(
            "SELECT status, COUNT(*) as count 
             FROM $table_name 
             WHERE created_at > DATE_SUB(NOW(), INTERVAL %d DAY)
             GROUP BY status 
             ORDER BY count DESC",
            $days
        ));
    }
    
    /**
     * Export logs to CSV
     *
     * @param int $days Number of days to export
     * @param string $status Filter by status (optional)
     * @return string CSV content
     */
    public function export_logs_csv($days = 30, $status = '') {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'app_sync_logs';
        
        $where_clause = $wpdb->prepare('WHERE created_at > DATE_SUB(NOW(), INTERVAL %d DAY)', $days);
        
        if (!empty($status)) {
            $where_clause .= $wpdb->prepare(' AND status = %s', $status);
        }
        
        $logs = $wpdb->get_results(
            "SELECT * FROM $table_name $where_clause ORDER BY created_at DESC"
        );
        
        if (empty($logs)) {
            return '';
        }
        
        $csv_content = "ID,Post ID,GP ID,Site URL,Action,Status,Response Code,Response Message,Created At\n";
        
        foreach ($logs as $log) {
            $csv_content .= sprintf(
                "%d,%d,%s,%s,%s,%s,%s,\"%s\",%s\n",
                $log->id,
                $log->post_id,
                $log->gp_id,
                $log->site_url,
                $log->action,
                $log->status,
                $log->response_code ?: '',
                str_replace('"', '""', $log->response_message),
                $log->created_at
            );
        }
        
        return $csv_content;
    }
    
    /**
     * Get sync performance metrics
     *
     * @return array
     */
    public function get_performance_metrics() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'app_sync_logs';
        
        $metrics = array();
        
        // Average response times (if we were tracking them)
        // For now, we'll focus on success rates and frequency
        
        // Hourly sync distribution (last 24 hours)
        $metrics['hourly_distribution'] = $wpdb->get_results(
            "SELECT HOUR(created_at) as hour, COUNT(*) as count
             FROM $table_name 
             WHERE created_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)
             GROUP BY HOUR(created_at)
             ORDER BY hour"
        );
        
        // Most problematic sites (highest failure rate)
        $metrics['problematic_sites'] = $wpdb->get_results(
            "SELECT site_url,
                    COUNT(*) as total_attempts,
                    SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failures,
                    ROUND((SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) / COUNT(*)) * 100, 2) as failure_rate
             FROM $table_name 
             WHERE created_at > DATE_SUB(NOW(), INTERVAL 7 DAY)
             AND site_url != ''
             GROUP BY site_url
             HAVING total_attempts >= 5 AND failure_rate > 0
             ORDER BY failure_rate DESC, total_attempts DESC
             LIMIT 10"
        );
        
        // Most synced posts
        $metrics['most_synced_posts'] = $wpdb->get_results(
            "SELECT p.post_title, l.post_id, l.gp_id, COUNT(*) as sync_count
             FROM $table_name l
             LEFT JOIN {$wpdb->posts} p ON l.post_id = p.ID
             WHERE l.created_at > DATE_SUB(NOW(), INTERVAL 30 DAY)
             GROUP BY l.post_id
             ORDER BY sync_count DESC
             LIMIT 10"
        );
        
        return $metrics;
    }
}