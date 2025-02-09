<?php 
require_once get_template_directory() . '/functions/default.php';
require_once get_template_directory() . '/functions/helpers.php';
require_once get_template_directory() . '/functions/acf-blocks.php';
require_once get_template_directory() . '/functions/navigation.php';
require_once get_template_directory() . '/functions/rest-api.php';
require_once get_template_directory() . '/functions/acf-fields/field-parser.php';


function prepare_rest_api($response, $post, $request){
    $parsed_blocks = array();
    if (function_exists('parse_blocks')) {
        $blocks = parse_blocks($post->post_content);

        foreach($blocks as $block){
            if(strpos($block['blockName'], 'acf/') !== false && isset($block['attrs']['data'])){
                array_push($parsed_blocks, $block);
            }
        }
        $response->data['blocks'] = $parsed_blocks; // Adds blocks to REST API response
    }
    return $response;
}

add_filter('rest_prepare_post', function($response, $post, $request){
    return prepare_rest_api($response, $post, $request);
}, 10, 3);

add_filter('rest_prepare_page', function($response, $post, $request){
    return prepare_rest_api($response, $post, $request);
}, 10, 3);


function modify_acf_blocks_in_rest_response($response, $post, $request) {
    if (function_exists('parse_blocks')) {
        $blocks = parse_blocks($post->post_content);

        foreach ($blocks as &$block) {
            if (!isset($block['attrs']['data'])) {
                continue; // Skip if no ACF data
            }
            // $fieldParser = new FieldParser($field_key, $field_value);
            foreach ($block['attrs']['data'] as $field_key => $field_value) {
                if(strpos($field_key, '_') === 0) {
                    continue;
                }

                $key = $block['attrs']['data']['_' . $field_key];
                $field = get_field_object($key);

                if ($field) {
                    $field_type = $field['type'];

                    $block['attrs']['data'][$field_key] = consistent_field_render($field['name'], $field_type,$field_value);
                }
            }
        }

        $response->data['blocks'] = $blocks;
    }
    return $response;
}

// Apply to both posts and pages
add_filter('rest_prepare_post', 'modify_acf_blocks_in_rest_response', 10, 3);
add_filter('rest_prepare_page', 'modify_acf_blocks_in_rest_response', 10, 3);


add_action('init', function() {
    if (function_exists('acf_get_field_types')) {
        error_log("ACF is loaded and working.");
    } else {
        error_log("ACF is NOT loaded.");
    }
});

