<?php
if (!defined('ABSPATH')) {
    exit;
}

class HM_Package_Meta {
    public static function init(): void {
        add_action('add_meta_boxes', [self::class, 'add_meta_boxes']);
        add_action('save_post_packages', [self::class, 'save']);
    }

    public static function add_meta_boxes(): void {
        add_meta_box('hm_package_details', 'Package Details', [self::class, 'render'], 'packages', 'normal', 'high');
    }

    public static function render(WP_Post $post): void {
        wp_nonce_field('hm_package_save', 'hm_package_nonce');
        $meta_keys = ['package_id','destination_id','category','subtitle','summary','intro_content','days','nights','starting_price','currency','pricing_basis','rating','review_count','seo_title','meta_description','canonical_url'];
        $meta = [];
        foreach ($meta_keys as $key) $meta[$key] = get_post_meta($post->ID, $key, true);
        $destinations = get_posts(['post_type' => 'destinations', 'numberposts' => -1, 'post_status' => ['publish','draft']]);
        $pricing_tiers = hm_get_array_meta($post->ID, 'pricing_tiers');
        $inclusions = hm_get_array_meta($post->ID, 'inclusions');
        $exclusions = hm_get_array_meta($post->ID, 'exclusions');
        $departures = hm_get_array_meta($post->ID, 'departures');
        $itinerary = hm_get_array_meta($post->ID, 'itinerary');
        ?>
        <div class="hm-section">
            <h2>Basic Information</h2>
            <div class="hm-grid hm-grid-3">
                <p><label><strong>Package ID</strong></label><input class="widefat" type="text" name="hm_package[package_id]" value="<?php echo esc_attr($meta['package_id']); ?>"></p>
                <p><label><strong>Category</strong></label><select class="widefat" name="hm_package[category]"><?php foreach (['honeymoon'=>'Honeymoon','anniversary'=>'Anniversary','family_escape'=>'Family Escape','luxury_retreat'=>'Luxury Retreat'] as $value => $label) : ?><option value="<?php echo esc_attr($value); ?>" <?php selected($meta['category'], $value); ?>><?php echo esc_html($label); ?></option><?php endforeach; ?></select></p>
                <p><label><strong>Destination</strong></label><select class="widefat" name="hm_package[destination_id]"><option value="">Select destination</option><?php foreach ($destinations as $destination) : ?><option value="<?php echo esc_attr((string) $destination->ID); ?>" <?php selected((int) $meta['destination_id'], $destination->ID); ?>><?php echo esc_html($destination->post_title); ?></option><?php endforeach; ?></select></p>
            </div>
            <div class="hm-grid hm-grid-2">
                <p><label><strong>Subtitle</strong></label><input class="widefat" type="text" name="hm_package[subtitle]" value="<?php echo esc_attr($meta['subtitle']); ?>"></p>
                <p><label><strong>Summary</strong></label><input class="widefat" type="text" name="hm_package[summary]" value="<?php echo esc_attr($meta['summary']); ?>"></p>
            </div>
            <p><label><strong>Intro Content</strong></label><textarea class="widefat" rows="5" name="hm_package[intro_content]"><?php echo esc_textarea($meta['intro_content']); ?></textarea></p>
        </div>

        <div class="hm-section">
            <h2>Package Facts</h2>
            <div class="hm-grid hm-grid-4">
                <p><label><strong>Days</strong></label><input class="widefat" type="number" name="hm_package[days]" value="<?php echo esc_attr((string) $meta['days']); ?>"></p>
                <p><label><strong>Nights</strong></label><input class="widefat" type="number" name="hm_package[nights]" value="<?php echo esc_attr((string) $meta['nights']); ?>"></p>
                <p><label><strong>Starting Price</strong></label><input class="widefat" type="number" step="0.01" name="hm_package[starting_price]" value="<?php echo esc_attr((string) $meta['starting_price']); ?>"></p>
                <p><label><strong>Currency</strong></label><input class="widefat" type="text" name="hm_package[currency]" value="<?php echo esc_attr($meta['currency'] ?: 'NGN'); ?>"></p>
                <p><label><strong>Pricing Basis</strong></label><input class="widefat" type="text" name="hm_package[pricing_basis]" value="<?php echo esc_attr($meta['pricing_basis'] ?: 'per_couple'); ?>"></p>
                <p><label><strong>Rating</strong></label><input class="widefat" type="number" step="0.1" name="hm_package[rating]" value="<?php echo esc_attr((string) $meta['rating']); ?>"></p>
                <p><label><strong>Review Count</strong></label><input class="widefat" type="number" name="hm_package[review_count]" value="<?php echo esc_attr((string) $meta['review_count']); ?>"></p>
            </div>
        </div>

        <?php self::render_repeater('Pricing Tiers', 'hm_pricing_tiers', $pricing_tiers, ['tier_id','tier_name','tier_price','tier_basis','tier_label','tier_description']); ?>
        <?php self::render_repeater('Inclusions', 'hm_inclusions', $inclusions, ['category','title','description']); ?>
        <?php self::render_repeater('Exclusions', 'hm_exclusions', $exclusions, ['title','description']); ?>
        <?php self::render_repeater('Departure Dates', 'hm_departures', $departures, ['departure_id','departure_date','availability','note']); ?>
        <?php self::render_repeater('Itinerary', 'hm_itinerary', $itinerary, ['day_number','day_title','day_summary','day_description','day_image','highlight_tag']); ?>

        <div class="hm-section">
            <h2>SEO</h2>
            <p><label><strong>SEO Title</strong></label><input class="widefat" type="text" name="hm_package[seo_title]" value="<?php echo esc_attr($meta['seo_title']); ?>"></p>
            <p><label><strong>Meta Description</strong></label><textarea class="widefat" rows="4" name="hm_package[meta_description]"><?php echo esc_textarea($meta['meta_description']); ?></textarea></p>
            <p><label><strong>Canonical URL</strong></label><input class="widefat" type="url" name="hm_package[canonical_url]" value="<?php echo esc_attr($meta['canonical_url']); ?>"></p>
        </div>
        <?php
    }

