<?php
require_once get_template_directory() . '/functions/helpers.php';

$endpoint_files = glob(get_template_directory() . '/functions/endpoints/*.php');
foreach ($endpoint_files as $file) {
    require_once $file;
}