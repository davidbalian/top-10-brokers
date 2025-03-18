<?php

function top10_brokers_add_admin_menu() {
    add_menu_page(
        'Top 10 Brokers Settings',
        'Top 10 Brokers',
        'manage_options',
        'top10-brokers',
        'top10_brokers_settings_page',
        'dashicons-list-view'
    );
}
add_action('admin_menu', 'top10_brokers_add_admin_menu');

function top10_brokers_settings_page() {
    // Check if reset was requested
    if (isset($_POST['reset_settings']) && check_admin_referer('top10_brokers_reset_settings')) {
        // Reset to default options
        $default_options = array(
            'top10_brokers_post_type' => 'broker',
            'top10_brokers_taxonomy' => 'broker-category',
            'top10_brokers_limit' => 10,
            'top10_brokers_rating_field' => 'rating',
            'top10_brokers_button_text' => __('Learn More', 'top10-brokers'),
            'top10_brokers_button_bg_color' => '#4CAF50',
            'top10_brokers_button_text_color' => '#ffffff'
        );
        update_option('top10_brokers_options', $default_options);
        add_settings_error('top10_brokers_messages', 'settings_reset', __('Settings have been reset to defaults.', 'top10-brokers'), 'updated');
    }
    ?>
    <div class="wrap">
        <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
        <?php settings_errors('top10_brokers_messages'); ?>
        
        <form method="post" action="options.php">
            <?php
            settings_fields('top10_brokers_settings');
            do_settings_sections('top10_brokers');
            submit_button(__('Save Changes', 'top10-brokers'));
            ?>
        </form>
        
        <hr>
        
        <h2><?php _e('Reset Settings', 'top10-brokers'); ?></h2>
        <p><?php _e('Click the button below to reset all settings to their default values.', 'top10-brokers'); ?></p>
        <form method="post" action="">
            <?php wp_nonce_field('top10_brokers_reset_settings'); ?>
            <input type="submit" name="reset_settings" class="button button-secondary" value="<?php esc_attr_e('Reset All Settings', 'top10-brokers'); ?>" onclick="return confirm('<?php esc_attr_e('Are you sure you want to reset all settings to their default values?', 'top10-brokers'); ?>');">
        </form>
        
        <?php if (get_option('top10_brokers_options')): ?>
        <hr>
        <div class="top10-brokers-shortcode-info">
            <h2><?php _e('Shortcode', 'top10-brokers'); ?></h2>
            <p><?php _e('Use this shortcode to display your broker table:', 'top10-brokers'); ?></p>
            <code>[top10_brokers_table cache="no"]</code>
            
            <h3><?php _e('Preview', 'top10-brokers'); ?></h3>
            <div class="top10-brokers-preview">
                <?php echo do_shortcode('[top10_brokers_table cache="no"]'); ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
    <?php
}

function top10_brokers_settings_init() {
    register_setting('top10_brokers_settings', 'top10_brokers_options', 'top10_brokers_validate_options');

    add_settings_section(
        'top10_brokers_section',
        __('Table Settings', 'top10-brokers'),
        'top10_brokers_section_callback',
        'top10_brokers'
    );

    add_settings_field(
        'top10_brokers_post_type',
        __('Select Post Type', 'top10-brokers'),
        'top10_brokers_post_type_render',
        'top10_brokers',
        'top10_brokers_section'
    );

    add_settings_field(
        'top10_brokers_taxonomy',
        __('Select Category Type', 'top10-brokers'),
        'top10_brokers_taxonomy_render',
        'top10_brokers',
        'top10_brokers_section'
    );

    add_settings_field(
        'top10_brokers_term',
        __('Select Category', 'top10-brokers'),
        'top10_brokers_term_render',
        'top10_brokers',
        'top10_brokers_section'
    );
    
    add_settings_field(
        'top10_brokers_sample_post',
        __('Select Sample Post', 'top10-brokers'),
        'top10_brokers_sample_post_render',
        'top10_brokers',
        'top10_brokers_section'
    );

    add_settings_field(
        'top10_brokers_rating_field',
        __('Rating Meta Field', 'top10-brokers'),
        'top10_brokers_rating_field_render',
        'top10_brokers',
        'top10_brokers_section'
    );

    add_settings_field(
        'top10_brokers_limit',
        __('Number of Items to Display', 'top10-brokers'),
        'top10_brokers_limit_render',
        'top10_brokers',
        'top10_brokers_section'
    );

    add_settings_field(
        'top10_brokers_button_text',
        __('Button Text', 'top10-brokers'),
        'top10_brokers_button_text_render',
        'top10_brokers',
        'top10_brokers_section'
    );

    // Add new color picker fields
    add_settings_field(
        'top10_brokers_button_bg_color',
        __('Button Background Color', 'top10-brokers'),
        'top10_brokers_button_bg_color_render',
        'top10_brokers',
        'top10_brokers_section'
    );

    add_settings_field(
        'top10_brokers_button_text_color',
        __('Button Text Color', 'top10-brokers'),
        'top10_brokers_button_text_color_render',
        'top10_brokers',
        'top10_brokers_section'
    );

    add_settings_field(
        'top10_brokers_use_random_rating',
        __('Use Random Rating', 'top10-brokers'),
        'top10_brokers_use_random_rating_render',
        'top10_brokers',
        'top10_brokers_section'
    );
}
add_action('admin_init', 'top10_brokers_settings_init');

