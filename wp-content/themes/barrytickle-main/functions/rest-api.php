<?php 

function add_cors_http_header(){
    header("Access-Control-Allow-Origin: *");
}
add_action('init','add_cors_http_header');


add_action('rest_api_init', function () {
    register_rest_route('custom/v1', '/all-content(?:/(?P<type>\w+))?', array(
        'methods' => 'GET',
        'callback' => 'get_all_content',
    ));
});

add_action('rest_api_init', function () {
    register_rest_route('custom/v1', '/options', array(
        'methods' => 'GET',
        'callback' => 'get_acf_options',
    ));
});

add_action('rest_api_init', function () {
    register_rest_route('custom/v1', '/content/(?P<id>\d+)', array(
        'methods' => 'GET',
        'callback' => 'get_content_by_id',
    ));
});

function get_acf_options() {
    if (!function_exists('acf_get_options_page')) {
        return new WP_Error('acf_not_found', 'ACF plugin is not active', array('status' => 500));
    }

    $field_groups = acf_get_field_groups();
    $options = array();

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

function process_value($value) {
    if (is_array($value)) {
        foreach ($value as &$sub_value) {
            $sub_value = process_value($sub_value);
        }
    } else {
        $image = wp_get_attachment_image_src($value, 'full');
        if ($image && isset($image[0])) {
            $value = $image[0];
        }
    }
    return $value;
}

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

                    $value = process_value($value);

                    array_push($fields, [
                        'field_name' => $key,
                        'field_id' => $field_id,
                        'field_type' => $field_type,
                        'field_value' => $value,
                    ]);
                } 
            }

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


function get_content_by_id($data) {
    $post_id = isset($data['id']) ? intval($data['id']) : 0;
    
    if (!$post_id) {
        return new WP_Error('invalid_id', 'Invalid post ID', array('status' => 400));
    }
}

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
            'blocks' => get_content_by_post_id($post->ID),
            'type' => $post->post_type,
        );
    }

    return $result;
}

function get_acf_field_type($field_key) {
    $field = get_field_object($field_key);
    return $field ? $field['type'] : null;
}


add_action('rest_api_init', function () {
    register_rest_route('custom/v1', '/menus', array(
        'methods' => 'GET',
        'callback' => 'get_all_menus',
    ));
});

function get_all_menus() {
    $menus = wp_get_nav_menus();
    $result = array();

    foreach ($menus as $menu) {
        $menu_items = wp_get_nav_menu_items($menu->term_id);
        $items = array();

        // Create an associative array of items by their ID
        $items_by_id = array();
        foreach ($menu_items as $item) {
            $items_by_id[$item->ID] = array(
                'id' => $item->ID,
                'title' => $item->title,
                'url' => preg_replace('#/+#', '/', ($item->menu_item_parent ? str_replace(home_url(), '', $items_by_id[$item->menu_item_parent]['url']) : '') . str_replace(home_url(), '', $item->url)),
                'parent' => $item->menu_item_parent,
                'order' => $item->menu_order,
                'type' => $item->type,
                'cta' => get_post_meta($item->ID, '_menu_item_cta', true),
                'children' => array(),
            );
        }

        // Assign children to their parent items
        foreach ($items_by_id as &$item) {
            if ($item['parent']) {
                $items_by_id[$item['parent']]['children'][] = &$item;
            } else {
                $items[] = &$item;
            }
        }

        $result[] = array(
            'id' => $menu->term_id,
            'name' => $menu->name,
            'slug' => $menu->slug,
            'items' => $items,
        );
    }

    return $result;
}

add_action('wp_nav_menu_item_custom_fields', function($item_id, $item, $depth, $args, $id) {
    $cta = get_post_meta($item_id, '_menu_item_cta', true);
    ?>
    <p class="description description-wide">
        <label for="edit-menu-item-cta-<?php echo $item_id; ?>">
            <?php _e('CTA'); ?><br>
            <select id="edit-menu-item-cta-<?php echo $item_id; ?>" class="widefat edit-menu-item-cta" name="menu-item-cta[<?php echo $item_id; ?>]">
                <option value="false" <?php selected($cta, 'false'); ?>><?php _e('False'); ?></option>
                <option value="true" <?php selected($cta, 'true'); ?>><?php _e('True'); ?></option>
            </select>
        </label>
    </p>
    <?php
}, 10, 5);

add_action('wp_update_nav_menu_item', function($menu_id, $menu_item_db_id, $args) {
    if (isset($_POST['menu-item-cta'][$menu_item_db_id])) {
        update_post_meta($menu_item_db_id, '_menu_item_cta', sanitize_text_field($_POST['menu-item-cta'][$menu_item_db_id]));
    } else {
        delete_post_meta($menu_item_db_id, '_menu_item_cta');
    }
}, 10, 3);

add_filter('wp_setup_nav_menu_item', function($menu_item) {
    $menu_item->cta = get_post_meta($menu_item->ID, '_menu_item_cta', true);
    return $menu_item;
});



