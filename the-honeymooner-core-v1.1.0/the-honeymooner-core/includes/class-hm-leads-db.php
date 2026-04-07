<?php
if (!defined('ABSPATH')) {
    exit;
}

class HM_Leads_DB {
    public static function table_name(): string {
        global $wpdb;
        return $wpdb->prefix . 'hm_leads';
    }

    public static function create_table(): void {
        global $wpdb;
        $table = self::table_name();
        $charset_collate = $wpdb->get_charset_collate();
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        $sql = "CREATE TABLE {$table} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            package_id BIGINT UNSIGNED NULL,
            package_name VARCHAR(255) NOT NULL DEFAULT '',
            package_tier VARCHAR(100) NOT NULL DEFAULT '',
            departure_date DATE NULL,
            adults INT UNSIGNED NOT NULL DEFAULT 0,
            children INT UNSIGNED NOT NULL DEFAULT 0,
            traveler_name VARCHAR(255) NOT NULL DEFAULT '',
            email VARCHAR(255) NOT NULL DEFAULT '',
            phone VARCHAR(100) NOT NULL DEFAULT '',
            country_of_residence VARCHAR(150) NOT NULL DEFAULT '',
            occasion VARCHAR(100) NOT NULL DEFAULT '',
            message LONGTEXT NULL,
            status VARCHAR(50) NOT NULL DEFAULT 'new',
            source_url VARCHAR(500) NOT NULL DEFAULT '',
            assigned_user_id BIGINT UNSIGNED NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            PRIMARY KEY (id),
            KEY package_id (package_id),
            KEY status (status),
            KEY email (email),
            KEY created_at (created_at)
        ) {$charset_collate};";

        dbDelta($sql);
    }

    public static function insert(array $data): int {
        global $wpdb;
        $table = self::table_name();
        $now = current_time('mysql');
        $wpdb->insert(
            $table,
            [
                'package_id' => !empty($data['package_id']) ? absint($data['package_id']) : null,
                'package_name' => sanitize_text_field($data['package_name'] ?? ''),
                'package_tier' => sanitize_text_field($data['package_tier'] ?? ''),
                'departure_date' => !empty($data['departure_date']) ? sanitize_text_field($data['departure_date']) : null,
                'adults' => absint($data['adults'] ?? 0),
                'children' => absint($data['children'] ?? 0),
                'traveler_name' => sanitize_text_field($data['traveler_name'] ?? ''),
                'email' => sanitize_email($data['email'] ?? ''),
                'phone' => sanitize_text_field($data['phone'] ?? ''),
                'country_of_residence' => sanitize_text_field($data['country_of_residence'] ?? ''),
                'occasion' => sanitize_text_field($data['occasion'] ?? ''),
                'message' => sanitize_textarea_field($data['message'] ?? ''),
                'status' => sanitize_text_field($data['status'] ?? 'new'),
                'source_url' => esc_url_raw($data['source_url'] ?? ''),
                'assigned_user_id' => !empty($data['assigned_user_id']) ? absint($data['assigned_user_id']) : null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            ['%d','%s','%s','%s','%d','%d','%s','%s','%s','%s','%s','%s','%s','%d','%s','%s']
        );
        return (int) $wpdb->insert_id;
    }

    public static function update_status(int $lead_id, string $status): void {
        global $wpdb;
        $wpdb->update(
            self::table_name(),
            [
                'status' => sanitize_text_field($status),
                'updated_at' => current_time('mysql'),
            ],
            ['id' => $lead_id],
            ['%s', '%s'],
            ['%d']
        );
    }
}