function top10_brokers_validate_options($input) {
    // Sanitize inputs
    $input['top10_brokers_post_type'] = sanitize_text_field($input['top10_brokers_post_type']);
    $input['top10_brokers_taxonomy'] = sanitize_text_field($input['top10_brokers_taxonomy']);
    $input['top10_brokers_term'] = sanitize_text_field($input['top10_brokers_term']);
    $input['top10_brokers_sample_post'] = sanitize_text_field($input['top10_brokers_sample_post']);
    $input['top10_brokers_rating_field'] = sanitize_text_field($input['top10_brokers_rating_field']);
    $input['top10_brokers_limit'] = absint($input['top10_brokers_limit']);
    $input['top10_brokers_button_text'] = sanitize_text_field($input['top10_brokers_button_text']);
    
    // Validate color values
    $input['top10_brokers_button_bg_color'] = sanitize_hex_color($input['top10_brokers_button_bg_color']);
    $input['top10_brokers_button_text_color'] = sanitize_hex_color($input['top10_brokers_button_text_color']);
    
    return $input;
}

function top10_brokers_section_callback() {
    echo __('Customize the table settings below. Select the post type, category, and rating field to display in your table.', 'top10-brokers');
}

function top10_brokers_post_type_render() {
    $options = get_option('top10_brokers_options');
    $post_type = isset($options['top10_brokers_post_type']) ? $options['top10_brokers_post_type'] : 'broker';
    
    $post_types = get_post_types(array('public' => true), 'objects');
    ?>
    <select id="top10_brokers_post_type" name="top10_brokers_options[top10_brokers_post_type]" class="top10-brokers-select">
        <?php foreach ($post_types as $type): ?>
            <option value="<?php echo esc_attr($type->name); ?>" <?php selected($post_type, $type->name); ?>>
                <?php echo esc_html($type->labels->singular_name); ?>
            </option>
        <?php endforeach; ?>
    </select>
    <p class="description"><?php _e('Select the post type to display in the table.', 'top10-brokers'); ?></p>
    <?php
}

function top10_brokers_taxonomy_render() {
    $options = get_option('top10_brokers_options');
    $post_type = isset($options['top10_brokers_post_type']) ? $options['top10_brokers_post_type'] : 'broker';
    $taxonomy = isset($options['top10_brokers_taxonomy']) ? $options['top10_brokers_taxonomy'] : 'broker-category';
    
    $taxonomies = get_object_taxonomies($post_type, 'objects');
    ?>
    <select id="top10_brokers_taxonomy" name="top10_brokers_options[top10_brokers_taxonomy]" class="top10-brokers-select">
        <?php if (empty($taxonomies)): ?>
            <option value=""><?php _e('No taxonomies available', 'top10-brokers'); ?></option>
        <?php else: ?>
            <?php foreach ($taxonomies as $tax): ?>
                <option value="<?php echo esc_attr($tax->name); ?>" <?php selected($taxonomy, $tax->name); ?>>
                    <?php echo esc_html($tax->labels->singular_name); ?>
                </option>
            <?php endforeach; ?>
        <?php endif; ?>
    </select>
    <p class="description"><?php _e('Select the category type to filter posts by.', 'top10-brokers'); ?></p>
    <?php
}

