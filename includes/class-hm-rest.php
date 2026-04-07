<?php
if (!defined('ABSPATH')) {
    exit;
}

class HM_REST {
    public static function init(): void {
        add_action('rest_api_init', [self::class, 'register_routes']);
        add_action('rest_api_init', [self::class, 'register_rest_fields']);
    }

    public static function register_routes(): void {
        register_rest_route('honeymooner/v1', '/leads', [
            'methods' => 'POST',
            'callback' => [self::class, 'submit_lead'],
            'permission_callback' => '__return_true',
        ]);

        register_rest_route('honeymooner/v1', '/packages', [
            'methods' => 'GET',
            'callback' => [self::class, 'get_packages'],
            'permission_callback' => '__return_true',
        ]);

        register_rest_route('honeymooner/v1', '/packages/(?P<slug>[a-zA-Z0-9-]+)', [
            'methods' => 'GET',
            'callback' => [self::class, 'get_package_by_slug'],
            'permission_callback' => '__return_true',
            'args' => [
                'slug' => [
                    'required' => true,
                    'sanitize_callback' => 'sanitize_title',
                ],
            ],
        ]);

        register_rest_route('honeymooner/v1', '/packages-options', [
            'methods' => 'GET',
            'callback' => [self::class, 'get_package_options'],
            'permission_callback' => '__return_true',
        ]);

        register_rest_route('honeymooner/v1', '/destinations', [
            'methods' => 'GET',
            'callback' => [self::class, 'get_destinations'],
            'permission_callback' => '__return_true',
        ]);

        register_rest_route('honeymooner/v1', '/destinations/(?P<slug>[a-zA-Z0-9-]+)', [
            'methods' => 'GET',
            'callback' => [self::class, 'get_destination_by_slug'],
            'permission_callback' => '__return_true',
            'args' => [
                'slug' => [
                    'required' => true,
                    'sanitize_callback' => 'sanitize_title',
                ],
            ],
        ]);
    }

    public static function submit_lead(WP_REST_Request $request): WP_REST_Response {
        $email = sanitize_email((string) $request->get_param('email'));
        $traveler_name = sanitize_text_field((string) $request->get_param('traveler_name'));
        $phone = sanitize_text_field((string) $request->get_param('phone'));
        $package_name = sanitize_text_field((string) $request->get_param('package_name'));
        if (!$email || !is_email($email) || $traveler_name === '' || $phone === '' || $package_name === '') {
            return new WP_REST_Response(['success' => false, 'message' => 'Package, name, email, and phone are required.'], 422);
        }

        $lead_id = HM_Leads_DB::insert([
            'package_id' => $request->get_param('package_id'),
            'package_name' => $package_name,
            'package_tier' => $request->get_param('package_tier'),
            'departure_date' => $request->get_param('departure_date'),
            'adults' => $request->get_param('adults'),
            'children' => $request->get_param('children'),
            'traveler_name' => $traveler_name,
            'email' => $email,
            'phone' => $phone,
            'country_of_residence' => $request->get_param('country_of_residence'),
            'occasion' => $request->get_param('occasion'),
            'message' => $request->get_param('message'),
            'status' => 'new',
            'source_url' => $request->get_param('source_url'),
        ]);

        return new WP_REST_Response(['success' => true, 'lead_id' => $lead_id, 'message' => 'Enquiry submitted successfully.'], 201);
    }

    public static function get_packages(WP_REST_Request $request): WP_REST_Response {
        $posts = get_posts([
            'post_type' => 'packages',
            'post_status' => 'publish',
            'numberposts' => -1,
            'orderby' => 'menu_order title',
            'order' => 'ASC',
        ]);
        $items = array_map([self::class, 'format_package'], $posts);
        return new WP_REST_Response(['items' => $items], 200);
    }

    public static function get_package_by_slug(WP_REST_Request $request): WP_REST_Response {
        $posts = get_posts([
            'post_type' => 'packages',
            'name' => $request['slug'],
            'post_status' => ['publish', 'draft'],
            'numberposts' => 1,
        ]);
        if (!$posts) {
            return new WP_REST_Response(['message' => 'Package not found.'], 404);
        }
        return new WP_REST_Response(self::format_package($posts[0]), 200);
    }

    public static function get_package_options(WP_REST_Request $request): WP_REST_Response {
        $posts = get_posts([
            'post_type' => 'packages',
            'post_status' => 'publish',
            'numberposts' => -1,
            'orderby' => 'title',
            'order' => 'ASC',
        ]);
        $items = [];
        foreach ($posts as $post) {
            $meta = self::get_package_meta($post->ID);
            $items[] = [
                'id' => $post->ID,
                'package_id' => $meta['package_id'],
                'title' => get_the_title($post),
                'slug' => $post->post_name,
                'featured_image_url' => get_the_post_thumbnail_url($post, 'full') ?: null,
                'pricing_tiers' => is_array($meta['pricing_tiers']) ? $meta['pricing_tiers'] : [],
                'starting_price' => $meta['starting_price'],
                'currency' => $meta['currency'],
                'pricing_basis' => $meta['pricing_basis'],
            ];
        }
        return new WP_REST_Response(['items' => $items], 200);
    }

    public static function get_destinations(WP_REST_Request $request): WP_REST_Response {
        $posts = get_posts([
            'post_type' => 'destinations',
            'post_status' => 'publish',
            'numberposts' => -1,
            'orderby' => 'title',
            'order' => 'ASC',
        ]);
        $items = array_map([self::class, 'format_destination'], $posts);
        return new WP_REST_Response(['items' => $items], 200);
    }

