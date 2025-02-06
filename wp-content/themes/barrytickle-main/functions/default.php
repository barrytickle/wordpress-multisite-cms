
<?php

function cc_mime_types($mimes) {
    $mimes['svg'] = 'image/svg+xml';
    return $mimes;
}
add_filter('upload_mimes', 'cc_mime_types');


function add_cors_http_header(){
    header("Access-Control-Allow-Origin: *");
}
add_action('init','add_cors_http_header');


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