function top10_brokers_term_render() {
    $options = get_option('top10_brokers_options');
    $taxonomy = isset($options['top10_brokers_taxonomy']) ? $options['top10_brokers_taxonomy'] : 'broker-category';
    $term = isset($options['top10_brokers_term']) ? $options['top10_brokers_term'] : '';
    
    $terms = get_terms(array('taxonomy' => $taxonomy, 'hide_empty' => false));
    ?>
    <select id="top10_brokers_term" name="top10_brokers_options[top10_brokers_term]" class="top10-brokers-select">
        <option value=""><?php _e('-- Select Category --', 'top10-brokers'); ?></option>
        <?php if (!is_wp_error($terms) && !empty($terms)): ?>
            <?php foreach ($terms as $t): ?>
                <option value="<?php echo esc_attr($t->slug); ?>" <?php selected($term, $t->slug); ?>>
                    <?php echo esc_html($t->name); ?>
                </option>
            <?php endforeach; ?>
        <?php endif; ?>
    </select>
    <p class="description"><?php _e('Select the specific category to display posts from.', 'top10-brokers'); ?></p>
    <?php
}

function top10_brokers_sample_post_render() {
    $options = get_option('top10_brokers_options');
    $post_type = isset($options['top10_brokers_post_type']) ? $options['top10_brokers_post_type'] : 'broker';
    $taxonomy = isset($options['top10_brokers_taxonomy']) ? $options['top10_brokers_taxonomy'] : 'broker-category';
    $term = isset($options['top10_brokers_term']) ? $options['top10_brokers_term'] : '';
    $sample_post = isset($options['top10_brokers_sample_post']) ? $options['top10_brokers_sample_post'] : '';
    
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
    ?>
    <select id="top10_brokers_sample_post" name="top10_brokers_options[top10_brokers_sample_post]" class="top10-brokers-select">
        <option value=""><?php _e('-- Select Sample Post --', 'top10-brokers'); ?></option>
        <?php if (!empty($recent_posts)): ?>
            <?php foreach ($recent_posts as $post): ?>
                <option value="<?php echo esc_attr($post->ID); ?>" <?php selected($sample_post, $post->ID); ?>>
                    <?php echo esc_html($post->post_title); ?>
                </option>
            <?php endforeach; ?>
        <?php else: ?>
            <option value="" disabled><?php _e('No posts available in this category', 'top10-brokers'); ?></option>
        <?php endif; ?>
    </select>
    <p class="description"><?php _e('Select a sample post to view available meta fields.', 'top10-brokers'); ?></p>
    <?php
}

