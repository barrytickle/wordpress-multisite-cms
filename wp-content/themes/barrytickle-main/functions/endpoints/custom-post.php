<?php
add_action('rest_api_init', function () {
    register_rest_route('custom/v1', '/custom-posts/(?P<slug>\w+)', array(
        'methods' => 'GET',
        'callback' => 'get_custom_posts_by_slug',
    ));
});

function get_custom_posts_by_slug($data) {
    $slug = isset($data['slug']) ? sanitize_text_field($data['slug']) : '';

    if (empty($slug)) {
        return new WP_Error('invalid_slug', 'Invalid custom post type slug', array('status' => 400));
    }

    $args = array(
        'post_type' => $slug,
        'posts_per_page' => -1
    );

    $posts = get_posts($args);

    if (empty($posts)) {
        return new WP_Error('not_found', 'No posts found for the given slug', array('status' => 404));
    }

    $result = array();

    foreach ($posts as $post) {
        $result[] = array(
            'id' => $post->ID,
            'title' => get_the_title($post->ID),
            'excerpt' => get_the_excerpt($post->ID),
            'url' => str_replace(home_url(), '', get_permalink($post->ID)),
            'type' => $post->post_type,
        );
    }

    return $result;
}