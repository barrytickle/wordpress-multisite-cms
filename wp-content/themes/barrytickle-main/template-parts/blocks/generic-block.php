<?php
// Extract passed block variables
$block_name = $args['block_name'];
$fields = $args['fields'];
?>

<div class="acf-block <?= esc_attr($block_name); ?>">
    <h2><?= esc_html($fields['title'] ?? 'Default Title'); ?></h2>
    <p><?= esc_html($fields['content'] ?? 'Default Content'); ?></p>

    <?php if (!empty($fields['image'])) : ?>
        <img src="<?= esc_url($fields['image']['url']); ?>" alt="<?= esc_attr($fields['image']['alt']); ?>" />
    <?php endif; ?>
</div>