function top10_brokers_rating_field_render() {
    $options = get_option('top10_brokers_options');
    $rating_field = isset($options['top10_brokers_rating_field']) ? $options['top10_brokers_rating_field'] : 'rating_meta_key';
    $sample_post_id = isset($options['top10_brokers_sample_post']) ? $options['top10_brokers_sample_post'] : '';
    
    ?>
    <div id="rating-field-container">
        <select id="top10_brokers_rating_field" name="top10_brokers_options[top10_brokers_rating_field]" class="top10-brokers-select" <?php echo empty($sample_post_id) ? 'disabled' : ''; ?>>
            <?php if (empty($sample_post_id)): ?>
                <option value=""><?php _e('Select a post first', 'top10-brokers'); ?></option>
            <?php else: ?>
                <?php
                $meta_fields = array();
                $post_meta = get_post_meta($sample_post_id);
                
                if (!empty($post_meta)) {
                    foreach ($post_meta as $key => $values) {
                        if (is_protected_meta($key, 'post')) {
                            continue;
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
                
                foreach ($meta_fields as $key => $label): ?>
                    <option value="<?php echo esc_attr($key); ?>" <?php selected($rating_field, $key); ?>>
                        <?php echo esc_html($label); ?>
                    </option>
                <?php endforeach; ?>
            <?php endif; ?>
        </select>
        <?php if (empty($sample_post_id)): ?>
            <p class="description" style="color: #d63638;">
                <?php _e('First select a sample post above to see available rating fields.', 'top10-brokers'); ?>
            </p>
        <?php else: ?>
            <p class="description"><?php _e('Select the meta field that contains the broker rating value.', 'top10-brokers'); ?></p>
        <?php endif; ?>
    </div>

    <script>
    jQuery(document).ready(function($) {
        var samplePostSelect = $('#top10_brokers_sample_post');
        var ratingFieldContainer = $('#rating-field-container');

        samplePostSelect.on('change', function() {
            var postId = $(this).val();
            
            if (!postId) {
                ratingFieldContainer.html(`
                    <select id="top10_brokers_rating_field" name="top10_brokers_options[top10_brokers_rating_field]" class="top10-brokers-select" disabled>
                        <option value=""><?php echo esc_js(__('Select a post first', 'top10-brokers')); ?></option>
                    </select>
                    <p class="description" style="color: #d63638;">
                        <?php echo esc_js(__('First select a sample post above to see available rating fields.', 'top10-brokers')); ?>
                    </p>
                `);
                return;
            }

            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'top10_brokers_get_post_meta_fields',
                    post_id: postId,
                    nonce: '<?php echo wp_create_nonce('top10_brokers_nonce'); ?>'
                },
                success: function(response) {
                    if (response.success && Object.keys(response.data).length > 0) {
                        var select = $('<select>', {
                            id: 'top10_brokers_rating_field',
                            name: 'top10_brokers_options[top10_brokers_rating_field]',
                            class: 'top10-brokers-select'
                        });

                        $.each(response.data, function(key, label) {
                            select.append($('<option>', {
                                value: key,
                                text: label
                            }));
                        });

                        ratingFieldContainer.html('').append(select).append(
                            '<p class="description"><?php echo esc_js(__('Select the meta field that contains the broker rating value.', 'top10-brokers')); ?></p>'
                        );
                    } else {
                        ratingFieldContainer.html(`
                            <select id="top10_brokers_rating_field" name="top10_brokers_options[top10_brokers_rating_field]" class="top10-brokers-select" disabled>
                                <option value=""><?php echo esc_js(__('No fields available', 'top10-brokers')); ?></option>
                            </select>
                            <p class="description" style="color: #d63638;">
                                <?php echo esc_js(__('No meta fields found for the selected post.', 'top10-brokers')); ?>
                            </p>
                        `);
                    }
                },
                error: function() {
                    ratingFieldContainer.html(`
                        <select id="top10_brokers_rating_field" name="top10_brokers_options[top10_brokers_rating_field]" class="top10-brokers-select" disabled>
                            <option value=""><?php echo esc_js(__('Error loading fields', 'top10-brokers')); ?></option>
                        </select>
                        <p class="description" style="color: #d63638;">
                            <?php echo esc_js(__('Error loading meta fields. Please try again.', 'top10-brokers')); ?>
                        </p>
                    `);
                }
            });
        });
    });
    </script>
    <?php
}

function top10_brokers_limit_render() {
    $options = get_option('top10_brokers_options');
    $limit = isset($options['top10_brokers_limit']) ? $options['top10_brokers_limit'] : 10;
    ?>
    <input type="number" id="top10_brokers_limit" name="top10_brokers_options[top10_brokers_limit]" value="<?php echo esc_attr($limit); ?>" min="1" max="50" class="small-text">
    <p class="description"><?php _e('Number of items to display in the table (max 50).', 'top10-brokers'); ?></p>
    <?php
}

function top10_brokers_button_text_render() {
    $options = get_option('top10_brokers_options');
    $button_text = isset($options['top10_brokers_button_text']) ? $options['top10_brokers_button_text'] : __('Learn More', 'top10-brokers');
    ?>
    <input type="text" id="top10_brokers_button_text" name="top10_brokers_options[top10_brokers_button_text]" value="<?php echo esc_attr($button_text); ?>" class="regular-text">
    <p class="description"><?php _e('Text to display on the action button.', 'top10-brokers'); ?></p>
    <?php
}