    private static function render_repeater(string $title, string $name, array $rows, array $fields): void {
        ?>
        <div class="hm-section hm-repeater" data-name="<?php echo esc_attr($name); ?>">
            <div class="hm-repeater-header"><h2><?php echo esc_html($title); ?></h2><button type="button" class="button hm-add-row">Add Row</button></div>
            <div class="hm-repeater-rows"><?php foreach ($rows as $index => $row) : self::render_row($name, $index, $row, $fields); endforeach; ?></div>
            <template><?php self::render_row($name, '__index__', [], $fields); ?></template>
        </div>
        <?php
    }

    private static function render_row(string $name, $index, array $row, array $fields): void {
        ?>
        <div class="hm-row">
            <div class="hm-grid hm-grid-3">
                <?php foreach ($fields as $field) : ?>
                    <p>
                        <label><?php echo esc_html(ucwords(str_replace('_', ' ', $field))); ?></label>
                        <?php if (in_array($field, ['day_description', 'tier_description', 'description'], true)) : ?>
                            <textarea class="widefat" rows="3" name="<?php echo esc_attr($name); ?>[<?php echo esc_attr((string) $index); ?>][<?php echo esc_attr($field); ?>]"><?php echo esc_textarea($row[$field] ?? ''); ?></textarea>
                        <?php else : ?>
                            <input class="widefat" type="text" name="<?php echo esc_attr($name); ?>[<?php echo esc_attr((string) $index); ?>][<?php echo esc_attr($field); ?>]" value="<?php echo esc_attr($row[$field] ?? ''); ?>">
                        <?php endif; ?>
                    </p>
                <?php endforeach; ?>
            </div>
            <button type="button" class="button-link-delete hm-remove-row">Remove</button>
        </div>
        <?php
    }

    public static function save(int $post_id): void {
        if (!isset($_POST['hm_package_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['hm_package_nonce'])), 'hm_package_save')) return;
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
        if (!current_user_can('edit_post', $post_id)) return;

        $data = $_POST['hm_package'] ?? [];
        foreach (['package_id','category','subtitle','summary','intro_content','currency','pricing_basis','seo_title','meta_description'] as $field) {
            update_post_meta($post_id, $field, sanitize_textarea_field(wp_unslash($data[$field] ?? '')));
        }
        foreach (['destination_id','days','nights','review_count'] as $field) {
            update_post_meta($post_id, $field, absint($data[$field] ?? 0));
        }
        foreach (['starting_price','rating'] as $field) {
            update_post_meta($post_id, $field, is_numeric($data[$field] ?? null) ? (float) $data[$field] : 0);
        }
        update_post_meta($post_id, 'canonical_url', esc_url_raw(wp_unslash($data['canonical_url'] ?? '')));

        foreach (['hm_pricing_tiers' => 'pricing_tiers', 'hm_inclusions' => 'inclusions', 'hm_exclusions' => 'exclusions', 'hm_departures' => 'departures', 'hm_itinerary' => 'itinerary'] as $source => $meta_key) {
            $rows = $_POST[$source] ?? [];
            $clean = [];
            if (is_array($rows)) {
                foreach ($rows as $row) {
                    $san = [];
                    $has = false;
                    foreach ((array) $row as $key => $value) {
                        $san[$key] = sanitize_textarea_field(wp_unslash($value));
                        if ($san[$key] !== '') $has = true;
                    }
                    if ($has) $clean[] = $san;
                }
            }
            update_post_meta($post_id, $meta_key, $clean);
        }
    }
}
