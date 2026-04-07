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