// Add color picker render functions
function top10_brokers_button_bg_color_render() {
    $options = get_option('top10_brokers_options');
    $bg_color = isset($options['top10_brokers_button_bg_color']) ? $options['top10_brokers_button_bg_color'] : '#4CAF50';
    ?>
    <input type="text" 
           class="top10-brokers-color-picker" 
           name="top10_brokers_options[top10_brokers_button_bg_color]" 
           value="<?php echo esc_attr($bg_color); ?>" />
    <p class="description"><?php _e('Choose the background color for the Learn More button.', 'top10-brokers'); ?></p>
    <?php
}

function top10_brokers_button_text_color_render() {
    $options = get_option('top10_brokers_options');
    $text_color = isset($options['top10_brokers_button_text_color']) ? $options['top10_brokers_button_text_color'] : '#ffffff';
    ?>
    <input type="text" 
           class="top10-brokers-color-picker" 
           name="top10_brokers_options[top10_brokers_button_text_color]" 
           value="<?php echo esc_attr($text_color); ?>" />
    <p class="description"><?php _e('Choose the text color for the Learn More button.', 'top10-brokers'); ?></p>
    <?php
}

function top10_brokers_use_random_rating_render() {
    $options = get_option('top10_brokers_options');
    $use_random_rating = isset($options['top10_brokers_use_random_rating']) ? $options['top10_brokers_use_random_rating'] : 'no';
    ?>
    <label>
        <input type="checkbox" name="top10_brokers_options[top10_brokers_use_random_rating]" value="yes" <?php checked($use_random_rating, 'yes'); ?>>
        <?php _e('Can\'t find rating meta - Use random rating between 4.3 and 4.9', 'top10-brokers'); ?>
    </label>
    <p class="description"><?php _e('If checked, posts without a rating will be assigned a random rating between 4.3 and 4.9.', 'top10-brokers'); ?></p>
    <?php
}

