<?php
if (!defined('ABSPATH')) {
    exit;
}

class HM_Capabilities {
    public static function register_roles(): void {
        $caps = [
            'read' => true,
            'edit_destination' => true,
            'read_destination' => true,
            'delete_destination' => true,
            'edit_destinations' => true,
            'edit_others_destinations' => true,
            'publish_destinations' => true,
            'read_private_destinations' => true,
            'delete_destinations' => true,
            'delete_private_destinations' => true,
            'delete_published_destinations' => true,
            'delete_others_destinations' => true,
            'edit_private_destinations' => true,
            'edit_published_destinations' => true,
            'create_destinations' => true,

            'edit_package' => true,
            'read_package' => true,
            'delete_package' => true,
            'edit_packages' => true,
            'edit_others_packages' => true,
            'publish_packages' => true,
            'read_private_packages' => true,
            'delete_packages' => true,
            'delete_private_packages' => true,
            'delete_published_packages' => true,
            'delete_others_packages' => true,
            'edit_private_packages' => true,
            'edit_published_packages' => true,
            'create_packages' => true,

            'view_hm_leads' => true,
            'edit_hm_leads' => true,
            'assign_hm_leads' => true,
            'export_hm_leads' => true,
        ];

        add_role('honeymoon_manager', 'Honeymoon Manager', $caps);

        foreach (['administrator', 'shop_manager', 'editor'] as $role_name) {
            $role = get_role($role_name);
            if ($role) {
                foreach ($caps as $cap => $grant) {
                    $role->add_cap($cap, $grant);
                }
            }
        }
    }
}
