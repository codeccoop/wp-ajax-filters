<?php

add_action('wp_ajax_waf_search', 'waf_ajax_waf_search');
add_action('wp_ajax_nopriv_waf_search', 'waf_ajax_waf_search');
function waf_ajax_waf_search()
{
    check_ajax_referer('waf-searcher', 'nonce');

    $post_type = $_GET['post_type'];
    $search_pattern = $_GET['search_pattern'];
    $per_page = isset($_GET['per_page']) ? (int) $_GET['per_page'] : -1;
    $offset = isset($_GET['offset']) ? (int) $_GET['offset'] : 0;

    add_filter('posts_where', 'waf_posts_like_search', 10, 2);
    $query = new WP_Query([
        'cc_search_term' => $search_pattern,
        'post_type' => $post_type,
        'post_status' => 'publish',
        'posts_per_page' => $per_page,
        'offset' => $offset,
    ]);
    remove_filter('posts_where', 'waf_posts_like_search', 10, 2);

    if ($per_page !== -1) {
        header('WAF-Pages: ' . ceil($query->found_posts / $per_page));
    }

    $html = '';
    while ($query->have_posts()) {
        $query->the_post();
        $post_id = get_the_ID();
        $html = apply_filters('waf_template', apply_filters("waf_template_{$post_type}", '', $post_id), $post_id);
        if ($html) echo $html;
    }

    wp_die();
}

function waf_posts_like_search($where, $wp_query)
{
    global $wpdb;
    if ($search_pattern = $wp_query->get('cc_search_term')) {
        $where .= ' AND (LOWER(' . $wpdb->posts . '.post_title) LIKE LOWER(\'%' . $wpdb->esc_like($search_pattern) . '%\')';
        $where .= ' OR LOWER(' . $wpdb->posts . '.post_content) LIKE LOWER(\'%' . $wpdb->esc_like($search_pattern) . '%\')';
        $where .= ' OR LOWER(' . $wpdb->posts . '.post_excerpt) LIKE LOWER(\'%' . $wpdb->esc_like($search_pattern) . '%\'))';
    }

    return $where;
}
