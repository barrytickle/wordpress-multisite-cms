<?php

add_action('rest_api_init', function () {
    register_rest_route('custom/v1', '/page/(?P<id>\d+)/(?P<block>\w+)', array(
        'methods' => 'GET',
        'callback' => 'get_page_block_by_id',
    ));
});

function get_page_block_by_id($data) {
    $post_id = isset($data['id']) ? intval($data['id']) : 0;
    $block_name = isset($data['block']) ? sanitize_text_field($data['block']) : '';

    if (!$post_id || !$block_name) {
        return new WP_Error('invalid_request', 'Invalid post ID or block name', array('status' => 400));
    }

    $post = get_post($post_id);

    if (!$post) {
        return new WP_Error('not_found', 'Post not found', array('status' => 404));
    }

    $content = get_post_field('post_content', $post_id);
    $acf_data = array();

    if (has_blocks($content)) {
        $blocks = parse_blocks($content);

        foreach ($blocks as $block) {
            if (strpos($block['blockName'], 'acf/' . $block_name) !== false) {
                if (!isset($block['attrs']['data'])) continue;
                $fields = [];
                foreach ($block['attrs']['data'] as $key => $value) {
                    if (strpos($key, '_') !== 0) {
                        $field_id = $block['attrs']['data']['_' . $key];
                        if (!$field_id) continue;
                        $field_type = get_acf_field_type($field_id);
                        $value = process_value($value);
                        if ($field_type === 'link') $value = parse_link($value);
                        array_push($fields, [
                            'field_name' => $key,
                            'field_id' => $field_id,
                            'field_type' => $field_type,
                            'field_value' => $value,
                        ]);
                    }
                }

                $fields = checkForRepeater($fields);
                
                $acf_data = [
                    'block_name' => $block['blockName'],
                    'fields' => $fields,
                ];
                break;
            }
        }
    }

    return $acf_data;
}
