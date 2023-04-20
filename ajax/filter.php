<?php

add_action('wp_ajax_filter', 'ajax_mn_filter');

if (!function_exists('ajax_mn_filter')) {
    function ajax_mn_filter()
    {
        // Is a valid request? Check the nonce
        check_ajax_referer('ajax-mn', 'nonce');

        // Get the page from the search params with 1 as fallback value
        //$page = isset($_GET['page']) ? $_GET['page'] : 1;
        //$taxonomies = array();
        $tax_query = array();
        foreach (array_keys($_GET) as $taxonomy) {
            //if (isset($key, ['page', 'nonce', 'action'])) continue;
            if ($taxonomy == "action" || $taxonomy == "nonce" || $taxonomy == "") {
                continue;
            }
            $tax_keys = array_filter(explode(",", $_GET[$taxonomy]), function ($cat) {
                return $cat !== '';
            });
            if (sizeof($tax_keys)) {
                $terms = $tax_keys;
            } else {
                $terms = array_map(function ($term) {
                    return $term->slug;
                }, get_terms($taxonomy));
            }
            $tax_query[] = array(
                'taxonomy' => $taxonomy,
                'field'    => 'slug',
                'terms' => $terms
            );
        }
        echo print_r($tax_query);
        // $split_genero = array_filter(explode(",", $categories['genero']), function ($cat) {
        //     return $cat !== '';
        // });
        // $split_metraje = explode(",", $categories['metraje']);
        // $split_pueblo = explode(",", $categories['pueblo_indigena']);
        // $split_tematica = explode(",", $categories['tematica']);
        // $split_zona = explode(",", $categories['zona_geografica']);
        // //throw new Exception(print_r($split_metraje));
        //$array_genero =  explode(",", $categories['genero']);
        // Get query arguments

        // if (sizeof($split_genero)) {

        //     $tax_query[] = array(
        //         'taxonomy' => 'genero',
        //         'field'    => 'slug',
        //         'terms' => $split_genero
        //     );
        // }
        // $tax_query[] = array(
        //     'taxonomy' => 'genero',
        //     'field'    => 'slug',
        //     'terms' => $split_genero
        // );
        // $tax_query[] = array(
        //     'taxonomy' => 'metraje',
        //     'field'    => 'slug',
        //     'terms' => $split_metraje
        // );
        // $tax_query[] = array(
        //     'taxonomy' => 'pueblo_indigena',
        //     'field'    => 'slug',
        //     'terms' => $split_pueblo
        // );
        // $tax_query[] = array(
        //     'taxonomy' => 'tematica',
        //     'field'    => 'slug',
        //     'terms' => $split_tematica
        // );
        // $tax_query[] = array(
        //     'taxonomy' => 'zona_geografica',
        //     'field'    => 'slug',
        //     'terms' => $split_zona
        // );

        $args = array(
            'post_type' => 'pelicula',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'tax_query' => $tax_query
        );

        // Execute the query
        $query = new WP_Query($args);

        // Initialize the result as an empty array
        // $result = array();

        // $data = array(
        //     'posts' => array(),
        //     'pages' => 0
        // );

        $html = '';
        // Loop over the query results
        while ($query->have_posts()) {
            $query->the_post();
            $ID = get_the_ID();
            $thumbnail = get_the_post_thumbnail($ID, '');
            //$html .= '<div class="test">';
            //$html .= $ID;
            //$html .= get_the_title();
            //$html .= '</div>';
            // if (!$thumbnail) {
            //     $thumbnail = get_template_directory_uri() . '/assets/images/event--default.png';
            // }

            // array_push($data['posts'], array(
            //     'id' => $ID,
            //     'title' => get_the_title($ID),
            //     'category' => get_the_category($ID),
            //     'excerpt' => get_the_excerpt($ID),
            //     'url' => get_post_permalink($ID),
            //     'thumbnail' => $thumbnail,
            //     'author' => get_the_author($ID),
            //     'date' => get_the_date('j \d\e F \d\e Y', $ID),
            //     'tag' => get_the_tags($ID),
            //     'lang' => pll_get_post_language($ID)
            // ));
            //$html .= mn_render_filtered_post();
            //ob_start();
            $html .= pods('pelicula', $ID)->template('Search Película');
            //$html .= ob_get_contents();
            //ob_clean();
            //throw new Exception(print_r($split_metraje));
        }

        // wp_send_json($data, 200);
        echo $html;
        wp_die();
    }

    function mn_render_filtered_post()
    {
        // return '<div class="post">' . $the_post->title() . '</div>';
        ob_start();
        //get_template_part('template-parts/content', apply_filter('ajax_mn_list_template', 'post-list'));
        pods('pelicula', $ID)->template('Search Película');
        $html = ob_get_contents();
        ob_clean();
        return $html;
    }
}
