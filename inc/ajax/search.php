<?php

add_action('wp_ajax_waf_search', 'waf_ajax_search');
add_action('wp_ajax_nopriv_waf_search', 'waf_ajax_search');
function waf_ajax_search()
{
    check_ajax_referer('waf-searcher', 'nonce');

    $post_type = $_GET['post_type'];
    $pattern = $_GET['pattern'];
    $per_page = isset($_GET['per_page']) ? (int) $_GET['per_page'] : -1;
    $offset = isset($_GET['offset']) ? (int) $_GET['offset'] : 0;

    add_filter('posts_join', 'waf_posts_join_search', 10, 2);
    add_filter('posts_where', 'waf_posts_like_search', 10, 2);
    $query = new WP_Query([
        'cc_search_term' => $pattern,
        'post_type' => $post_type,
        'post_status' => 'publish',
        'posts_per_page' => $per_page,
        'offset' => $offset,
    ]);
    remove_filter('posts_join', 'waf_posts_join_search', 10, 2);
    remove_filter('posts_where', 'waf_posts_like_search', 10, 2);

    if ($per_page !== -1) {
        header('WAF-Pages: ' . ceil($query->found_posts / $per_page));
    }

    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $post_id = get_the_ID();
            $html = apply_filters('waf_template', apply_filters("waf_template_{$post_type}", '', $post_id), $post_id);
            if ($html) {
                echo $html;
            }
        }
    } else {
        echo '<p>' . __('No matches founds', 'waf') . '</p>';
    }

    wp_die();
}

function waf_posts_join_search($join, $query)
{
    global $wpdb;
    $pattern = $query->get('cc_search_term');
    $fields = (array) apply_filters('waf_search_meta_fields', [], $pattern, $query->get('post_type'));
    if (!(empty($pattern) || empty($fields))) {
        $join .= " LEFT JOIN {$wpdb->postmeta} ON ({$wpdb->posts}.ID = {$wpdb->postmeta}.post_id)";
    }

    return $join;
}

function waf_posts_like_search($where, $query)
{
    global $wpdb;

    $post_type = $query->get('post_type');
    $pattern = $query->get('cc_search_term');
    $fields = (array) apply_filters('waf_search_meta_fields', [], $pattern, $post_type);

    if (!empty($pattern)) {
        $where .= ' AND (';
        $where .= " LOWER({$wpdb->posts}.post_title) LIKE LOWER('%{$wpdb->esc_like($pattern)}%')";
        $where .= " OR LOWER({$wpdb->posts}.post_content) LIKE LOWER('%{$wpdb->esc_like($pattern)}%')";
        $where .= " OR LOWER({$wpdb->posts}.post_excerpt) LIKE LOWER('%{$wpdb->esc_like($pattern)}%')";
        foreach ($fields as $field) {

            $where .= " OR ({$wpdb->postmeta}.meta_key = '{$field}' AND LOWER({$wpdb->postmeta}.meta_value) LIKE LOWER('%{$wpdb->esc_like($pattern)}%'))";
        }

        $where .= ' )';
    }

    return $where;
}
