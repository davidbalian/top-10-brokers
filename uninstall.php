<?php
// If uninstall not called from WordPress, exit
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Define the plugin prefix for options and transients
define('TOP10_BROKERS_PREFIX', 'top10_brokers_');

/**
 * Cleanup function for the uninstallation process
 */
function top10_brokers_cleanup() {
    global $wpdb;
    
    // Remove all plugin options
    delete_option('top10_brokers_options');
    
    // Clear all plugin transients
    $wpdb->query(
        $wpdb->prepare(
            "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s",
            $wpdb->esc_like('_transient_' . TOP10_BROKERS_PREFIX) . '%',
            $wpdb->esc_like('_transient_timeout_' . TOP10_BROKERS_PREFIX) . '%'
        )
    );
    
    // Remove all posts of type 'broker'
    $posts = get_posts(array(
        'post_type' => 'broker',
        'numberposts' => -1,
        'post_status' => 'any'
    ));
    
    foreach ($posts as $post) {
        // Remove post meta
        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM {$wpdb->postmeta} WHERE post_id = %d",
                $post->ID
            )
        );
        
        // Remove the post
        wp_delete_post($post->ID, true);
    }
    
    // Remove all terms from broker-category taxonomy
    $terms = get_terms(array(
        'taxonomy' => 'broker-category',
        'hide_empty' => false
    ));
    
    if (!is_wp_error($terms)) {
        foreach ($terms as $term) {
            wp_delete_term($term->term_id, 'broker-category');
        }
    }
    
    // Remove taxonomy relationships
    $wpdb->query(
        $wpdb->prepare(
            "DELETE FROM {$wpdb->term_relationships} WHERE term_taxonomy_id IN (
                SELECT tt.term_taxonomy_id FROM {$wpdb->term_taxonomy} tt
                WHERE tt.taxonomy = %s
            )",
            'broker-category'
        )
    );
    
    // Clean up any remaining metadata
    $wpdb->query(
        $wpdb->prepare(
            "DELETE FROM {$wpdb->postmeta} WHERE meta_key LIKE %s",
            $wpdb->esc_like(TOP10_BROKERS_PREFIX) . '%'
        )
    );
    
    // Remove user meta related to the plugin
    $wpdb->query(
        $wpdb->prepare(
            "DELETE FROM {$wpdb->usermeta} WHERE meta_key LIKE %s",
            $wpdb->esc_like(TOP10_BROKERS_PREFIX) . '%'
        )
    );
    
    // Clear any scheduled hooks
    wp_clear_scheduled_hook('top10_brokers_daily_cleanup');
    
    // Flush rewrite rules
    flush_rewrite_rules();
}

// Run the cleanup
top10_brokers_cleanup();