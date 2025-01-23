<?php 


add_action('rest_api_init', function () {
    register_rest_route('custom/v1', '/all-content(?:/(?P<type>\w+))?', array(
        'methods' => 'GET',
        'callback' => 'get_all_content',
    ));
});

add_action('rest_api_init', function () {
    register_rest_route('custom/v1', '/content/(?P<id>\d+)', array(
        'methods' => 'GET',
        'callback' => 'get_content_by_id',
    ));
});

function get_content_by_post_id($post_id) {
    $post = get_post($post_id);

    if (!$post) {
        return new WP_Error('not_found', 'Post not found', array('status' => 404));
    }

    $content = get_post_field('post_content', $post_id);
    $blocks = parse_blocks($content);

    $acf_data = array();

    if (has_blocks($content)) {
        $blocks = parse_blocks($content);

        foreach ($blocks as $block) {
            if(!isset($block['attrs']['data'])) return;
            $fields = [];
            foreach($block['attrs']['data'] as $key => $value) {

                if (strpos($key, '_') !== 0) {
                    $field_id = $block['attrs']['data']['_'.$key];

                    if(!$field_id) return;
                    $field_type = get_acf_field_type($field_id);

                    if($field_type && $field_type === 'image') {
                        $image = wp_get_attachment_image_src($value, 'full');
                        if ($image && $image[0]){
                            $image = $image[0];
                        } 
                        else {
                            $image = '';
                        }
                    }
                    array_push($fields, [
                        'field_name' => $key,
                        'field_id' => $field_id,
                        'field_type' => $field_type,
                        'field_value' => $field_type === 'image' ? $image : $value,
                    ]);
                } 
            }

            array_push($acf_data, [
                'block_name' => $block['blockName'],
                'fields' => $fields,
            ]);
        }
        $result = array(
            'id' => $post_id,
            'title' => get_the_title($post->ID),
            'blocks' => $acf_data,
        );
    } else {
        $result = [];
    }
    return $result;
}


function get_content_by_id($data) {
    $post_id = isset($data['id']) ? intval($data['id']) : 0;
    
    if (!$post_id) {
        return new WP_Error('invalid_id', 'Invalid post ID', array('status' => 400));
    }
}

function get_all_content($data) {

    $type = isset($data['type']) ? sanitize_text_field($data['type']) : 'post';

    if (!in_array($type, array('post', 'page'))) {
        return new WP_Error('invalid_type', 'Invalid content type', array('status' => 400));
    }

    $args = array(
        'post_type' => $type,
        'posts_per_page' => -1
    );

    $posts = get_posts($args);

    $result = array();

    foreach ($posts as $post) {
        $result[] = array(
            'id' => $post->ID,
            'title' => get_the_title($post->ID),
            'excerpt' => get_the_excerpt($post->ID),
            'category' => get_the_category($post->ID),
            'tags' => get_the_tags($post->ID),
            'blocks' => get_content_by_post_id($post->ID),
        );
    }

    return $result;
}

function get_acf_field_type($field_key) {
    $field = get_field_object($field_key);
    return $field ? $field['type'] : null;
}




