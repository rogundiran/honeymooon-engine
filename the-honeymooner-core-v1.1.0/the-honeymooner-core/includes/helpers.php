<?php
if (!defined('ABSPATH')) {
    exit;
}

function hm_get_array_meta($post_id, $key) {
    $value = get_post_meta($post_id, $key, true);
    return is_array($value) ? $value : [];
}