// Add JavaScript to handle dynamic field updates
function top10_brokers_admin_footer_scripts() {
    $screen = get_current_screen();
    if ($screen->id !== 'toplevel_page_top10-brokers') {
        return;
    }
    ?>
    <script>
    jQuery(document).ready(function($) {
        // When post type changes, update taxonomies
        $('#top10_brokers_post_type').on('change', function() {
            var postType = $(this).val();
            
            $.ajax({
                url: top10BrokersAjax.ajax_url,
                type: 'POST',
                data: {
                    action: 'top10_brokers_get_taxonomies_for_post_type',
                    nonce: top10BrokersAjax.nonce,
                    post_type: postType
                },
                success: function(response) {
                    if (response.success) {
                        var $taxonomySelect = $('#top10_brokers_taxonomy');
                        $taxonomySelect.empty();
                        
                        $.each(response.data, function(key, value) {
                            $taxonomySelect.append($('<option></option>').attr('value', key).text(value));
                        });
                        
                        // Trigger change to update terms
                        $taxonomySelect.trigger('change');
                    }
                }
            });
        });
        
        // When taxonomy changes, update terms
        $('#top10_brokers_taxonomy').on('change', function() {
            var taxonomy = $(this).val();
            
            $.ajax({
                url: top10BrokersAjax.ajax_url,
                type: 'POST',
                data: {
                    action: 'top10_brokers_get_terms_for_taxonomy',
                    nonce: top10BrokersAjax.nonce,
                    taxonomy: taxonomy
                },
                success: function(response) {
                    if (response.success) {
                        var $termSelect = $('#top10_brokers_term');
                        $termSelect.empty();
                        $termSelect.append($('<option></option>').attr('value', '').text('-- Select Category --'));
                        
                        $.each(response.data, function(index, term) {
                            $termSelect.append($('<option></option>').attr('value', term.slug).text(term.name));
                        });
                        
                        // Trigger change to update sample posts
                        $termSelect.trigger('change');
                    }
                }
            });
        });
        
        // When term changes, update sample posts
        $('#top10_brokers_term').on('change', function() {
            var postType = $('#top10_brokers_post_type').val();
            var taxonomy = $('#top10_brokers_taxonomy').val();
            var term = $(this).val();
            
            $.ajax({
                url: top10BrokersAjax.ajax_url,
                type: 'POST',
                data: {
                    action: 'top10_brokers_get_sample_posts',
                    nonce: top10BrokersAjax.nonce,
                    post_type: postType,
                    taxonomy: taxonomy,
                    term: term
                },
                success: function(response) {
                    if (response.success) {
                        var $postSelect = $('#top10_brokers_sample_post');
                        $postSelect.empty();
                        $postSelect.append($('<option></option>').attr('value', '').text('-- Select Sample Post --'));
                        
                        if (response.data.length > 0) {
                            $.each(response.data, function(index, post) {
                                $postSelect.append($('<option></option>').attr('value', post.id).text(post.title));
                            });
                        } else {
                            $postSelect.append($('<option></option>').attr('value', '').attr('disabled', 'disabled').text('No posts available in this category'));
                        }
                        
                        // Trigger change to update meta fields
                        $postSelect.trigger('change');
                    }
                }
            });
        });
        
        // When sample post changes, update meta fields
        $('#top10_brokers_sample_post').on('change', function() {
            var postId = $(this).val();
            
            // If no post is selected, disable the rating field
            if (!postId) {
                var $metaFieldSelect = $('#top10_brokers_rating_field');
                var currentValue = $metaFieldSelect.val();
                var isSelect = $metaFieldSelect.is('select');
                
                // Replace with disabled text input
                var inputHtml = '<input type="text" id="top10_brokers_rating_field" name="top10_brokers_options[top10_brokers_rating_field]" value="' + currentValue + '" class="regular-text" disabled>';
                inputHtml += '<p class="description">Please select a sample post above to see available meta fields.</p>';
                
                $metaFieldSelect.replaceWith(inputHtml);
                return;
            }
            
            $.ajax({
                url: top10BrokersAjax.ajax_url,
                type: 'POST',
                data: {
                    action: 'top10_brokers_get_post_meta_fields',
                    nonce: top10BrokersAjax.nonce,
                    post_id: postId
                },
                success: function(response) {
                    if (response.success) {
                        var $metaFieldSelect = $('#top10_brokers_rating_field');
                        var currentValue = $metaFieldSelect.val();
                        var isSelect = $metaFieldSelect.is('select');
                        
                        // If we have meta fields, create or update the dropdown
                        if (Object.keys(response.data).length > 0) {
                            if (!isSelect) {
                                var selectHtml = '<select id="top10_brokers_rating_field" name="top10_brokers_options[top10_brokers_rating_field]" class="top10-brokers-select">';
                                $.each(response.data, function(key, label) {
                                    var selected = (key === currentValue) ? 'selected="selected"' : '';
                                    selectHtml += '<option value="' + key + '" ' + selected + '>' + label + '</option>';
                                });
                                selectHtml += '</select>';
                                selectHtml += '<p class="description">Select the meta field to use for ratings.</p>';
                                
                                $metaFieldSelect.replaceWith(selectHtml);
                            } else {
                                $metaFieldSelect.empty();
                                $.each(response.data, function(key, label) {
                                    var option = $('<option></option>').attr('value', key).text(label);
                                    if (key === currentValue) {
                                        option.attr('selected', 'selected');
                                    }
                                    $metaFieldSelect.append(option);
                                });
                            }
                        } else {
                            // No meta fields found, show enabled text input with message
                            var inputHtml = '<input type="text" id="top10_brokers_rating_field" name="top10_brokers_options[top10_brokers_rating_field]" value="' + currentValue + '" class="regular-text">';
                            inputHtml += '<p class="description">No meta fields found for this post. Enter the meta field key manually.</p>';
                            
                            $metaFieldSelect.replaceWith(inputHtml);
                        }
                    }
                }
            });
        });
    });
    </script>
    <?php
}
add_action('admin_footer', 'top10_brokers_admin_footer_scripts');

// Add color picker scripts and styles
function top10_brokers_admin_enqueue($hook) {
    if ($hook !== 'toplevel_page_top10-brokers') {
        return;
    }

    // Add WordPress color picker
    wp_enqueue_style('wp-color-picker');
    wp_enqueue_script('wp-color-picker');
    
    // Add our custom script to initialize color pickers
    wp_enqueue_script(
        'top10-brokers-admin',
        TOP10_BROKERS_PLUGIN_URL . 'assets/js/admin.js',
        array('wp-color-picker'),
        TOP10_BROKERS_VERSION,
        true
    );
} 