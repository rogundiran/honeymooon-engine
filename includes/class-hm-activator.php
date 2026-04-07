<?php
if (!defined('ABSPATH')) {
    exit;
}

class HM_Activator {
    public static function activate(): void {
        HM_Capabilities::register_roles();
        HM_Leads_DB::create_table();
        HM_Post_Types::register();
        flush_rewrite_rules();
    }

    public static function deactivate(): void {
        flush_rewrite_rules();
    }
}
