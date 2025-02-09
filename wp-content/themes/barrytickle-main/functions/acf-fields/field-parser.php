<?php 

class FieldParser {
    private $field_value;
    private $field_type;
    private $parsed_value;
    private $field_name;
    private $post_id;

    public function __construct($field_type, $field_value, $field_name = null, $post_id = null)    
    {
        $this->field_value = $field_value;
        $this->field_type = $field_type;
        $this->field_name = $field_name;
        $this->post_id = $post_id;

        // Store the parsed value
        $this->parsed_value = $this->parse_fields();
    }

    public function parse_fields(){
        if($this->field_type === 'image'){
            return $this->parse_image();
        }
        if($this->field_type === 'group'){
            return $this->parse_group();
        }
        if($this->field_type === 'gallery'){
            return $this->parse_gallery();
        }
        if($this->field_type === 'post_object'){
            return $this->parse_post_object();
        }
        if($this->field_type === 'acfe_hidden'){
            return $this->parse_hidden_field();   
        }
        return $this->field_value;
    }

    public function parse_hidden_field(){
        $post = get_post($this->post_id);

        if($this->field_name === 'page_title') {
            return $post->post_title;
        }
        if($this->field_name === 'featured_image'){
            return get_the_post_thumbnail_url($post->ID, 'full');
        }

        return $this->field_value;
    }

    public function parse_post_object(){
        $post = get_post($this->field_value);
        $blocks = parse_blocks($post->post_content);

        $parsed_blocks = array();

        foreach($blocks as $block){
            if(strpos($block['blockName'], 'acf/') === false) continue;
            $parsed_fields = array();

            $ind = 0;
            foreach($block['attrs']['data'] as $block_key => $block_value){
                if(strpos('_', $block_key) === 0) continue;

                $key = $block['attrs']['data']['_' . $block_key];
                $field = get_field_object($key);

                if($field){
                    $field_type = $field['type'];

                    $data = consistent_field_render($field['name'], $field_type, $block_value, $post->ID);
                    $parsed_fields[$field['name']] = $data;
                }

                $ind++;
            }


            array_push($parsed_blocks, $parsed_fields);
        }
        
        return array (
            'id' => $post->ID, 
            'title' => $post->post_title,
            'url' => get_permalink($post->ID),
            'featured_image' => get_the_post_thumbnail_url($post->ID, 'full'),
            'blocks' => $parsed_blocks,
        );


        return $post_object;
    }

    public function parse_image(){
        return wp_get_attachment_image_src($this->field_value, 'full');
    }

    public function parse_group(){
        $parsed_group = array();
        foreach($this->field_value as $key => $value){
            if(!isset($value['type']) || !isset($value['value'])) return $this->field_value;
            $parsed_group[$key] = new FieldParser($value['type'], $value['value']);
        }
        return $parsed_group;
    }

    public function parse_gallery(){
        $parsed_gallery = array();
        foreach($this->field_value as $value){
            $parsed = new FieldParser('image', $value);
            array_push($parsed_gallery, $parsed->get_parsed_value());
        }
        return $parsed_gallery;
    }

    // Getter method to retrieve the parsed value
    public function get_parsed_value() {
        return $this->parsed_value;
    }
}
