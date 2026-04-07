<?php
if (!defined('ABSPATH')) {
    exit;
}

class HM_Post_Types {
    public static function init(): void {
        add_action('init', [self::class, 'register'], 0);
        add_action('init', [self::class, 'register_meta'], 1);
    }

    public static function register(): void {
        $destination_labels = [
            'name' => 'Destinations',
            'singular_name' => 'Destination',
            'menu_name' => 'Destinations',
            'name_admin_bar' => 'Destination',
            'add_new' => 'Add New',
            'add_new_item' => 'Add New Destination',
            'new_item' => 'New Destination',
            'edit_item' => 'Edit Destination',
            'view_item' => 'View Destination',
            'all_items' => 'All Destinations',
            'search_items' => 'Search Destinations',
            'not_found' => 'No destinations found.',
            'not_found_in_trash' => 'No destinations found in Trash.',
        ];

        register_post_type('destinations', [
            'labels' => $destination_labels,
            'public' => true,
            'publicly_queryable' => true,
            'show_ui' => true,
            'show_in_menu' => 'hm_dashboard',
            'show_in_admin_bar' => true,
            'show_in_nav_menus' => false,
            'show_in_rest' => true,
            'rest_base' => 'destinations',
            'rest_controller_class' => 'WP_REST_Posts_Controller',
            'menu_icon' => 'dashicons-location-alt',
            'supports' => ['title', 'editor', 'thumbnail', 'excerpt', 'revisions'],
            'rewrite' => ['slug' => 'destinations', 'with_front' => false],
            'has_archive' => false,
            'hierarchical' => false,
            'exclude_from_search' => false,
            'query_var' => true,
            'capability_type' => ['destination', 'destinations'],
            'map_meta_cap' => true,
        ]);

        $package_labels = [
            'name' => 'Packages',
            'singular_name' => 'Package',
            'menu_name' => 'Packages',
            'name_admin_bar' => 'Package',
            'add_new' => 'Add New',
            'add_new_item' => 'Add New Package',
            'new_item' => 'New Package',
            'edit_item' => 'Edit Package',
            'view_item' => 'View Package',
            'all_items' => 'All Packages',
            'search_items' => 'Search Packages',
            'not_found' => 'No packages found.',
            'not_found_in_trash' => 'No packages found in Trash.',
        ];

        register_post_type('packages', [
            'labels' => $package_labels,
            'public' => true,
            'publicly_queryable' => true,
            'show_ui' => true,
            'show_in_menu' => 'hm_dashboard',
            'show_in_admin_bar' => true,
            'show_in_nav_menus' => false,
            'show_in_rest' => true,
            'rest_base' => 'packages',
            'rest_controller_class' => 'WP_REST_Posts_Controller',
            'menu_icon' => 'dashicons-palmtree',
            'supports' => ['title', 'editor', 'thumbnail', 'excerpt', 'revisions'],
            'rewrite' => ['slug' => 'packages', 'with_front' => false],
            'has_archive' => false,
            'hierarchical' => false,
            'exclude_from_search' => false,
            'query_var' => true,
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
            'gallery' => 'array',
        ];
        foreach ($destination_meta as $key => $type) {
            register_post_meta('destinations', $key, [
                'single' => true,
                'type' => $type,
                'show_in_rest' => [
                    'schema' => ['type' => $type],
                ],
                'sanitize_callback' => [self::class, 'sanitize_meta_value'],
                'auth_callback' => function () {
                    return current_user_can('edit_posts');
                },
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
                'show_in_rest' => [
                    'schema' => ['type' => $type],
                ],
                'sanitize_callback' => [self::class, 'sanitize_meta_value'],
                'auth_callback' => function () {
                    return current_user_can('edit_posts');
                },
            ]);
        }
    }

    public static function sanitize_meta_value($value, $meta_key = '', $object_type = '') {
        if (is_array($value)) {
            return self::sanitize_recursive($value);
        }
        if (is_numeric($value)) {
            return $value + 0;
        }
        return sanitize_textarea_field((string) $value);
    }

    private static function sanitize_recursive(array $value): array {
        $clean = [];
        foreach ($value as $k => $v) {
            $key = sanitize_key((string) $k);
            if (is_array($v)) {
                $clean[$key] = self::sanitize_recursive($v);
            } elseif (is_numeric($v)) {
                $clean[$key] = $v + 0;
            } else {
                $clean[$key] = sanitize_textarea_field((string) $v);
            }
        }
        return $clean;
    }
}
