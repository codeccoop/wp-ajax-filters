<?php

add_shortcode('waf_tax_filter', 'waf_tax_filter');
function waf_tax_filter($atts = [])
{
    try {
        if (!isset($atts['taxonomies'])) throw new Exception('Em fan falte les taxonomies');
        if (!isset($atts['el'])) throw new Exception('Em falta el selector css del contingut');
        if (!isset($atts['post_type'])) $atts['post_type'] = 'post';
    } catch (Exception $e) {
        return '[' . $e->getMessage() . ']';
    }

    $tax_names = array_map(function ($tax_name) {
        return trim($tax_name, ' ');
    }, explode(',', $atts['taxonomies']));
    $taxonomies = array_values(array_filter(get_taxonomies([], 'objects'), function ($tax) use ($tax_names) {
        return in_array($tax->name, $tax_names);
    }));

    if (!wp_script_is('waf-tax-filter')) wp_enqueue_script('waf-tax-filter');

    ob_start(); ?>
    <div class="waf-filter-form" aria-controls="<?= $atts['el']; ?>">
        <?php foreach ($taxonomies as $tax) : ?>
            <div class="waf-filter-field" data-type="select" id="<?= $tax->name; ?>">
                <label for="<?= $tax->name; ?>"><?= $tax->label ?></label>
                <select name="<?= $tax->name; ?>" multiple>
                    <?php
                    $terms = get_terms($tax->name);
                    foreach ($terms as $term) : ?>
                        <option value="<?= $term->slug; ?>"><?= $term->name; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        <?php endforeach; ?>
        <div class="waf-filter-field" data-type="hidden" style="display: none">
            <input type="text" name="post_type" value="<?= $atts['post_type']; ?>" />
        </div>
    </div>
<?php

    return ob_get_clean();
}

add_shortcode('waf_tax_filter', 'waf_tax_filter');
function waf_searcher()
{
    try {
        if (!isset($atts['el'])) throw new Exception('Em falta el selector css del contingut');
        if (!isset($atts['post_type'])) $atts['post_type'] = 'post';
    } catch (Exception $e) {
        return '[' . $e->getMessage() . ']';
    }

    ob_start(); ?>
    <div class="waf-filter-form" aria-controls="<?= $atts['el']; ?>">
        <div class="waf-filter-field" data-type="text" id="search_term">
            <label for="search-pattern"><?= __('search', 'wp-ajax-filters'); ?></label>
            <input name="search_term" type="text" />
        </div>
        <div class="waf-filter-field" data-type="hidden" style="display: none">
            <input type="text" name="post_type" value="<?= $atts['post_type']; ?>" />
        </div>
        <div class="waf-filter-field" data-type="submit">
            <input type="submit" value="<?= __('submit', 'wp-ajax-filters'); ?>" />
        </div>
    </div>
<?php

    return ob_get_clean();
}
