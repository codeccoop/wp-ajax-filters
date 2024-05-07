<?php

add_shortcode('waf_tax_filter', 'waf_tax_filter');
function waf_tax_filter($atts = [])
{
    try {
        if (!isset($atts['taxonomies'])) {
            throw new Exception(__('I need the taxonomies', 'waf'));
        }
        if (!isset($atts['el'])) {
            throw new Exception(__('I need the css selector of the content', 'waf'));
        }
        if (!isset($atts['post_type'])) {
            $atts['post_type'] = 'post';
        }
    } catch (Exception $e) {
        return '[' . $e->getMessage() . ']';
    }

    $tax_names = array_map(function ($tax_name) {
        return trim($tax_name, ' ');
    }, explode(',', $atts['taxonomies']));
    $taxonomies = array_values(array_filter(get_taxonomies([], 'objects'), function ($tax) use ($tax_names) {
        return in_array($tax->name, $tax_names);
    }));

    if (!wp_script_is('waf-tax-filter')) {
        wp_enqueue_script('waf-tax-filter');
    }

    ob_start(); ?>
    <div class="waf-filter-form" aria-controls="<?= $atts['el']; ?>">
        <div class="waf-controls">
            <?php foreach ($taxonomies as $tax) : ?>
                <div class="waf-control" data-type="select" id="<?= $tax->name; ?>">
                    <label for="<?= $tax->name; ?>"><?= __($tax->label, 'waf') ?></label>
                    <select name="<?= $tax->name; ?>" multiple>
                        <?php
                        $terms = get_terms(['taxonomy' => $tax->name, 'hide_empty' => true]);
                foreach ($terms as $term) : ?>
                            <option value="<?= $term->slug; ?>"><?= $term->name; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            <?php endforeach; ?>
            <div class="waf-control" data-type="hidden" style="display: none">
                <input type="text" name="post_type" value="<?= $atts['post_type']; ?>" />
            </div>
        </div>
    </div>
<?php

    return ob_get_clean();
}

add_shortcode('waf_searcher', 'waf_searcher');
function waf_searcher($atts = [])
{
    try {
        if (!isset($atts['el'])) {
            throw new Exception('Em falta el selector css del contingut');
        }

        if (!isset($atts['post_type'])) {
            $atts['post_type'] = 'post';
        }

        if (!isset($atts['taxonomies'])) {
            $atts['taxonomies'] = [];
        } else {
            $atts['taxonomies'] = array_map(function ($tax) {
                return trim($tax);
            }, explode(',', $atts['taxonomies']));
        }
    } catch (Exception $e) {
        return '[' . $e->getMessage() . ']';
    }

    if (!wp_script_is('waf-searcher')) {
        wp_enqueue_script('waf-searcher');
    }

    ob_start(); ?>
    <div class="waf-search-form" aria-controls="<?= $atts['el']; ?>">
        <div class="waf-controls">
            <div class="waf-control" data-type="text" id="pattern">
                <input name="pattern" type="text" placeholder="<?= __('What are you looking for?', 'waf') ?>"/>
            </div>
            <div class="waf-control" data-type="hidden" style="display: none">
                <input type="text" name="post_type" value="<?= $atts['post_type']; ?>" />
            </div>
            <div class="waf-control" data-type="submit">
                <input type="submit" value="<?= __('Search', 'waf'); ?>" />
            </div>
        </div>
    </div>
<?php

    return ob_get_clean();
}

add_shortcode('waf_pager', 'waf_pager');
function waf_pager($atts = [])
{
    $per_page = isset($atts['per_page']) ? (int) $atts['per_page'] : get_option('posts_per_page');
    return "<div class='waf-pager' data-perpage='{$per_page}'></div>";
}
