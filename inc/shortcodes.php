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

    $tax_names = explode(',', $atts['taxonomies']);
    $taxonomies = array_values(array_filter(get_taxonomies([], 'objects'), function ($tax) use ($tax_names) {
        return in_array($tax->name, $tax_names);
    }));

    if (!wp_script_is('waf-tax-filter')) wp_enqueue_script('waf-tax-filter');

    ob_start(); ?>
    <div class="waf-filter-form" for="<?= $atts['el']; ?>">
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
}
