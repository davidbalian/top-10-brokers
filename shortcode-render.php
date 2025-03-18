<?php

function top10_brokers_render_table($atts) {
    $atts = shortcode_atts(array(
        'category' => '',
        'post_type' => '',
        'taxonomy' => '',
        'limit' => '',
        'rating_field' => '',
        'button_text' => '',
        'cache' => 'yes',
        'use_random_rating' => ''
    ), $atts, 'top10_brokers_table');

    // Get options from settings
    $options = get_option('top10_brokers_options');
    
    // Get button colors
    $button_bg_color = isset($options['top10_brokers_button_bg_color']) ? $options['top10_brokers_button_bg_color'] : '#4CAF50';
    $button_text_color = isset($options['top10_brokers_button_text_color']) ? $options['top10_brokers_button_text_color'] : '#ffffff';
    
    // Add button colors to JavaScript
    wp_localize_script('top10-brokers-script', 'top10BrokersButtonColors', array(
        'bgColor' => $button_bg_color,
        'textColor' => $button_text_color
    ));

    // Use shortcode attributes if provided, otherwise use settings
    $post_type = !empty($atts['post_type']) ? sanitize_text_field($atts['post_type']) : (isset($options['top10_brokers_post_type']) ? $options['top10_brokers_post_type'] : 'broker');
    $taxonomy = !empty($atts['taxonomy']) ? sanitize_text_field($atts['taxonomy']) : (isset($options['top10_brokers_taxonomy']) ? $options['top10_brokers_taxonomy'] : 'broker-category');
    $category = !empty($atts['category']) ? sanitize_text_field($atts['category']) : (isset($options['top10_brokers_term']) ? $options['top10_brokers_term'] : '');
    $limit = !empty($atts['limit']) ? intval($atts['limit']) : (isset($options['top10_brokers_limit']) ? intval($options['top10_brokers_limit']) : 10);
    $rating_field = !empty($atts['rating_field']) ? sanitize_text_field($atts['rating_field']) : (isset($options['top10_brokers_rating_field']) ? $options['top10_brokers_rating_field'] : 'rating_meta_key');
    $button_text = !empty($atts['button_text']) ? sanitize_text_field($atts['button_text']) : (isset($options['top10_brokers_button_text']) ? $options['top10_brokers_button_text'] : __('Learn More', 'top10-brokers'));
    $use_cache = ($atts['cache'] === 'yes');
    $use_random_rating = !empty($atts['use_random_rating']) ? $atts['use_random_rating'] : (isset($options['top10_brokers_use_random_rating']) ? $options['top10_brokers_use_random_rating'] : 'no');

    // Debug log the parameters
    error_log('Rendering table with parameters: ' . print_r(array(
        'post_type' => $post_type,
        'taxonomy' => $taxonomy,
        'category' => $category,
        'limit' => $limit,
        'rating_field' => $rating_field
    ), true));

    // Generate a unique cache key based on the parameters
    $cache_key = 'top10_brokers_cache_' . md5(serialize(array(
        'post_type' => $post_type,
        'taxonomy' => $taxonomy,
        'category' => $category,
        'limit' => $limit,
        'rating_field' => $rating_field,
        'button_text' => $button_text,
        'use_random_rating' => $use_random_rating
    )));

    // Try to get cached content
    $output = false;
    if ($use_cache) {
        $output = get_transient($cache_key);
    }

    // If no cached content or cache disabled, generate the content
    if ($output === false) {
        // Fetch items based on the settings
        $items = top10_brokers_get_items($post_type, $taxonomy, $category, $limit, $rating_field, $use_random_rating);
        error_log('Items before sorting: ' . print_r($items, true));
        
        ob_start();
        
        // Allow theme to override the template
        $template_path = locate_template('top10-brokers/table-template.php');
        if ($template_path) {
            // Pass variables to the template
            set_query_var('top10_brokers_items', $items);
            load_template($template_path, false);
        } else {
            // Default template
            ?>
            <div class="top10-brokers-container" role="region" aria-label="<?php esc_attr_e('Top 10 Brokers List', 'top10-brokers'); ?>">
                <table class="top10-brokers-table" role="table" aria-label="<?php esc_attr_e('Broker Rankings', 'top10-brokers'); ?>">
                    <caption class="screen-reader-text"><?php esc_html_e('List of top-rated brokers with their rankings and ratings', 'top10-brokers'); ?></caption>
                    <thead>
                        <tr>
                            <th class="top10-brokers-rank-header" scope="col" role="columnheader"><?php esc_html_e('Rank', 'top10-brokers'); ?></th>
                            <th class="top10-brokers-broker-header" scope="col" role="columnheader"><?php esc_html_e('Broker', 'top10-brokers'); ?></th>
                            <th class="top10-brokers-rating-header" scope="col" role="columnheader"><?php esc_html_e('Rating', 'top10-brokers'); ?></th>
                            <th class="top10-brokers-review-header" scope="col" role="columnheader"><?php esc_html_e('Review', 'top10-brokers'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($items)): ?>
                            <?php foreach ($items as $item): ?>
                                <tr>
                                    <td class="top10-brokers-rank" role="cell"><?php echo esc_html($item['rank']); ?></td>
                                    <td class="top10-brokers-broker" role="cell">
                                        <?php if (!empty($item['image'])): ?>
                                            <a href="<?php echo esc_url($item['link']); ?>" aria-label="<?php echo esc_attr(sprintf(__('View details for %s', 'top10-brokers'), $item['name'])); ?>">
                                                <img src="<?php echo esc_url($item['image']); ?>" 
                                                     alt="<?php echo esc_attr($item['name']); ?>" 
                                                     class="top10-brokers-image"
                                                     width="150"
                                                     height="50"
                                                     loading="lazy" />
                                            </a>
                                        <?php else: ?>
                                            <a href="<?php echo esc_url($item['link']); ?>"><?php echo esc_html($item['name']); ?></a>
                                        <?php endif; ?>
                                    </td>
                                    <td class="top10-brokers-rating" role="cell">
                                        <?php
                                        $rating = floatval($item['rating']);
                                        $rating_text = sprintf(__('Rated %s out of 5 stars', 'top10-brokers'), number_format($rating, 1));
                                        ?>
                                        <div class="top10-brokers-star-rating"
                                             role="img" 
                                             aria-label="<?php echo esc_attr($rating_text); ?>"
                                             data-rating="<?php echo esc_attr(number_format($rating, 1)); ?>">
                                        </div>
                                    </td>
                                    <td class="top10-brokers-review" role="cell">
                                        <a href="<?php echo esc_url($item['link']); ?>" 
                                           class="learn-more-button"
                                           aria-label="<?php echo esc_attr(sprintf(__('Learn more about %s', 'top10-brokers'), $item['name'])); ?>">
                                            <?php echo esc_html($item['button_text']); ?>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" role="cell" class="no-results">
                                    <p><?php esc_html_e('No items found in this category.', 'top10-brokers'); ?></p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <?php
        }
        
        $output = ob_get_clean();
        
        // Cache the output for 1 hour if caching is enabled
        if ($use_cache) {
            set_transient($cache_key, $output, HOUR_IN_SECONDS);
        }
    }
    
    return $output;
}

