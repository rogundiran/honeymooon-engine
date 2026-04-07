<?php
if (!defined('ABSPATH')) {
    exit;
}

class HM_Destination_Meta {
    public static function init(): void {
        add_action('add_meta_boxes', [self::class, 'add_meta_boxes']);
        add_action('save_post_destinations', [self::class, 'save']);
    }

    public static function add_meta_boxes(): void {
        add_meta_box('hm_destination_details', 'Destination Details', [self::class, 'render'], 'destinations', 'normal', 'high');
    }

    public static function render(WP_Post $post): void {
        wp_nonce_field('hm_destination_save', 'hm_destination_nonce');
        $meta = [
            'subtitle' => get_post_meta($post->ID, 'subtitle', true),
            'intro_content' => get_post_meta($post->ID, 'intro_content', true),
            'best_time_to_visit' => get_post_meta($post->ID, 'best_time_to_visit', true),
            'seo_title' => get_post_meta($post->ID, 'seo_title', true),
            'meta_description' => get_post_meta($post->ID, 'meta_description', true),
            'canonical_url' => get_post_meta($post->ID, 'canonical_url', true),
        ];
        $highlights = hm_get_array_meta($post->ID, 'highlights');
        ?>
        <div class="hm-grid hm-grid-2">
            <p><label><strong>Subtitle</strong></label><input type="text" class="widefat" name="hm_destination[subtitle]" value="<?php echo esc_attr($meta['subtitle']); ?>"></p>
            <p><label><strong>Best Time to Visit</strong></label><input type="text" class="widefat" name="hm_destination[best_time_to_visit]" value="<?php echo esc_attr($meta['best_time_to_visit']); ?>"></p>
        </div>
        <p><label><strong>Intro Content</strong></label><textarea class="widefat" rows="5" name="hm_destination[intro_content]"><?php echo esc_textarea($meta['intro_content']); ?></textarea></p>
        <div class="hm-repeater" data-name="hm_destination_highlights">
            <div class="hm-repeater-header"><h3>Highlights</h3><button type="button" class="button hm-add-row">Add Highlight</button></div>
            <div class="hm-repeater-rows">
                <?php foreach ($highlights as $index => $row) : self::highlight_row($index, $row); endforeach; ?>
            </div>
            <template><?php self::highlight_row('__index__', ['title' => '', 'description' => '']); ?></template>
        </div>
        <h3>SEO</h3>
        <p><label><strong>SEO Title</strong></label><input type="text" class="widefat" name="hm_destination[seo_title]" value="<?php echo esc_attr($meta['seo_title']); ?>"></p>
        <p><label><strong>Meta Description</strong></label><textarea class="widefat" rows="4" name="hm_destination[meta_description]"><?php echo esc_textarea($meta['meta_description']); ?></textarea></p>
        <p><label><strong>Canonical URL</strong></label><input type="url" class="widefat" name="hm_destination[canonical_url]" value="<?php echo esc_attr($meta['canonical_url']); ?>"></p>
        <?php
    }

    private static function highlight_row($index, array $row): void {
        ?>
        <div class="hm-row">
            <div class="hm-grid hm-grid-2">
                <p><label>Title</label><input type="text" class="widefat" name="hm_destination_highlights[<?php echo esc_attr((string) $index); ?>][title]" value="<?php echo esc_attr($row['title'] ?? ''); ?>"></p>
                <p><label>Description</label><input type="text" class="widefat" name="hm_destination_highlights[<?php echo esc_attr((string) $index); ?>][description]" value="<?php echo esc_attr($row['description'] ?? ''); ?>"></p>
            </div>
            <button type="button" class="button-link-delete hm-remove-row">Remove</button>
        </div>
        <?php
    }

    public static function save(int $post_id): void {
        if (!isset($_POST['hm_destination_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['hm_destination_nonce'])), 'hm_destination_save')) return;
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
        if (!current_user_can('edit_post', $post_id)) return;

        $data = $_POST['hm_destination'] ?? [];
        foreach (['subtitle','intro_content','best_time_to_visit','seo_title','meta_description'] as $field) {
            update_post_meta($post_id, $field, sanitize_textarea_field(wp_unslash($data[$field] ?? '')));
        }
        update_post_meta($post_id, 'canonical_url', esc_url_raw(wp_unslash($data['canonical_url'] ?? '')));

        $rows = $_POST['hm_destination_highlights'] ?? [];
        $clean = [];
        if (is_array($rows)) {
            foreach ($rows as $row) {
                $title = sanitize_text_field($row['title'] ?? '');
                $description = sanitize_text_field($row['description'] ?? '');
                if ($title !== '' || $description !== '') $clean[] = ['title' => $title, 'description' => $description];
            }
        }
        update_post_meta($post_id, 'highlights', $clean);
    }
}
