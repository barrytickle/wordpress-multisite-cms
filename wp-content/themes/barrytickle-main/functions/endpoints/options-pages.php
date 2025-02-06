<?php
add_action('rest_api_init', function () {
    register_rest_route('custom/v1', '/options', array(
        'methods' => 'GET',
        'callback' => 'get_acf_options',
    ));
});

function get_acf_options() {
    if (!function_exists('acf_get_options_page')) {
        return new WP_Error('acf_not_found', 'ACF plugin is not active', array('status' => 500));
    }

    $field_groups = acf_get_field_groups();

    foreach ($field_groups as $field_group) {
        $location = $field_group['location'][0][0]['param'];
        $slug = $field_group['location'][0][0]['value'];

        if(!isset($location) || !isset($slug)) continue;

        $groups = array();
        $groups['fields'] = array();

        if ($location === 'options_page') {
            $camel_case_slug = str_replace(' ', '', ucwords(preg_replace('/[^a-zA-Z0-9]+/', ' ', lcfirst($slug))));
            $groups['fields'][$camel_case_slug] = get_fields('option');
        }
    }

    return $groups;
}