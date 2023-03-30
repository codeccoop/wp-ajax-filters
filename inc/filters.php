<?php
function ajax_mn_filters()
{
    $taxonomies = get_taxonomies();
    //throw new Exception (print_r($taxonomies));
    $taxonomies = array_slice($taxonomies, 10, 17);
    $taxonomies = array_diff($taxonomies, ["produccion", "realizacion", "cataleg"]);
    $html = '<div class="ajax_mn_filters">';
    foreach ($taxonomies as $taxonomy) {
        $html .= '<div class="ajax_mn_filtercont">';
        $html .= '<label for="' . $taxonomy . '">' . $taxonomy . '</label>';
        $html .= '<select class="ajax_mn_filter" id="' . $taxonomy . '" multiple="multiple"">';
        $terms = get_terms($taxonomy);
        $html .= '<option selected="selected" >selecciona un valor </option>';
        foreach ($terms as $term) {
            $html .= '<option value="' . $term->name . '">' . $term->name . '</option>';
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
    $html .= '<div class="ajax_mn_content"></div>';

    return $html;
}
//do_action('wp_ajax_filter');
add_shortcode('ajax_mn_filters', 'ajax_mn_filters');
