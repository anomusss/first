<?php
/**
 * Plugin Name: Property Portfolio Manager
 * Description: Simple property portfolio management for private landlords.
 * Version: 1.0.0
 * Author: Codex
 */

if (!defined('ABSPATH')) {
    exit;
}

define('PPM_VERSION', '1.0.0');
define('PPM_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('PPM_PLUGIN_URL', plugin_dir_url(__FILE__));

define('PPM_UPLOAD_SUBDIR', 'ppm_uploads');

define('PPM_ROLE_TENANT', 'ppm_tenant');

define('PPM_CURRENCY', 'PLN');

define('PPM_LOGIN_RATE_LIMIT_KEY', 'ppm_login_rate_limit');

define('PPM_INVITE_TTL_DAYS', 7);

define('PPM_MAX_UPLOAD_MB', 8);

require_once PPM_PLUGIN_DIR . 'includes/class-install.php';
require_once PPM_PLUGIN_DIR . 'includes/class-db.php';
require_once PPM_PLUGIN_DIR . 'includes/class-email.php';
require_once PPM_PLUGIN_DIR . 'includes/class-uploads.php';
require_once PPM_PLUGIN_DIR . 'includes/class-cron.php';
require_once PPM_PLUGIN_DIR . 'includes/admin-pages.php';
require_once PPM_PLUGIN_DIR . 'includes/frontend-portal.php';
require_once PPM_PLUGIN_DIR . 'includes/reports.php';

register_activation_hook(__FILE__, ['PPM_Install', 'activate']);
register_deactivation_hook(__FILE__, ['PPM_Install', 'deactivate']);

add_action('init', function () {
    PPM_Install::register_roles();
});

add_action('plugins_loaded', function () {
    PPM_DB::maybe_upgrade();
});

add_action('init', function () {
    if (isset($_GET['ppm_download_proof'])) {
        PPM_Uploads::handle_proof_download();
    }
});
