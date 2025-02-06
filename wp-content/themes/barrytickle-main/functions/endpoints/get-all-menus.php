<?php

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
