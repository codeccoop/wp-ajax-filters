<?php

/**
 * Plugin Name:     Ajax Miradanativa
 * Plugin URI:      PLUGIN SITE HERE
 * Description:     PLUGIN DESCRIPTION HERE
 * Author:          YOUR NAME HERE
 * Author URI:      YOUR SITE HERE
 * Text Domain:     ajax-mn
 * Domain Path:     /languages
 * Version:         0.1.0
 *
 * @package         Ajax_mn
 */

// Your code starts here.
define('AJAX_MN_VERSION', '0.0.1');
define('AJAX_MN_ENV', 'development');

require_once plugin_dir_path(__FILE__) . 'ajax/filter.php';
require_once plugin_dir_path(__FILE__) . 'inc/filters.php';

add_action('wp_enqueue_scripts', 'ajax_scripts');
function ajax_scripts()
{
    wp_enqueue_script(
        'ajax-mn',
        plugin_dir_url(__FILE__) . '/js/index.js',
        array(),
        AJAX_MN_VERSION
    );

    $localize_args = apply_filters('ajax_mn_localize_args', array(
        'nonce' => wp_create_nonce('ajax-mn'),
        'url' => admin_url('admin-ajax.php'),
        'selector' => '.ajax_mn_filter'
    ));

    wp_localize_script(
        'ajax-mn',
        'wp_ajax',
        $localize_args
    );
    wp_enqueue_script(
        'jQuery-UI',
        'https://unpkg.com/multiple-select@1.5.2/dist/multiple-select.min.js',
        array(
            'jquery'
        ),
        '1.13.1'
    );
    wp_enqueue_style('plugin-style', plugins_url('plugin-style.css', __FILE__));
    wp_enqueue_style('jQuery-UI-theme', 'https://unpkg.com/multiple-select@1.5.2/dist/multiple-select.min.css');
}



add_filter('ajax_mn_localize_args', 'my_theme_localize_args');
function my_theme_localize_args($args)
{
    $args['options'] = array(1, 2, 3);
    return $args;
}


add_filter('ajax_mn_query_args', 'my_theme_query_args');
function my_theme_query_args($args)
{
    $args['post_type'] = 'pelicula';
    $args['meta_key'] = 'date';
    $args['orderby'] = 'meta_value';
    return $args;
}


add_filter('ajax_mn_each_post', 'my_theme_each_post', 2);
function my_theme_each_post($data, $ID)
{
    $post = get_post($ID);
    return $data;
}
