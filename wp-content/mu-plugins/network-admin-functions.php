<?php 
/**
 * Plugin Name: Network Admin Functions
 * Description: Custom functions for network administration.
 * Network: true
 */

 if (!defined('WP_NETWORK_ADMIN')) {
    return;
}

function generate_api_key() {
    return bin2hex(random_bytes(16)); // Generates a 32-character hexadecimal string
}

function add_api_key_to_user($user_id) {
    $api_key = generate_api_key();
    update_user_meta($user_id, 'api_key', $api_key);
}

add_action('user_register', 'add_api_key_to_user'); // Hook for new users

function add_api_key_to_existing_users() {
    $users = get_users();
    foreach ($users as $user) {
        if (!get_user_meta($user->ID, 'api_key', true)) {
            add_api_key_to_user($user->ID);
        }
    }
}

add_action('network_admin_edit', 'add_api_key_to_existing_users'); // Hook for existing users when network admin dashboard is accessed


// Add API Key column to the users table in the network admin
add_filter('wpmu_users_columns', function ($columns) {
    $columns['api_key'] = 'API Key';
    return $columns;
});

add_action('manage_users_custom_column', function ($value, $column_name, $user_id) {
    if ($column_name == 'api_key') {
        return get_user_meta($user_id, 'api_key', true);
    }
    return $value;
}, 10, 3);

add_filter('manage_users_sortable_columns', function ($columns) {
    $columns['api_key'] = 'api_key';
    return $columns;
});

// Add Update Key column to the users table in the network admin
add_filter('wpmu_users_columns', function ($columns) {
    $columns['update_key'] = 'Update Key';
    return $columns;
});

// Add content to the Update Key column
add_action('manage_users_custom_column', function ($value, $column_name, $user_id) {
    if ($column_name == 'update_key') {
        return '<form method="post" action="">
                    <input type="hidden" name="user_id" value="' . $user_id . '">
                    <input type="submit" name="update_api_key" value="Update">
                </form>';
    }
    return $value;
}, 10, 3);

// Handle the form submission to update the API key
add_action('admin_init', function () {
    if (isset($_POST['update_api_key']) && isset($_POST['user_id'])) {
        $user_id = intval($_POST['user_id']);
        add_api_key_to_user($user_id);
        wp_redirect(add_query_arg('updated', 'true', wp_get_referer()));
        exit;
    }
});

