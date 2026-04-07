<?php
/**
 * Plugin Name: The Honeymooner Core
 * Description: Core CMS plugin for The Honeymooner. Manages Destinations, Packages, Leads, admin UI, and REST integration for Next.js.
 * Version: 1.1.0
 * Author: OpenAI
 * Text Domain: the-honeymooner-core
 */

<?php
/**
 * Plugin Name: The Honeymooner Core v2
 */

if (!defined('ABSPATH')) exit;

require_once plugin_dir_path(__FILE__) . 'includes/rest.php';

add_action('init', function() {

    register_post_type('packages', [
        'label' => 'Packages',
        'public' => true,
        'show_in_rest' => true,
        'supports' => ['title','editor','thumbnail','excerpt']
    ]);

    register_post_type('destinations', [
        'label' => 'Destinations',
        'public' => true,
        'show_in_rest' => true,
        'supports' => ['title','editor','thumbnail','excerpt']
    ]);

});
