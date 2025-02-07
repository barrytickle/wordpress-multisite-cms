<?php 
require_once get_template_directory() . '/functions/default.php';
require_once get_template_directory() . '/functions/helpers.php';
require_once get_template_directory() . '/functions/endpoints/index.php';
require_once get_template_directory() . '/functions/acf-blocks.php';
require_once get_template_directory() . '/functions/navigation.php';


add_action('init', function() {
    if (function_exists('acf_get_field_types')) {
        error_log("ACF is loaded and working.");
    } else {
        error_log("ACF is NOT loaded.");
    }
});

