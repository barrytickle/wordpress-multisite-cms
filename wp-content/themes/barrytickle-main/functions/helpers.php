<?php

function consistent_field_render($field_name, $field_type, $field_value, $post_id = null) {
    $parser = new FieldParser($field_type, $field_value, $field_name, $post_id);

    return array(
        'type' => $field_type,
        'value' => $parser->get_parsed_value(),
    );
}