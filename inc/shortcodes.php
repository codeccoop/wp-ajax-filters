<?php

add_shortcode('waf_tax_filter', 'waf_tax_filter');
function waf_tax_filter($atts = [])
{
    if (!isset($atts['taxonomies'])) throw new Exception("Em fan falte les taxonomies");
    if (!isset($atts['template'])) $atts['template'] = 'default';

    $tax_names = explode(',', $atts['taxonomies']);
    $taxonomies = array_values(array_filter(get_taxonomies([], 'objects'), function ($tax) use ($tax_names) {
        return in_array($tax->name, $tax_names);
    }));

    if (!wp_script_is('waf-tax-filter')) wp_enqueue_style('waf-tax-filter');

    ob_start(); ?>
    <div class="waf-filter-fields">
        <?php foreach ($taxonomies as $tax) : ?>
            <div class="waf-filter-field">
                <label for="<?= $tax->slug; ?>"><?= $tax->label ?></label>
                <select name="<?= $tax->slug; ?>" id="<?= $tag->slug; ?>" multiple>
                    <option value=""><?= __('Desactivat', 'waf-ajax-filter'); ?></option>
                    <?php
                    $terms = get_terms($tax->name);
                    foreach ($terms as $term) : ?>
                        <option value="<?= $term->slug; ?>"><?= $term->name; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        <?php endforeach; ?>
        <div class="waf-filter-field hidden">
            <input type="text" value="<?= $atts['template']; ?>" />
        </div>
    </div>
<?php

    return ob_get_clean();
}

add_shortcode('waf_tax_filter', 'waf_tax_filter');
function waf_searcher()
{
}
