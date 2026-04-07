<?php
if (!defined('ABSPATH')) {
    exit;
}

class HM_Leads_Admin {
    public static function init(): void {
        add_action('admin_post_hm_update_lead_status', [self::class, 'handle_status_update']);
    }

    public static function render_page(): void {
        if (!current_user_can('view_hm_leads')) wp_die('You do not have permission to view leads.');
        global $wpdb;
        $table = HM_Leads_DB::table_name();
        $status = isset($_GET['status']) ? sanitize_text_field(wp_unslash($_GET['status'])) : '';
        $where = $status !== '' ? $wpdb->prepare(' WHERE status = %s', $status) : '';
        $rows = $wpdb->get_results("SELECT * FROM {$table}{$where} ORDER BY created_at DESC LIMIT 200", ARRAY_A);
        ?>
        <div class="wrap">
            <h1>Lead Enquiries</h1>
            <form method="get" style="margin-bottom:12px;">
                <input type="hidden" name="page" value="hm_leads">
                <select name="status"><option value="">All statuses</option><?php foreach (['new','contacted','qualified','proposal_sent','won','lost','spam'] as $s) : ?><option value="<?php echo esc_attr($s); ?>" <?php selected($status, $s); ?>><?php echo esc_html(ucwords(str_replace('_', ' ', $s))); ?></option><?php endforeach; ?></select>
                <button class="button">Filter</button>
            </form>
            <table class="widefat striped"><thead><tr><th>ID</th><th>Traveler</th><th>Email</th><th>Phone</th><th>Package</th><th>Tier</th><th>Departure</th><th>Adults</th><th>Children</th><th>Occasion</th><th>Status</th><th>Created</th><th>Action</th></tr></thead><tbody>
            <?php if ($rows) : foreach ($rows as $row) : ?>
                <tr>
                    <td><?php echo esc_html((string) $row['id']); ?></td>
                    <td><?php echo esc_html($row['traveler_name']); ?><br><small><?php echo esc_html($row['country_of_residence']); ?></small></td>
                    <td><?php echo esc_html($row['email']); ?></td>
                    <td><?php echo esc_html($row['phone']); ?></td>
                    <td><?php echo esc_html($row['package_name']); ?></td>
                    <td><?php echo esc_html($row['package_tier']); ?></td>
                    <td><?php echo esc_html($row['departure_date']); ?></td>
                    <td><?php echo esc_html((string) $row['adults']); ?></td>
                    <td><?php echo esc_html((string) $row['children']); ?></td>
                    <td><?php echo esc_html($row['occasion']); ?></td>
                    <td><strong><?php echo esc_html($row['status']); ?></strong><br><small><?php echo esc_html(wp_trim_words($row['message'], 12)); ?></small></td>
                    <td><?php echo esc_html($row['created_at']); ?></td>
                    <td><form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>"><?php wp_nonce_field('hm_update_lead_status_' . $row['id']); ?><input type="hidden" name="action" value="hm_update_lead_status"><input type="hidden" name="lead_id" value="<?php echo esc_attr((string) $row['id']); ?>"><select name="status"><?php foreach (['new','contacted','qualified','proposal_sent','won','lost','spam'] as $s) : ?><option value="<?php echo esc_attr($s); ?>" <?php selected($row['status'], $s); ?>><?php echo esc_html(ucwords(str_replace('_', ' ', $s))); ?></option><?php endforeach; ?></select><button class="button button-small">Save</button></form></td>
                </tr>
            <?php endforeach; else : ?><tr><td colspan="13">No leads found.</td></tr><?php endif; ?>
            </tbody></table>
        </div>
        <?php
    }

    public static function handle_status_update(): void {
        if (!current_user_can('edit_hm_leads')) wp_die('You do not have permission to edit leads.');
        $lead_id = absint($_POST['lead_id'] ?? 0);
        check_admin_referer('hm_update_lead_status_' . $lead_id);
        HM_Leads_DB::update_status($lead_id, sanitize_text_field(wp_unslash($_POST['status'] ?? 'new')));
        wp_safe_redirect(admin_url('admin.php?page=hm_leads&updated=1'));
        exit;
    }
}
