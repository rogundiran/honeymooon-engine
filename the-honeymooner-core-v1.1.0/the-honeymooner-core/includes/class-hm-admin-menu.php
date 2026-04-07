<?php
if (!defined('ABSPATH')) {
    exit;
}

class HM_Admin_Menu {
    public static function init(): void {
        add_action('admin_menu', [self::class, 'register_menu']);
    }

    public static function register_menu(): void {
        add_menu_page('Honeymooner', 'Honeymooner', 'edit_packages', 'hm_dashboard', [self::class, 'render_dashboard'], 'dashicons-heart', 26);
        add_submenu_page('hm_dashboard', 'Dashboard', 'Dashboard', 'edit_packages', 'hm_dashboard', [self::class, 'render_dashboard']);
        add_submenu_page('hm_dashboard', 'Destinations', 'Destinations', 'edit_destinations', 'edit.php?post_type=destinations');
        add_submenu_page('hm_dashboard', 'Packages', 'Packages', 'edit_packages', 'edit.php?post_type=packages');
        add_submenu_page('hm_dashboard', 'Leads', 'Leads', 'view_hm_leads', 'hm_leads', [HM_Leads_Admin::class, 'render_page']);
    }

    public static function render_dashboard(): void {
        global $wpdb;
        $leads = (int) $wpdb->get_var('SELECT COUNT(*) FROM ' . HM_Leads_DB::table_name());
        $packages = wp_count_posts('packages');
        $destinations = wp_count_posts('destinations');
        ?>
        <div class="wrap hm-dashboard">
            <h1>The Honeymooner Dashboard</h1>
            <div class="hm-cards">
                <div class="hm-card"><h2>Destinations</h2><p><?php echo esc_html((string) ($destinations->publish ?? 0)); ?></p></div>
                <div class="hm-card"><h2>Packages</h2><p><?php echo esc_html((string) ($packages->publish ?? 0)); ?></p></div>
                <div class="hm-card"><h2>Leads</h2><p><?php echo esc_html((string) $leads); ?></p></div>
            </div>
        </div>
        <?php
    }
}
