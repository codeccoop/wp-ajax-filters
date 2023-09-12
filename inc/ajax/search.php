<?php

add_action('wp_ajax_waf_search', 'waf_ajax_waf_search');
add_action('wp_ajax_nopriv_waf_search', 'waf_ajax_waf_search');
function waf_ajax_waf_search()
{
    check_ajax_referer('waf-searcher', 'nonce');

    $post_type = $_GET['post_type'];
    $search_term = $_GET['search_term'];

    $title_like_query = array(
        'cc_search_term' => $search_term,
        'post_type' => $post_type,
        'post_status' => 'publish',
    );

    add_filter('posts_where', 'waf_posts_like_where', 10, 2);
    $query = new WP_Query($title_like_query);
    remove_filter('posts_where', 'waf_posts_like_where', 10);

    $html = '';
    while ($query->have_posts()) {
        $query->the_post();
        $post_id = get_the_ID();
        $html .= apply_filters('waf_template', apply_filters("waf_template_{$post_type}", '', $post_id), $post_id);
    }

    echo $html;
    wp_die();
}

function waf_posts_like_where($where, $wp_query)
{
    global $wpdb;
    if ($search_term = $wp_query->get('cc_search_term')) {
        $where .= ' AND (LOWER(' . $wpdb->posts . '.post_title) LIKE LOWER(\'%' . $wpdb->esc_like($search_term) . '%\')';
        $where .= ' OR LOWER(' . $wpdb->posts . '.post_content) LIKE LOWER(\'%' . $wpdb->esc_like($search_term) . '%\')';
        $where .= ' OR LOWER(' . $wpdb->posts . '.post_excerpt) LIKE LOWER(\'%' . $wpdb->esc_like($search_term) . '%\'))';
    }
    echo substr($where, 57);
    return substr($where, 57);
}
