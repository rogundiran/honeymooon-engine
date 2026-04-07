<?php
/**
 * Plugin Name: The Honeymooner Core
 * Description: Core CMS plugin for The Honeymooner. Manages Destinations, Packages, Leads, admin UI, and REST integration for Next.js.
 * Version: 1.2.0
 * Author: Avario Digitals (Ralphmore)
 * Text Domain: the-honeymooner-core
 */

if (!defined('ABSPATH')) {
    exit;
}

define('HM_CORE_VERSION', '1.2.0');
define('HM_CORE_FILE', __FILE__);
define('HM_CORE_PATH', plugin_dir_path(__FILE__));
define('HM_CORE_URL', plugin_dir_url(__FILE__));

require_once HM_CORE_PATH . 'includes/helpers.php';
require_once HM_CORE_PATH . 'includes/class-hm-capabilities.php';
require_once HM_CORE_PATH . 'includes/class-hm-leads-db.php';
require_once HM_CORE_PATH . 'includes/class-hm-post-types.php';
require_once HM_CORE_PATH . 'includes/class-hm-destination-meta.php';
require_once HM_CORE_PATH . 'includes/class-hm-package-meta.php';
require_once HM_CORE_PATH . 'includes/class-hm-leads-admin.php';
require_once HM_CORE_PATH . 'includes/class-hm-rest.php';
require_once HM_CORE_PATH . 'includes/class-hm-admin-menu.php';
require_once HM_CORE_PATH . 'includes/class-hm-activator.php';

register_activation_hook(__FILE__, ['HM_Activator', 'activate']);
register_deactivation_hook(__FILE__, ['HM_Activator', 'deactivate']);

add_action('plugins_loaded', function () {
    HM_Post_Types::init();
    HM_Destination_Meta::init();
    HM_Package_Meta::init();
    HM_Leads_Admin::init();
    HM_REST::init();
    HM_Admin_Menu::init();

    add_action('admin_enqueue_scripts', function ($hook) {
        if (strpos((string) $hook, 'hm_') !== false || in_array($hook, ['post.php', 'post-new.php'], true)) {
            wp_enqueue_style('hm-core-admin', HM_CORE_URL . 'assets/css/admin.css', [], HM_CORE_VERSION);
            wp_enqueue_script('hm-core-admin', HM_CORE_URL . 'assets/js/admin.js', ['jquery'], HM_CORE_VERSION, true);
        }
    });
});
