<?php

add_action('wp_ajax_filter', 'ajax_mn_filter');

if (!function_exists('ajax_mn_filter')) {
    function ajax_mn_filter()
    {
        // Is a valid request? Check the nonce
        check_ajax_referer('ajax-mn', 'nonce');

        // Get the page from the search params with 1 as fallback value
        //$page = isset($_GET['page']) ? $_GET['page'] : 1;
        $categories = array();
        foreach (array_keys($_GET) as $key) {
            //if (isset($key, ['page', 'nonce', 'action'])) continue;
            $categories[$key] = $_GET[$key];
        }

        // Get query arguments
        $args = apply_filters('ajax_mn_query_args', array(
            'post_type' => 'pelicula',
            'post_status' => 'publish',
            'posts_per_page' => -1,
        ));

        // Execute the query
        $query = new WP_Query($args);

        // Initialize the result as an empty array
        // $result = array();

        // $data = array(
        //     'posts' => array(),
        //     'pages' => 0
        // );

        $html = 'Hello';
        // Loop over the query results
        while ($query->have_posts()) {
            // $query->the_post();
            // $ID = get_the_ID();
            // $thumbnail = get_the_post_thumbnail_url($ID, 'medium');
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
            //$html .= mn_render_filtered_post($post);
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
        pods('pelicula', get_the_id())->template('Search Película');
        $html = ob_get_contents();
        ob_clean();
        return $html;
    }
}
