<?php

if (!defined('ABSPATH')) {
    exit;
}

class PPM_Install
{
    public static function activate()
    {
        self::register_roles();
        self::install_tables();
        PPM_Cron::schedule_events();
    }

    public static function deactivate()
    {
        wp_clear_scheduled_hook('ppm_daily_cron');
    }

    public static function register_roles()
    {
        add_role(PPM_ROLE_TENANT, 'Najemca', [
            'read' => true,
        ]);
    }

    public static function install_tables()
    {
        global $wpdb;
        $charset = $wpdb->get_charset_collate();

        $tables = [];
        $tables[] = "CREATE TABLE {$wpdb->prefix}ppm_apartments (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            name VARCHAR(191) NOT NULL,
            address VARCHAR(191) NOT NULL,
            city VARCHAR(191) NOT NULL,
            notes TEXT NULL,
            status VARCHAR(20) NOT NULL DEFAULT 'vacant',
            start_date DATE NULL,
            end_date DATE NULL,
            archived TINYINT(1) NOT NULL DEFAULT 0,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            PRIMARY KEY (id)
        ) $charset;";

        $tables[] = "CREATE TABLE {$wpdb->prefix}ppm_tenants (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id BIGINT UNSIGNED NOT NULL,
            apartment_id BIGINT UNSIGNED NOT NULL,
            start_date DATE NULL,
            end_date DATE NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            PRIMARY KEY (id),
            KEY user_id (user_id),
            KEY apartment_id (apartment_id)
        ) $charset;";

        $tables[] = "CREATE TABLE {$wpdb->prefix}ppm_invites (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            email VARCHAR(191) NOT NULL,
            token_hash VARCHAR(255) NOT NULL,
            apartment_id BIGINT UNSIGNED NULL,
            expires_at DATETIME NOT NULL,
            used_at DATETIME NULL,
            created_at DATETIME NOT NULL,
            PRIMARY KEY (id),
            KEY email (email)
        ) $charset;";

        $tables[] = "CREATE TABLE {$wpdb->prefix}ppm_recurring_templates (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            apartment_id BIGINT UNSIGNED NOT NULL,
            type VARCHAR(50) NOT NULL,
            amount DECIMAL(12,2) NOT NULL,
            currency VARCHAR(10) NOT NULL,
            start_month CHAR(7) NOT NULL,
            end_month CHAR(7) NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            PRIMARY KEY (id),
            KEY apartment_id (apartment_id)
        ) $charset;";

        $tables[] = "CREATE TABLE {$wpdb->prefix}ppm_payments (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            apartment_id BIGINT UNSIGNED NOT NULL,
            tenant_id BIGINT UNSIGNED NULL,
            type VARCHAR(50) NOT NULL,
            period CHAR(7) NOT NULL,
            created_at DATETIME NOT NULL,
            due_date DATE NOT NULL,
            amount DECIMAL(12,2) NOT NULL,
            currency VARCHAR(10) NOT NULL,
            status VARCHAR(50) NOT NULL DEFAULT 'pending',
            admin_only TINYINT(1) NOT NULL DEFAULT 0,
            note TEXT NULL,
            proof_file VARCHAR(255) NULL,
            updated_at DATETIME NOT NULL,
            PRIMARY KEY (id),
            KEY apartment_id (apartment_id),
            KEY tenant_id (tenant_id),
            KEY period (period)
        ) $charset;";

        $tables[] = "CREATE TABLE {$wpdb->prefix}ppm_payment_logs (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            payment_id BIGINT UNSIGNED NOT NULL,
            action VARCHAR(100) NOT NULL,
            note TEXT NULL,
            created_at DATETIME NOT NULL,
            user_id BIGINT UNSIGNED NULL,
            PRIMARY KEY (id),
            KEY payment_id (payment_id)
        ) $charset;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        foreach ($tables as $sql) {
            dbDelta($sql);
        }
    }
}
