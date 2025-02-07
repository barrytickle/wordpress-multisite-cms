<?php

// Register custom block category
add_filter('block_categories_all', function ($categories) {
    // Define the custom category
    $custom_category = [
        'slug'  => 'site-blocks',
        'title' => __('Site Blocks', 'textdomain'),
    ];

    // Remove existing "Site Blocks" category if it exists to prevent duplication
    foreach ($categories as $key => $category) {
        if ($category['slug'] === 'site-blocks') {
            unset($categories[$key]);
        }
    }

    // Prepend the custom category to the top of the list
    array_unshift($categories, $custom_category);

    return $categories;
});

function my_acf_block_render_callback( $block ) {
    // Use a single template file
    get_template_part('template-parts/blocks/generic-block', null, ['block' => $block]);
}

add_action('acf/init', function () {
    if (function_exists('acf_register_block_type')) {
        $field_groups = acf_get_field_groups();

        foreach ($field_groups as $group) {
            $block_name = sanitize_title($group['title']);

            // Exclude option pages
            if (isset($group['location']) && is_array($group['location'])) {
                $location = $group['location'][0][0]['param'];
                if(isset($location) && $location === 'options_page') {
                    continue;
                }
            }

            acf_register_block_type([
                'name'              => $block_name,
                'title'             => $group['title'],
                'category'          => 'site-blocks',  // Assign to custom category
                'icon'              => 'admin-generic',
                'keywords'          => [$block_name],
                'render_callback'   => function ($block) use ($group, $block_name) {
                    // Get all fields in the field group
                    $fields = acf_get_fields($group['key']);
                    $field_data = [];

                    foreach ($fields as $field) {
                        $field_data[$field['name']] = get_field($field['name']);
                    }

                    // Render the generic block template
                    get_template_part('template-parts/blocks/generic-block', null, [
                        'block'      => $block,
                        'block_name' => $block_name,
                        'fields'     => $field_data
                    ]);
                },
            ]);
        }
    } 
});



