<?php

function sort_repeater_fields($fields) {
    // Get the repeater field, this bit is getting the label of the repeater field, so we can find the other repeater fields, e.g. buttons_0_button_text
    $repeater_field = array_filter($fields, function($field) {
        return $field['field_type'] === 'repeater';
    });
    $repeater_field = reset($repeater_field); // This will get the first repeater field in the array
    $label = $repeater_field['field_name']; // This will be the label of the repeater field


    $parsed_repeater = array(); // This will store the parsed repeater fields
    $new_fields = array(); // Will contain the new $fields array without the repeater fields

    foreach($fields as $field){
        // If the field is not part of the repeater, add it to the new fields array
        if (strpos($field['field_name'], $label . '_') !== 0) {
            array_push($new_fields, $field);
            continue;
        };

        // If the field is part of the repeater, parse the field name and add it to the parsed repeater array
        $parts = explode('_', $field['field_name']);

        // This will look like {type: "animated"},
        $parsed_repeater[$parts[1]][$parts[2]] = $field['field_value'];

    }

    /*
        parsed_repeater will look like this
        [
            {
                "type": "animated",
                "text" "Some text",
            }
        ]
    */

    // New fields will be a new array with only the parsed_repeater field, removing the original repeater fields.
    array_push($new_fields, array(
        'field_name' => 'parsed_repeater',
        'field_value' => $parsed_repeater));
    
    return $new_fields;
}


function checkForRepeater($fields){
    //Checks if the block has a repeater field
    $has_repeater = count(array_filter($fields, function($field) {
        return $field['field_type'] === 'repeater';
    })) > 0;

    if($has_repeater) $fields = sort_repeater_fields($fields);

    return $fields;
}