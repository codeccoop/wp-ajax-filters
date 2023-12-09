<?php

add_action('wp_ajax_waf_tax_filter', 'waf_ajax_waf_tax_filter');
add_action('wp_ajax_nopriv_waf_tax_filter', 'waf_ajax_waf_tax_filter');

function waf_ajax_waf_tax_filter()
{
    check_ajax_referer('waf-tax-filter', 'nonce');

    $post_type = isset($_GET['post_type']) ? $_GET['post_type'] : 'post';
    $per_page = isset($_GET['per_page']) ? (int) $_GET['per_page'] : -1;
    $offset = isset($_GET['offset']) ? (int) $_GET['offset'] : 0;
    $tax_query = [];
    foreach (array_keys($_GET) as $taxonomy) {
        if (in_array($taxonomy, ['action', 'nonce', 'template', 'post_type', 'per_page', 'offset', ''])) continue;

        $values = array_values(array_filter(explode(',', $_GET[$taxonomy]), function ($val) {
            return $val !== '';
        }));

        if (sizeof($values)) {
            $terms = $values;
        } else {
            $terms = array_map(function ($term) {
                return $term->slug;
            }, get_terms($taxonomy));
        }

        $tax_query[] = [
            'taxonomy' => $taxonomy,
            'field' => 'slug',
            'operator' => 'IN',
            'terms' => $terms,
            'include_children' => false,
        ];
    }

    $args = [
        'post_type' => $post_type,
        'post_status' => 'publish',
        'posts_per_page' => $per_page,
        'offset' => $offset,
        'tax_query' => [
            'relation' => $relation,
            $tax_query
        ]
    ];

    $query = new WP_Query($args);

    if ($per_page !== -1) {
        header('WAF-PAGES: ' . ceil($query->found_posts / $per_page));
    }

    while ($query->have_posts()) {
        $query->the_post();
        $post_id = get_the_ID();
        $html = apply_filters('waf_template', apply_filters("waf_template_{$post_type}", '', $post_id), $post_id);
        if ($html) echo $html;
    }

    wp_die();
}

add_filter('waf_template', function ($html, $post_id) {
    if ($html) return $html;
    return '<div class="waf-single">
        <a href="' . get_post_permalink($post_id) . '"><h3>' . get_the_title($post_id) . '</h3></a>
        <p>' . get_the_excerpt($post_id) . '</p>
    </div>';
}, 10, 2);
