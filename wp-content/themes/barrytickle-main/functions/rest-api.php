<?php 


add_action('rest_api_init', function() {
    register_rest_route('wp/v2', '/options/', [
        'methods' => 'GET',
        'callback' => 'get_acf_options_data',
    ]);

    register_rest_route('wp/v2', '/options/(?P<page>(?!all)[a-zA-Z0-9-]+)', [
        'methods' => 'GET',
        'callback' => 'get_acf_options_page_data',
    ]);

    register_rest_route('wp/v2', '/options/all', [
        'methods' => 'GET',
        'callback' => 'get_all_options_pages',
    ]);

});
function get_all_options_pages() {
    $acf = acf_get_field_groups();

    if(!$acf) return [];

    print_r($acf);


    foreach($acf as $a){
        $location = $a['location'][0][0]['param'];
        $slug = $a['location'][0][0]['value'];
        if(!isset($location) || !isset($slug)) continue;

        if ($location !== 'options_page') continue;

        print_r($location);

        array_push($list, [
            'slug' => $slug,
            'location' => $location,
        ]);
            
    }
    return rest_ensure_response($list);
}

function get_acf_options_page_data($request) {

    $page = $request['page'];

    $acf = acf_get_field_groups();

    if(!$acf) return [];

    foreach($acf as $a){
        $location = $a['location'][0][0]['param'];
        $slug = $a['location'][0][0]['value'];
        if(!isset($location) || !isset($slug)) continue;

        if ($location !== 'options_page') continue;

        if (strpos($slug, $page) !== false) {
            $fields = acf_get_fields($a['key']);
            $fields = array_map(function($field) {
                return [
                    'name' => $field['name'],
                    'label' => $field['label'],
                    'type' => $field['type'],
                    'value' => get_field($field['name'], 'option'),
                ];
            }, $fields);
            return rest_ensure_response($fields);
        }
    }

    return [];
}

function get_acf_options_data() {
    $field_groups = acf_get_field_groups();
    $all_fields = [];

    foreach ($field_groups as $group) {
        $location = $group['location'][0][0]['param'];
        if($location !== 'options_page') continue;
        $fields = acf_get_fields($group['key']);

        foreach ($fields as $field) {
            $all_fields[$field['name']] = consistent_field_render($field['name'], $field['type'], get_field($field['name'], 'option'));
        }
    }

    return rest_ensure_response($all_fields);
}


function modify_acf_rest_response($response, $post, $request) {
    return $response;
}

add_filter('rest_prepare_post', 'modify_acf_rest_response', 10, 3);
add_filter('rest_prepare_page', 'modify_acf_rest_response', 10, 3);
add_filter('rest_prepare_services', 'modify_acf_rest_response', 10, 3); // Add for CPTs if needed

