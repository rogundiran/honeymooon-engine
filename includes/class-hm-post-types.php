<?php
if (!defined('ABSPATH')) {
    exit;
}

class HM_Post_Types {
    public static function init(): void {
        add_action('init', [self::class, 'register']);
        add_action('init', [self::class, 'register_meta']);
    }

    public static function register(): void {
        register_post_type('destinations', [
            'labels' => [
                'name' => 'Destinations',
                'singular_name' => 'Destination',
                'menu_name' => 'Destinations',
                'add_new_item' => 'Add New Destination',
                'edit_item' => 'Edit Destination',
            ],
            'public' => true,
            'show_ui' => true,
            'show_in_menu' => 'hm_dashboard',
            'show_in_rest' => true,
            'rest_base' => 'destinations',
            'menu_icon' => 'dashicons-location-alt',
            'supports' => ['title', 'editor', 'thumbnail', 'excerpt'],
            'rewrite' => ['slug' => 'destinations'],
            'has_archive' => false,
            'capability_type' => ['destination', 'destinations'],
            'map_meta_cap' => true,
        ]);

        register_post_type('packages', [
            'labels' => [
                'name' => 'Packages',
                'singular_name' => 'Package',
                'menu_name' => 'Packages',
                'add_new_item' => 'Add New Package',
                'edit_item' => 'Edit Package',
            ],
            'public' => true,
            'show_ui' => true,
            'show_in_menu' => 'hm_dashboard',
            'show_in_rest' => true,
            'rest_base' => 'packages',
            'menu_icon' => 'dashicons-palmtree',
            'supports' => ['title', 'editor', 'thumbnail', 'excerpt'],
            'rewrite' => ['slug' => 'packages'],
            'has_archive' => false,
            'capability_type' => ['package', 'packages'],
            'map_meta_cap' => true,
        ]);
    }

    public static function register_meta(): void {
        $destination_meta = [
            'subtitle' => 'string',
            'intro_content' => 'string',
            'best_time_to_visit' => 'string',
            'seo_title' => 'string',
            'meta_description' => 'string',
            'canonical_url' => 'string',
            'highlights' => 'array',
        ];
        foreach ($destination_meta as $key => $type) {
            register_post_meta('destinations', $key, [
                'single' => true,
                'type' => $type,
                'show_in_rest' => ['schema' => ['type' => $type]],
                'auth_callback' => function () { return current_user_can('edit_posts'); },
            ]);
        }

        $package_meta = [
            'package_id' => 'string',
            'destination_id' => 'integer',
            'category' => 'string',
            'subtitle' => 'string',
            'summary' => 'string',
            'intro_content' => 'string',
            'days' => 'integer',
            'nights' => 'integer',
            'starting_price' => 'number',
            'currency' => 'string',
            'pricing_basis' => 'string',
            'rating' => 'number',
            'review_count' => 'integer',
            'pricing_tiers' => 'array',
            'inclusions' => 'array',
            'exclusions' => 'array',
            'departures' => 'array',
            'itinerary' => 'array',
            'seo_title' => 'string',
            'meta_description' => 'string',
            'canonical_url' => 'string',
        ];
        foreach ($package_meta as $key => $type) {
            register_post_meta('packages', $key, [
                'single' => true,
                'type' => $type,
                'show_in_rest' => ['schema' => ['type' => $type]],
                'auth_callback' => function () { return current_user_can('edit_posts'); },
            ]);
        }
    }
}
