<?php
add_action('rest_api_init', function () {
    register_rest_route('custom/v1', '/all-content(?:/(?P<type>\w+))?', array(
        'methods' => 'GET',
        'callback' => 'get_all_content',
    ));
});

function get_all_content($data) {
    $type = isset($data['type']) ? sanitize_text_field($data['type']) : array('post', 'page');

    if (!is_array($type) && !in_array($type, array('post', 'page'))) {
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
            'url' => str_replace(home_url(), '', get_permalink($post->ID)),
            'is_homepage' => (get_option('page_on_front') == $post->ID),
            'tags' => get_the_tags($post->ID),
            'type' => $post->post_type,
            'blocks' => get_content_by_post_id($post->ID),
        );
    }

    return $result;
}