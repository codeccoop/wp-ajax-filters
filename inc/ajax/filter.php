<?php

add_action('wp_ajax_waf_tax_filter', 'waf_ajax_waf_tax_filter');

function waf_ajax_waf_tax_filter()
{
    check_ajax_referer('waf-script', 'nonce');
    //     foreach (array_keys($_GET) as $taxonomy) {

    //         if ($taxonomy == "action" || $taxonomy == "nonce" || $taxonomy == "") {
    //             continue;
    //         }

    //         $tax_keys = array_filter(explode(",", $_GET[$taxonomy]), function ($cat) {
    //             return $cat !== '';
    //         });
    //         if (sizeof($tax_keys)) {
    //             $terms = $tax_keys;
    //         } else {
    //             $terms = array_map(function ($term) {
    //                 return $term->slug;
    //             }, get_terms($taxonomy));
    //         }

    //         $tax_query[] = array(
    //             'taxonomy' => $taxonomy,
    //             'field'    => 'slug',
    //             'terms' => $terms,
    //         );
    //     }
    //     if ($title) {
    //         $args = array(
    //             'post_type' => 'pelicula',
    //             'post_status' => 'publish',
    //             'posts_per_page' => -1,
    //             'name' => $title,
    //             'tax_query' => $tax_query
    //         );
    //     } else {
    //         $args = array(
    //             'post_type' => 'pelicula',
    //             'post_status' => 'publish',
    //             'posts_per_page' => -1,
    //             'tax_query' => $tax_query
    //         );
    //     }
    //     // Execute the query
    //     $query = new WP_Query($args);

    //     // Initialize the result as an empty array

    //     $html = '';
    //     // Loop over the query results
    //     while ($query->have_posts()) {
    //         $query->the_post();
    //         $ID = get_the_ID();
    //         $thumbnail = get_the_post_thumbnail($ID, '');
    //         $html .= pods('pelicula', $ID)->template('Search Película');
    //     }

    //     echo $html;
    //     wp_die();
    // }

    function post_like_title_filter($where, $wp_query)
    {
        global $wpdb;
        if ($search_term = $wp_query->get('cc_search_post_title')) {
            $where .= ' AND LOWER(' . $wpdb->posts . '.post_title) LIKE LOWER(\'%' . $wpdb->esc_like($search_term) . '%\')';
        }
        echo substr($where, 57);
        return substr($where, 57);
    }

    // function mn_render_filtered_post()
    // {
    //     ob_start();
    //     pods('pelicula', $ID)->template('Search Película');
    //     $html = ob_get_contents();
    //     ob_clean();
    //     return $html;
    // }
    $search_term = "plat";
    // global $wpdb;
    // echo $wpdb->esc_like($search_term);
    // wp_die();
    $title_like_query = array(
        'cc_search_post_title' => $search_term, // search post title only
        'post_status' => 'publish',
        'post_type' => 'pelicula'
    );
    //if ($_GET["search-field"] !== "") {
    //$search_term = $_GET["search-field"];
    add_filter('posts_where', 'post_like_title_filter', 10, 2);
    $like_query = new WP_Query($title_like_query);
    remove_filter('posts_where', 'post_like_title_filter', 10);
    //echo print_r($like_query);
    //$all_wp_pages = $like_query->query(array('post_type' => 'page'));
    //echo $all_wp_pages;
    $html = "";
    while ($like_query->have_posts()) {
        $like_query->the_post();
        $ID = get_the_ID();
        $thumbnail = get_the_post_thumbnail($ID, '');
        $html .= pods('pelicula', $ID)->template('Search Película');
    }


    echo $html;
    wp_die();


    // $posts = get_posts(array('suppress_filters' => FALSE));
    // echo print_r($posts);
}
