<?php

/**
 * Plugin Name:     WP Ajax Filters
 * Plugin URI:      https://github.com/codeccoop/wp-ajax-filters
 * Description:     Archive filters by wp ajax requests
 * Author:          Còdec Coop
 * Author URI:      https://www.codeccoop.org
 * Text Domain:     waf
 * Domain Path:     /languages
 * Version:         0.1.0
 *
 * @package         wp_ajax_filters
 */

define('WAF_VERSION', '0.1.0');
define('WAF_ENV', 'development');

require_once 'inc/ajax/filter.php';
require_once 'inc/ajax/search.php';
require_once 'inc/shortcodes.php';

add_action('wp_enqueue_scripts', 'waf_enqueue_scripts');
function waf_enqueue_scripts()
{
    wp_register_script(
        'waf-tax-filter',
        plugin_dir_url(__FILE__) . 'js/tax-filter.js',
        ['jquery'],
        WAF_VERSION,
    );

    wp_register_script(
        'waf-searcher',
        plugin_dir_url(__FILE__) . 'js/searcher.js',
        ['jquery'],
        WAF_VERSION,
    );

    wp_localize_script(
        'waf-tax-filter',
        '_wafTaxFilterSafeguard',
        [
            'nonce' => wp_create_nonce('waf-tax-filter'),
            'url' => admin_url('admin-ajax.php'),
        ]
    );

    wp_localize_script(
        'waf-searcher',
        '_wafSearchSafeguard',
        [
            'nonce' => wp_create_nonce('waf-searcher'),
            'url' => admin_url('admin-ajax.php'),
        ]
    );

    wp_enqueue_script(
        'jquery-ui',
        'https://unpkg.com/multiple-select@1.5.2/dist/multiple-select.min.js',
        ['jquery'],
        '1.13.1'
    );

    wp_enqueue_style(
        'jquery-ui-theme',
        'https://unpkg.com/multiple-select@1.5.2/dist/multiple-select.min.css',
    );
}

add_filter('script_loader_tag', 'waf_script_module', 10, 3);
function waf_script_module($tag, $handle, $src)
{
    if (!in_array($handle, ['waf-tax-filter', 'waf-searcher'])) {
        return $tag;
    }

    $url = esc_url($src);
    $tag = "<script type='module' src='{$url}'></script>";
    return $tag;
}

add_filter('waf_template', function ($html, $post_id) {
    if ($html) {
        return $html;
    }
    return '<div class="waf-single">
        <a href="' . get_post_permalink($post_id) . '"><h3>' . get_the_title($post_id) . '</h3></a>
        <p>' . get_the_excerpt($post_id) . '</p>
    </div>';
}, 10, 2);

add_action('init', 'waf_load_textdomain');
function waf_load_textdomain()
{
    load_plugin_textdomain('waf', false, dirname(plugin_basename(__FILE__)) . '/languages');
}

add_filter('load_textdomain_mofile', 'waf_textdomain', 10, 2);
function waf_textdomain($mofile, $domain)
{
    if ('waf' === $domain && false !== strpos($mofile, WP_LANG_DIR . '/plugins/')) {
        $locale = apply_filters('plugin_locale', determine_locale(), $domain);
        $mofile = WP_PLUGIN_DIR . '/' . dirname(plugin_basename(__FILE__)) . '/languages/' . $domain . '-' . $locale . '.mo';
    }
    return $mofile;
}
