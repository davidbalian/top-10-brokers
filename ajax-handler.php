<?php

/**
 * Rate limiting functionality for AJAX requests
 */
function top10_brokers_check_rate_limit() {
    if (!defined('DOING_AJAX') || !DOING_AJAX) {
        return true;
    }

    $user_ip = $_SERVER['REMOTE_ADDR'];
    $action = isset($_REQUEST['action']) ? sanitize_text_field($_REQUEST['action']) : '';
    
    if (empty($action) || strpos($action, 'top10_brokers_') !== 0) {
        return true;
    }

    $transient_name = 'top10_brokers_rate_limit_' . md5($user_ip . '_' . $action);
    $limit_count = get_transient($transient_name);

    if ($limit_count === false) {
        set_transient($transient_name, 1, MINUTE_IN_SECONDS);
        return true;
    }

    if ($limit_count >= 30) { // Maximum 30 requests per minute
        Top10_Brokers_Core::log("Rate limit exceeded for IP: {$user_ip}, Action: {$action}", 'warning');
        wp_send_json_error(array(
            'message' => __('Too many requests. Please try again later.', 'top10-brokers')
        ), 429);
        exit;
    }

    set_transient($transient_name, $limit_count + 1, MINUTE_IN_SECONDS);
    return true;
}

// Add rate limiting check to all AJAX handlers
add_action('init', function() {
    $ajax_actions = array(
        'top10_brokers_get_taxonomies',
        'top10_brokers_get_taxonomies_for_post_type',
        'top10_brokers_get_terms_for_taxonomy',
        'top10_brokers_get_sample_posts',
        'top10_brokers_get_post_meta_fields',
        'top10_brokers_get_brokers'
    );

    foreach ($ajax_actions as $action) {
        add_action('wp_ajax_' . $action, 'top10_brokers_check_rate_limit', 1);
        add_action('wp_ajax_nopriv_' . $action, 'top10_brokers_check_rate_limit', 1);
    }
});

function top10_brokers_enqueue_admin_assets($hook) {
    if ($hook !== 'toplevel_page_top10-brokers') {
        return;
    }
    wp_enqueue_script('top10-brokers-admin-script', TOP10_BROKERS_PLUGIN_URL . 'assets/js/admin-script.js', array('jquery'), TOP10_BROKERS_VERSION, true);
    wp_localize_script('top10-brokers-admin-script', 'top10BrokersAjax', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('top10_brokers_nonce')
    ));
}
add_action('admin_enqueue_scripts', 'top10_brokers_enqueue_admin_assets');

function top10_brokers_get_taxonomies() {
    // Security check: Verify nonce
    check_ajax_referer('top10_brokers_nonce', 'nonce');
    
    // Security check: Verify user capabilities
    if (!current_user_can('manage_options')) {
        wp_send_json_error(__('You do not have permission to perform this action', 'top10-brokers'));
        return;
    }

    $taxonomies = get_taxonomies(array('public' => true), 'objects');
    $options = array();
    foreach ($taxonomies as $taxonomy) {
        $options[$taxonomy->name] = $taxonomy->label;
    }
    wp_send_json_success($options);
}
add_action('wp_ajax_top10_brokers_get_taxonomies', 'top10_brokers_get_taxonomies');

// AJAX handler for getting taxonomies for a specific post type
function top10_brokers_get_taxonomies_for_post_type() {
    // Security check: Verify nonce
    check_ajax_referer('top10_brokers_nonce', 'nonce');
    
    // Security check: Verify user capabilities
    if (!current_user_can('manage_options')) {
        wp_send_json_error(__('You do not have permission to perform this action', 'top10-brokers'));
        return;
    }
    
    $post_type = isset($_POST['post_type']) ? sanitize_text_field($_POST['post_type']) : '';
    if (empty($post_type)) {
        wp_send_json_error(__('No post type specified', 'top10-brokers'));
        return;
    }
    
    $taxonomies = get_object_taxonomies($post_type, 'objects');
    $options = array();
    
    foreach ($taxonomies as $taxonomy) {
        if ($taxonomy->public || $taxonomy->publicly_queryable || $taxonomy->show_ui) {
            $options[$taxonomy->name] = $taxonomy->labels->singular_name;
        }
    }
    
    wp_send_json_success($options);
}
add_action('wp_ajax_top10_brokers_get_taxonomies_for_post_type', 'top10_brokers_get_taxonomies_for_post_type');

