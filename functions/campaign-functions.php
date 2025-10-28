<?php

/**
 * Register custom post types
 */
add_action('init', 'reg_custom_ptype');
// campaign
function reg_custom_ptype() {
  $labels = array(
    'name' => _x('Campaign', 'post type general name'),
    'singular_name' => _x('campaign', 'post type singular name'),
    'add_new' => _x('Add Campaign', 'testimonial'),
    'add_new_item' => __('Add New Campaign'),
    'edit_item' => __('Edit Campaign'),
    'new_item' => __('New Campaign'),
    'all_items' => __('All Campaign'),
    'view_item' => __('View Campaign'),
    'search_items' => __('Search Campaign'),
    'not_found' => __('No Campaign found'),
    'not_found_in_trash' => __('No Campaign found in Trash'),
    'parent_item_colon' => '',
    'menu_name' => 'Campaigns'
  );

  $args = array(
    'labels' => $labels,
    'show_ui' => true,
    'show_in_menu' => true,
    'menu_position' => 7,
    'query_var' => false,
    'rewrite' => false,
    'capability_type' => 'post',
    'hierarchical' => false,
    'menu_icon' => 'dashicons-admin-site',
    'supports' => array(
      'title',
      'revisions'
    ),
    'exclude_from_search' => true
  );
  register_post_type('campaign', $args);

  //lead
  $labels = array(
    'name' => _x('Leads', 'post type general name'),
    'singular_name' => _x('leads', 'post type singular name'),
    'add_new' => _x('Add Lead', 'testimonial'),
    'add_new_item' => __('Add New Lead'),
    'edit_item' => __('Edit Lead'),
    'new_item' => __('New Lead'),
    'all_items' => __('All Leads'),
    'view_item' => __('View Leads'),
    'search_items' => __('Search Leads'),
    'not_found' => __('No Leads found'),
    'not_found_in_trash' => __('No Leads found in Trash'),
    'parent_item_colon' => '',
    'menu_name' => 'Leads'
  );

  $args = array(
    'labels' => $labels,
    'menu_position' => 7,
    'show_ui' => true,
    'show_in_menu' => true,
    'query_var' => false,
    'rewrite' => false,
    'capability_type' => 'post',
    'hierarchical' => false,
    'menu_icon' => 'dashicons-id-alt',
    'supports' => array(
      'title',
    ),
    'exclude_from_search' => true
  );
  register_post_type('lead', $args);
}

/**
 * Generate a campaign URL with ACF fields as query parameters.
 */
add_action('acf/save_post', 'generate_campaign_share_url', 20);

function generate_campaign_share_url($post_id) {
    if (get_post_type($post_id) !== 'campaign') {
        return;
    }

    // Field names to include as query parameters
    $acf_fields = array(
        'utm_campaign',
        'utm_medium',
        'utm_source',
        'utm_term',
        'utm_content',
    );

    $params = array();

    foreach ($acf_fields as $field_key) {
        $value = get_field($field_key, $post_id);
        if (!empty($value)) {
            $params[$field_key] = $value;
        }
    }

    // Build the URL
    $base_url = get_site_url();
    $query_string = http_build_query($params);
    $share_url = $base_url;

    if (!empty($query_string)) {
        $share_url .= '?' . $query_string;
    }
    // Save the URL as a custom field
    update_post_meta($post_id, '_campaign_share_url', esc_url($share_url));
}


/**
 * Create custom meta box to showcase the share URL
 */
add_action('add_meta_boxes_campaign', function() {
    add_meta_box(
        'campaign_share_url_box',
        'Campaign Share URL',
        'render_campaign_share_url_box',
        'campaign',
    );
});

function render_campaign_share_url_box($post) {
    $url = get_post_meta($post->ID, '_campaign_share_url', true);
    if ($url) {
        echo '<p><input type="text" readonly value="' . esc_attr($url) . '" style="width:100%;" onclick="this.select();"></p>';
        echo '<p><em>Click to copy the URL.</em></p>';
    } else {
        echo '<p>No URL generated yet. Save the campaign to generate one.</p>';
    }
}

/**
 * Save GF form submissions for the Lead CPT
 */
add_action('gform_after_submission_1', 'gf_to_acf_lead_submission', 10, 2);
add_action('gform_after_submission_2', 'gf_to_acf_lead_submission', 10, 2);

function gf_to_acf_lead_submission($entry, $form) {
    // Map GF fields to ACF field keys/names
    $field_map = array(
        '2.3' => 'first_name',      
        '2.6' => 'last_name',
        '12' => 'campaign',      
        '4' => 'email',          
        '3' => 'phone', 
        '5.3' => 'city',
        '5.4' => 'state',
        '5.5' => 'country',
        '5.6' => 'zip_code',
    );

    // IP Address
    $ip_address = rgar($entry, 'ip');

    // Email
    $email_field_id = '4'; 
    $email_value = rgar($entry, $email_field_id);

    if (empty($email_value)) {
        error_log('GF to Lead: No email provided.');
        return;
    }

    // Query leads to see if any exist with the same email
    $existing_leads = get_posts(array(
        'post_type'      => 'lead',
        'posts_per_page' => 1,
        'meta_query'     => array(
            array(
                'key'     => 'email',
                'value'   => $email_value,
                'compare' => '='
            )
        )
    ));

    // Find lead. If none exist, create a new lead.
    if (!empty($existing_leads)) {
        $post_id = $existing_leads[0]->ID;
    } else {
        $post_data = array(
            'post_title'  => rgar($entry, '2.3') . ' ' . rgar($entry, '2.6'),
            'post_type'   => 'lead',
            'post_status' => 'publish',
        );

        $post_id = wp_insert_post($post_data);

        if (is_wp_error($post_id) || !$post_id) {
            error_log('GF to Lead: Failed to create lead.');
            return;
        }
    }

    // Loop through the field map and save each GF value into the corresponding ACF field
    foreach ($field_map as $gf_field_id => $acf_field_name) {
        $value = rgar($entry, $gf_field_id);
        if (!empty($value)) {
            update_field($acf_field_name, $value, $post_id);
        }
    }

    // Insert IP address
    if (!empty($ip_address)) {
        update_field('ip_address', $ip_address, $post_id);
    }
}

/**
 * Create/format timestamp merge tag
 */
add_filter('gform_replace_merge_tags', function($text, $form, $entry) {
    if (strpos($text, '{timestamp}') === false) {
        return $text;
    }

    // Generate current timestamp (WordPress timezone)
    $timestamp = date_i18n('Y-m-d H:i:s', current_time('timestamp'));

    // Replace {timestamp} with the formatted time
    $text = str_replace('{timestamp}', $timestamp, $text);

    return $text;
}, 10, 3);