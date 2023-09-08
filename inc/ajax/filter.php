<?php

add_action('wp_ajax_waf_tax_filter', 'waf_ajax_waf_tax_filter');
add_action('wp_ajax_nopriv_waf_tax_filter', 'waf_ajax_waf_tax_filter');

function waf_ajax_waf_tax_filter()
{
    check_ajax_referer('waf-tax-filter', 'nonce');

    $post_type = $_GET['post_type'];
    $tax_query = [];
    foreach (array_keys($_GET) as $taxonomy) {
        if (in_array($taxonomy, ['action', 'nonce', 'template', 'post_type', ''])) continue;

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
        ];
    }

    $args = [
        'post_type' => $post_type,
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'tax_query' => $tax_query
    ];

    $query = new WP_Query($args);
    $html = '';
    while ($query->have_posts()) {
        $query->the_post();
        $post_id = get_the_ID();
        $html .= apply_filters('waf_template', apply_filters("waf_template_{$post_type}", '', $post_id), $post_id);
    }

    echo $html;
    wp_die();
}

add_filter('waf_template', function ($html, $post_id) {
    return '<div class="waf-single">
        <a href="' . get_post_permalink($post_id) . '"><h3>' . get_the_title($post_id) . '</h3></a>
        <p>' . get_the_excerpt($post_id) . '</p>
    </div>';
}, 10, 2);