function top10_brokers_get_items($post_type, $taxonomy, $term, $limit = 10, $rating_field = 'rating_meta_key', $use_random_rating = 'no') {
    $priority_brokers = array('iforex', 'capex', 'trade.com', 'infinox');
    $priority_items = array();
    $priority_post_ids = array(); // Keep track of priority broker post IDs

    // Helper function to get rating
    $get_rating = function($post_id, $rating_field) use ($use_random_rating) {
        $rating = get_post_meta($post_id, $rating_field, true);
        if (empty($rating) && $use_random_rating === 'yes') {
            // Generate random rating between 4.3 and 4.9
            $rating = number_format(mt_rand(43, 49) / 10, 1);
        }
        return $rating ? floatval($rating) : 0;
    };

    // First query: Get priority brokers
    foreach ($priority_brokers as $broker) {
        $args = array(
            'post_type' => $post_type,
            'posts_per_page' => -1,
            's' => $broker, // Search in title
            'orderby' => 'title',
            'order' => 'ASC',
        );

        // Only add taxonomy query if term is provided
        if (!empty($term) && !empty($taxonomy)) {
            $args['tax_query'] = array(
                array(
                    'taxonomy' => $taxonomy,
                    'field'    => 'slug',
                    'terms'    => $term,
                ),
            );
        }

        $query = new WP_Query($args);
        
        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $post_id = get_the_ID();
                $title = get_the_title();
                
                // Only add if the broker name is found in the title (case insensitive)
                if (stripos($title, $broker) !== false) {
                    $rating = $get_rating($post_id, $rating_field);
                    $options = get_option('top10_brokers_options');
                    $button_text = isset($options['top10_brokers_button_text']) ? $options['top10_brokers_button_text'] : __('Learn More', 'top10-brokers');
                    
                    $image = '';
                    if (has_post_thumbnail($post_id)) {
                        $image = get_the_post_thumbnail_url($post_id, 'full');
                    }
                    
                    $priority_items[$broker] = array(
                        'name' => $title,
                        'image' => $image,
                        'rating' => $rating,
                        'link' => get_permalink(),
                        'button_text' => $button_text
                    );
                    
                    $priority_post_ids[] = $post_id;
                    break; // Only take the first matching post for each broker
                }
            }
            wp_reset_postdata();
        }
    }

    // Sort priority items in the specified order
    $sorted_priority_items = array();
    foreach ($priority_brokers as $broker) {
        if (isset($priority_items[$broker])) {
            $sorted_priority_items[] = $priority_items[$broker];
        }
    }

    // Second query: Get remaining brokers sorted by post date
    $remaining_slots = $limit - count($sorted_priority_items);
    
    if ($remaining_slots > 0) {
        $args = array(
            'post_type' => $post_type,
            'posts_per_page' => $remaining_slots,
            'orderby' => 'date',
            'order' => 'DESC',
            'post__not_in' => $priority_post_ids, // Exclude priority brokers
        );

        // Only add meta query for rating if random rating is not enabled
        if ($use_random_rating !== 'yes') {
            $args['meta_query'] = array(
                array(
                    'key' => $rating_field,
                    'value' => '3.0',
                    'compare' => '>',
                    'type' => 'NUMERIC'
                )
            );
        }

        // Only add taxonomy query if term is provided
        if (!empty($term) && !empty($taxonomy)) {
            $args['tax_query'] = array(
                array(
                    'taxonomy' => $taxonomy,
                    'field'    => 'slug',
                    'terms'    => $term,
                ),
            );
        }

        $query = new WP_Query($args);
        $remaining_items = array();

        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $post_id = get_the_ID();
                
                $rating = $get_rating($post_id, $rating_field);
                $options = get_option('top10_brokers_options');
                $button_text = isset($options['top10_brokers_button_text']) ? $options['top10_brokers_button_text'] : __('Learn More', 'top10-brokers');
                
                $image = '';
                if (has_post_thumbnail($post_id)) {
                    $image = get_the_post_thumbnail_url($post_id, 'full');
                }
                
                $remaining_items[] = array(
                    'name' => get_the_title(),
                    'image' => $image,
                    'rating' => $rating,
                    'link' => get_permalink(),
                    'button_text' => $button_text
                );
            }
            wp_reset_postdata();
        }

        // Combine priority and remaining items
        $all_items = array_merge($sorted_priority_items, $remaining_items);
    } else {
        $all_items = $sorted_priority_items;
    }

    // Add ranks
    foreach ($all_items as $index => &$item) {
        $item['rank'] = $index + 1;
    }

    return $all_items;
}

// For backward compatibility
function top10_brokers_get_brokers($category) {
    $options = get_option('top10_brokers_options');
    $post_type = isset($options['top10_brokers_post_type']) ? $options['top10_brokers_post_type'] : 'broker';
    $taxonomy = isset($options['top10_brokers_taxonomy']) ? $options['top10_brokers_taxonomy'] : 'broker-category';
    $limit = isset($options['top10_brokers_limit']) ? intval($options['top10_brokers_limit']) : 10;
    $rating_field = isset($options['top10_brokers_rating_field']) ? $options['top10_brokers_rating_field'] : 'rating_meta_key';
    
    return top10_brokers_get_items($post_type, $taxonomy, $category, $limit, $rating_field);
} 