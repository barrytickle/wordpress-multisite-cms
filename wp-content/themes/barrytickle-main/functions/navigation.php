<?php
// Register custom navigation menu locations
function barrytickle_register_nav_menus() {
    register_nav_menus(array(
        'website-menu' => __('Website Menu', 'barrytickle'),
        'website-menu-2' => __('Website Menu 2', 'barrytickle'),
        'website-menu-3' => __('Website Menu 3', 'barrytickle'),
        'website-menu-4' => __('Website Menu 4', 'barrytickle'),
        'website-menu-5' => __('Website Menu 5', 'barrytickle'),
        'website-menu-6' => __('Website Menu 6', 'barrytickle'),
        'website-menu-7' => __('Website Menu 7', 'barrytickle'),
        'website-menu-8' => __('Website Menu 8', 'barrytickle'),
        'website-menu-9' => __('Website Menu 9', 'barrytickle')
        
    ));
}
add_action('init', 'barrytickle_register_nav_menus');

// Display a navigation menu
function barrytickle_display_nav_menu($location) {
    if (has_nav_menu($location)) {
        wp_nav_menu(array(
            'theme_location' => $location,
            'container' => 'nav',
            'container_class' => $location . '-nav',
            'menu_class' => 'menu'
        ));
    }
}
?>