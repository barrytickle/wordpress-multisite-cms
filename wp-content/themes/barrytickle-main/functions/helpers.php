<?php

function sort_repeater_fields($fields) {
    // Get the repeater field, this bit is getting the label of the repeater field, so we can find the other repeater fields, e.g. buttons_0_button_text
    $repeater_field = array_filter($fields, function($field) {
        return $field['field_type'] === 'repeater';
    });
    $repeater_field = reset($repeater_field); // This will get the first repeater field in the array
    $label = $repeater_field['field_name']; // This will be the label of the repeater field


    $parsed_repeater = array(); // This will store the parsed repeater fields
    $new_fields = array(); // Will contain the new $fields array without the repeater fields

    foreach($fields as $field){
        // If the field is not part of the repeater, add it to the new fields array
        if (strpos($field['field_name'], $label . '_') !== 0) {
            array_push($new_fields, $field);
            continue;
        };

        // If the field is part of the repeater, parse the field name and add it to the parsed repeater array
        $parts = explode('_', $field['field_name']);

        // This will look like {type: "animated"},
        $parsed_repeater[$parts[1]][$parts[2]] = $field['field_value'];

    }

    /*
        parsed_repeater will look like this
        [
            {
                "type": "animated",
                "text" "Some text",
            }
        ]
    */

    // New fields will be a new array with only the parsed_repeater field, removing the original repeater fields.
    array_push($new_fields, array(
        'field_name' => 'parsed_repeater',
        'field_value' => $parsed_repeater));
    
    return $new_fields;
}


function checkForRepeater($fields){
    //Checks if the block has a repeater field
    $has_repeater = count(array_filter($fields, function($field) {
        return $field['field_type'] === 'repeater';
    })) > 0;

    if($has_repeater) $fields = sort_repeater_fields($fields);

    return $fields;
}



function process_value($value) {
    if (is_array($value)) {
        foreach ($value as &$sub_value) {
            $sub_value = process_value($sub_value);
        }
    } else {
        $image = wp_get_attachment_image_src($value, 'full');
        if ($image && isset($image[0])) {
            $value = array(
                'url' => $image[0],
                'alt' => get_post_meta($value, '_wp_attachment_image_alt', TRUE),
            );
        }
    }
    return $value;
}

function get_rest_api_url() {
    return esc_url_raw(rest_url('custom/v1'));
}

// When you use a page link in wordpress, it will return the full url of the page. This function will remove the base url from the link and return the relative path.
function parse_link($value){
    $url = esc_url_raw(rest_url('custom/v1'));
    $cleaned_url = preg_replace("#wp-json/.*#", "", $url);

    if (strpos($value['url'], $cleaned_url) !== false) {
        $value['url'] = '/'.str_replace($cleaned_url, '', $value['url']);

    }
    
    $link = array(
        'url' => $value['url'],
        'title' => $value['title'],
        'target' => $value['target'],
    );
    return $link;
}

function get_content_by_post_id($post_id) {
    $post = get_post($post_id);

    if (!$post) {
        return new WP_Error('not_found', 'Post not found', array('status' => 404));
    }

    $content = get_post_field('post_content', $post_id);
    
    $acf_data = array();

    if (has_blocks($content)) {
        $blocks = parse_blocks($content);

        foreach ($blocks as $block) {
            if(!isset($block['attrs']['data'])) continue;
            if (strpos($block['blockName'], 'acf') === false) continue;
            $fields = [];
            foreach($block['attrs']['data'] as $key => $value) {

                if (strpos($key, '_') !== 0) {
                    $field_id = $block['attrs']['data']['_'.$key];

                    if(!$field_id) {
                        print_r('ERROR');
                        return;
                    };
                    $field_type = get_acf_field_type($field_id);

                    $value = process_value($value);

                    if($field_type === 'link') $value = parse_link($value);

                    array_push($fields, [
                        'field_name' => $key,
                        'field_id' => $field_id,
                        'field_type' => $field_type,
                        'field_value' => $value,
                    ]);
                }
            }

            $fields = checkForRepeater($fields);

            array_push($acf_data, [
                'block_name' => $block['blockName'],
                'fields' => $fields,
            ]);
        }
       $result = $acf_data;
    } else {
        $result = [];
    }
    return $result;
}


function get_acf_field_type($field_key) {
    $field = get_field_object($field_key);
    return $field ? $field['type'] : null;
}