// AJAX handler for getting terms for a specific taxonomy
function top10_brokers_get_terms_for_taxonomy() {
    // Security check: Verify nonce
    check_ajax_referer('top10_brokers_nonce', 'nonce');
    
    // Security check: Verify user capabilities
    if (!current_user_can('manage_options')) {
        wp_send_json_error(__('You do not have permission to perform this action', 'top10-brokers'));
        return;
    }
    
    $taxonomy = isset($_POST['taxonomy']) ? sanitize_text_field($_POST['taxonomy']) : '';
    if (empty($taxonomy)) {
        wp_send_json_error(__('No taxonomy specified', 'top10-brokers'));
        return;
    }
    
    $terms = get_terms(array(
        'taxonomy' => $taxonomy,
        'hide_empty' => false
    ));
    
    if (is_wp_error($terms)) {
        wp_send_json_error($terms->get_error_message());
        return;
    }
    
    $term_data = array();
    foreach ($terms as $term) {
        $term_data[] = array(
            'id' => $term->term_id,
            'slug' => $term->slug,
            'name' => $term->name
        );
    }
    
    wp_send_json_success($term_data);
}
add_action('wp_ajax_top10_brokers_get_terms_for_taxonomy', 'top10_brokers_get_terms_for_taxonomy');

// AJAX handler for getting sample posts from a category
function top10_brokers_get_sample_posts() {
    // Security check: Verify nonce
    check_ajax_referer('top10_brokers_nonce', 'nonce');
    
    // Security check: Verify user capabilities
    if (!current_user_can('manage_options')) {
        wp_send_json_error(__('You do not have permission to perform this action', 'top10-brokers'));
        return;
    }
    
    $post_type = isset($_POST['post_type']) ? sanitize_text_field($_POST['post_type']) : '';
    $taxonomy = isset($_POST['taxonomy']) ? sanitize_text_field($_POST['taxonomy']) : '';
    $term = isset($_POST['term']) ? sanitize_text_field($_POST['term']) : '';
    
    if (empty($post_type)) {
        wp_send_json_error(__('No post type specified', 'top10-brokers'));
        return;
    }
    
    // Get 3 latest posts from the selected category
    $args = array(
        'post_type' => $post_type,
        'posts_per_page' => 3,
        'orderby' => 'date',
        'order' => 'DESC',
    );
    
    if (!empty($term) && !empty($taxonomy)) {
        $args['tax_query'] = array(
            array(
                'taxonomy' => $taxonomy,
                'field'    => 'slug',
                'terms'    => $term,
            ),
        );
    }
    
    $recent_posts = get_posts($args);
    $posts_data = array();
    
    foreach ($recent_posts as $post) {
        $posts_data[] = array(
            'id' => $post->ID,
            'title' => $post->post_title
        );
    }
    
    wp_send_json_success($posts_data);
}
add_action('wp_ajax_top10_brokers_get_sample_posts', 'top10_brokers_get_sample_posts');

// New AJAX handler for getting meta fields for a specific post
function top10_brokers_get_post_meta_fields() {
    check_ajax_referer('top10_brokers_nonce', 'nonce');
    
    $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
    
    if (empty($post_id)) {
        wp_send_json_error(__('No post ID specified', 'top10-brokers'));
        return;
    }
    
    $post_meta = get_post_meta($post_id);
    $meta_fields = array();
    
    if (!empty($post_meta)) {
        foreach ($post_meta as $key => $values) {
            if (is_protected_meta($key, 'post')) {
                continue; // Skip protected meta fields
            }
            $value = is_array($values) && !empty($values) ? maybe_unserialize($values[0]) : '';
            if (is_array($value) || is_object($value)) {
                $value = 'Complex value';
            } else {
                $value = substr(sanitize_text_field($value), 0, 30);
                if (strlen($value) > 29) {
                    $value .= '...';
                }
            }
            $meta_fields[$key] = $key . ': ' . $value;
        }
    }
    
    wp_send_json_success($meta_fields);
}
add_action('wp_ajax_top10_brokers_get_post_meta_fields', 'top10_brokers_get_post_meta_fields');

// This function is now moved to a common location in shortcode-render.php
// and will be used by both the shortcode and AJAX handlers
function top10_brokers_ajax_get_brokers() {
    check_ajax_referer('top10_brokers_nonce', 'nonce');
    
    $category = isset($_POST['category']) ? sanitize_text_field($_POST['category']) : '';
    $brokers = top10_brokers_get_brokers($category);
    
    wp_send_json_success($brokers);
}
add_action('wp_ajax_top10_brokers_get_brokers', 'top10_brokers_ajax_get_brokers');
?>