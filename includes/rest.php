<?php

class HM_Core_REST {

    public static function init() {
        add_action('rest_api_init', function() {

            register_rest_route('honeymooner/v1', '/packages', [
                'methods' => 'GET',
                'callback' => [self::class, 'get_packages']
            ]);

        });
    }

    public static function get_packages() {

        $posts = get_posts([
            'post_type' => 'packages',
            'numberposts' => -1
        ]);

        $items = [];

        foreach ($posts as $post) {
            $items[] = self::normalize($post->ID);
        }

        return ['items' => $items];
    }

    private static function normalize($id) {

        $days = (int) get_post_meta($id, 'duration_days', true);
        $nights = (int) get_post_meta($id, 'duration_nights', true);

        $tiers = [];
        $tier_keys = ['premium','luxuria','ultra_luxuria'];

        foreach ($tier_keys as $key) {
            $price = get_post_meta($id, "pricing_tiers_{$key}_price", true);

            if ($price) {
                $tiers[] = [
                    'tier_id' => $key,
                    'tier_name' => ucfirst($key),
                    'tier_price' => (float)$price
                ];
            }
        }

        $starting_price = 0;
        if (!empty($tiers)) {
            $prices = array_column($tiers, 'tier_price');
            $starting_price = min($prices);
        }

        return [
            'id' => $id,
            'title' => get_the_title($id),
            'slug' => get_post_field('post_name', $id),
            'featured_image_url' => get_the_post_thumbnail_url($id, 'full'),
            'days' => $days,
            'nights' => $nights,
            'starting_price' => $starting_price,
            'pricing_tiers' => $tiers
        ];
    }
}

HM_Core_REST::init();