    public static function get_destination_by_slug(WP_REST_Request $request): WP_REST_Response {
        $posts = get_posts([
            'post_type' => 'destinations',
            'name' => $request['slug'],
            'post_status' => ['publish', 'draft'],
            'numberposts' => 1,
        ]);
        if (!$posts) {
            return new WP_REST_Response(['message' => 'Destination not found.'], 404);
        }
        return new WP_REST_Response(self::format_destination($posts[0]), 200);
    }

    private static function get_package_meta(int $post_id): array {
        $keys = ['package_id','destination_id','category','subtitle','summary','intro_content','days','nights','starting_price','currency','pricing_basis','rating','review_count','pricing_tiers','inclusions','exclusions','departures','itinerary','seo_title','meta_description','canonical_url'];
        $meta = [];
        foreach ($keys as $key) {
            $meta[$key] = get_post_meta($post_id, $key, true);
        }
        return $meta;
    }

    private static function get_destination_meta(int $post_id): array {
        $keys = ['subtitle','intro_content','best_time_to_visit','seo_title','meta_description','canonical_url','highlights','gallery'];
        $meta = [];
        foreach ($keys as $key) {
            $meta[$key] = get_post_meta($post_id, $key, true);
        }
        return $meta;
    }

    public static function format_package($post): array {
        $meta = self::get_package_meta($post->ID);
        $destination_id = (int) ($meta['destination_id'] ?? 0);
        $destination = $destination_id ? get_post($destination_id) : null;

        return [
            'id' => $post->ID,
            'slug' => $post->post_name,
            'title' => get_the_title($post),
            'excerpt' => has_excerpt($post) ? get_the_excerpt($post) : '',
            'content' => apply_filters('the_content', $post->post_content),
            'featured_image_url' => get_the_post_thumbnail_url($post, 'full') ?: null,
            'package_id' => $meta['package_id'] ?: (string) $post->ID,
            'category' => $meta['category'] ?: '',
            'subtitle' => $meta['subtitle'] ?: '',
            'summary' => $meta['summary'] ?: '',
            'intro_content' => $meta['intro_content'] ?: '',
            'days' => (int) ($meta['days'] ?: 0),
            'nights' => (int) ($meta['nights'] ?: 0),
            'starting_price' => is_numeric($meta['starting_price']) ? (float) $meta['starting_price'] : 0,
            'currency' => $meta['currency'] ?: 'NGN',
            'pricing_basis' => $meta['pricing_basis'] ?: 'per_couple',
            'rating' => is_numeric($meta['rating']) ? (float) $meta['rating'] : 0,
            'review_count' => (int) ($meta['review_count'] ?: 0),
            'pricing_tiers' => is_array($meta['pricing_tiers']) ? $meta['pricing_tiers'] : [],
            'inclusions' => is_array($meta['inclusions']) ? $meta['inclusions'] : [],
            'exclusions' => is_array($meta['exclusions']) ? $meta['exclusions'] : [],
            'departures' => is_array($meta['departures']) ? $meta['departures'] : [],
            'itinerary' => is_array($meta['itinerary']) ? $meta['itinerary'] : [],
            'seo' => [
                'title' => $meta['seo_title'] ?: get_the_title($post),
                'meta_description' => $meta['meta_description'] ?: '',
                'canonical_url' => $meta['canonical_url'] ?: get_permalink($post),
            ],
            'destination' => $destination ? [
                'id' => $destination->ID,
                'title' => get_the_title($destination),
                'slug' => $destination->post_name,
            ] : null,
            'updated_at' => mysql_to_rfc3339($post->post_modified_gmt ?: $post->post_modified),
        ];
    }

    public static function format_destination($post): array {
        $meta = self::get_destination_meta($post->ID);
        $related_packages = get_posts([
            'post_type' => 'packages',
            'post_status' => 'publish',
            'numberposts' => -1,
            'meta_key' => 'destination_id',
            'meta_value' => $post->ID,
        ]);

        return [
            'id' => $post->ID,
            'slug' => $post->post_name,
            'title' => get_the_title($post),
            'excerpt' => has_excerpt($post) ? get_the_excerpt($post) : '',
            'content' => apply_filters('the_content', $post->post_content),
            'featured_image_url' => get_the_post_thumbnail_url($post, 'full') ?: null,
            'subtitle' => $meta['subtitle'] ?: '',
            'intro_content' => $meta['intro_content'] ?: '',
            'best_time_to_visit' => $meta['best_time_to_visit'] ?: '',
            'highlights' => is_array($meta['highlights']) ? $meta['highlights'] : [],
            'gallery' => is_array($meta['gallery']) ? $meta['gallery'] : [],
            'seo' => [
                'title' => $meta['seo_title'] ?: get_the_title($post),
                'meta_description' => $meta['meta_description'] ?: '',
                'canonical_url' => $meta['canonical_url'] ?: get_permalink($post),
            ],
            'related_packages' => array_map(function ($item) {
                return [
                    'id' => $item->ID,
                    'title' => get_the_title($item),
                    'slug' => $item->post_name,
                    'featured_image_url' => get_the_post_thumbnail_url($item, 'full') ?: null,
                ];
            }, $related_packages),
            'updated_at' => mysql_to_rfc3339($post->post_modified_gmt ?: $post->post_modified),
        ];
    }

    public static function register_rest_fields(): void {
        foreach (['packages', 'destinations'] as $type) {
            register_rest_field($type, 'featured_image_url', [
                'get_callback' => function (array $post_arr) {
                    $image_id = get_post_thumbnail_id($post_arr['id']);
                    return $image_id ? wp_get_attachment_image_url($image_id, 'full') : null;
                },
                'schema' => ['type' => 'string', 'context' => ['view', 'edit']],
            ]);
        }
    }
}
