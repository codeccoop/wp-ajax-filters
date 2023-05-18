<?php
function ajax_mn_filters()
{

    //$html = '<form role="search" method="get" class="search-form" action="' . home_url('/') . '">';
    //$html .= '<label>';
    //$html .= '<span class="screen-reader-text">' . _x('Search for:', 'label') . '</span>';
    $html = '<input type="text" class="search-field" placeholder="' . esc_attr_x('busca tu pelicula …', 'placeholder') . '" . name="search"/>';
    //$html .= '</label>';
    $html .= '<button class="search-submit">' . esc_attr_x('Search', 'submit button') . '</button>';

    $taxonomies = get_taxonomies();
    $names = get_taxonomy_labels($taxonomies[2]);

    $taxonomies = array_slice($taxonomies, 10, 17);
    $taxonomies = array_diff($taxonomies, ["produccion", "realizacion", "cataleg"]);
    $html .= '<div class="ajax_mn_filters alignwide">';
    foreach ($taxonomies as $taxonomy) {
        $html .= '<div class="ajax_mn_filtercont">';
        $html .= '<label for="' . $taxonomy . '">' . get_taxonomy($taxonomy)->labels->name . '</label>';
        $html .= '<select class="ajax_mn_filter" id="' . $taxonomy . '" multiple="multiple"">';
        $terms = get_terms($taxonomy);
        //$html .= '<option selected="selected" >selecciona un valor </option>';
        foreach ($terms as $term) {
            $html .= '<option value="' . $term->slug . '">' . $term->name . '</option>';
        }
        $html .= '</select>';
        $html .= '</div>';
    }

    $html .= '</div>';
    $html .= '<script>
        document.querySelectorAll(".ajax_mn_filter").forEach(filter => {
            filter.addEventListener("change", () => {

            })
        });
    </script>';
    $html .= '<div class="ajax_mn_content alignwide"></div>';

    return $html;
}
//do_action('wp_ajax_filter');
add_shortcode('ajax_mn_filters', 'ajax_mn_filters');
