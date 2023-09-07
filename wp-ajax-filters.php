<?php

/**
 * Plugin Name:     WP Ajax Filters
 * Plugin URI:      https://github.com/codeccoop/wp-ajax-filters
 * Description:     Archive filters by wp ajax requests
 * Author:          Còdec Coop
 * Author URI:      https://www.codeccoop.org
 * Text Domain:     wp-ajax-filters
 * Domain Path:     /languages
 * Version:         0.1.0
 *
 * @package         wp_ajax_filters
 */

define('WAF_VERSION', '0.1.0');
define('WAF_ENV', 'development');

require_once plugin_dir_path(__FILE__) . 'inc/ajax/filter.php';
require_once plugin_dir_path(__FILE__) . 'inc/ajax/search.php';
require_once plugin_dir_path(__FILE__) . 'inc/shortcodes.php';

add_action('wp_enqueue_scripts', 'waf_enqueue_scripts');
function waf_enqueue_scripts()
{
    wp_register_script(
        'waf-tax-filter',
        plugin_dir_url(__FILE__) . '/js/waf-tax-filter.js',
        [],
        WAF_VERSION,
    );

    wp_register_script(
        'waf-searcher',
        plugin_dir_url(__FILE__) . 'js/waf-searcher.js',
        [],
        WAF_VERSION,
    );

    wp_localize_script(
        'waf-tax-filter',
        'wp_ajax',
        [
            'nonce' => wp_create_nonce('waf-tax-filter'),
            'url' => admin_url('admin-ajax.php'),
        ]
    );

    wp_localize_script(
        'waf-search',
        'wp_ajax',
        [
            'nonce' => wp_create_nonce('waf-search'),
            'url' => admin_url('admin-ajax.php'),
        ]
    );

    wp_enqueue_script(
        'jquery-ui',
        'https://unpkg.com/multiple-select@1.5.2/dist/multiple-select.min.js',
        array(
            'jquery'
        ),
        '1.13.1'
    );

    wp_enqueue_style('waf-style', plugins_url('style.css', __FILE__));
    wp_enqueue_style('jquery-ui-theme', 'https://unpkg.com/multiple-select@1.5.2/dist/multiple-select.min.css');
}



add_filter('ajax_mn_localize_args', 'my_theme_localize_args');
function my_theme_localize_args($args)
{
    $args['options'] = array(1, 2, 3);
    return $args;
}
