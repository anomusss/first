<?php

if (!defined('ABSPATH')) {
    exit;
}

class PPM_Cron
{
    public static function schedule_events()
    {
        if (!wp_next_scheduled('ppm_daily_cron')) {
            wp_schedule_event(time(), 'daily', 'ppm_daily_cron');
        }
    }

    public static function run_daily()
    {
        self::generate_monthly_payments();
        self::mark_overdue();
        self::send_reminders();
    }

    private static function generate_monthly_payments()
    {
        global $wpdb;
        $month = gmdate('Y-m');
        $first_day = $month . '-01';

        $templates = PPM_DB::get_recurring_templates();
        foreach ($templates as $template) {
            if ($template->start_month > $month) {
                continue;
            }
            if ($template->end_month && $template->end_month < $month) {
                continue;
            }

            $exists = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM {$wpdb->prefix}ppm_payments WHERE apartment_id = %d AND type = %s AND period = %s",
                $template->apartment_id,
                $template->type,
                $month
            ));
            if ($exists) {
                continue;
            }

            $tenant_id = $wpdb->get_var($wpdb->prepare(
                "SELECT user_id FROM {$wpdb->prefix}ppm_tenants WHERE apartment_id = %d ORDER BY id DESC LIMIT 1",
                $template->apartment_id
            ));
            $payment_id = PPM_DB::create_payment([
                'apartment_id' => $template->apartment_id,
                'tenant_id' => $tenant_id,
                'type' => $template->type,
                'period' => $month,
                'due_date' => date('Y-m-d', strtotime($first_day . ' +10 days')),
                'amount' => $template->amount,
                'currency' => $template->currency,
                'admin_only' => in_array($template->type, ['admin_only_tax', 'admin_only_other'], true),
            ]);

            if ($tenant_id) {
                $user = get_user_by('id', $tenant_id);
                $admin_email = get_option('admin_email');
                $details = "{$template->type} za {$month}, kwota {$template->amount} {$template->currency}";
                PPM_Email::send_payment_created($user->user_email, $admin_email, $details);
            }
        }
    }

    private static function mark_overdue()
    {
        global $wpdb;
        $wpdb->query("UPDATE {$wpdb->prefix}ppm_payments SET status = 'overdue' WHERE status = 'pending' AND due_date < CURDATE()");
    }

    private static function send_reminders()
    {
        global $wpdb;
        $due_soon = $wpdb->get_results("SELECT p.*, u.user_email FROM {$wpdb->prefix}ppm_payments p LEFT JOIN {$wpdb->users} u ON p.tenant_id = u.ID WHERE p.status = 'pending' AND p.due_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 3 DAY) AND p.admin_only = 0");
        foreach ($due_soon as $payment) {
            if (!$payment->tenant_id || !$payment->user_email) {
                continue;
            }
            $details = "{$payment->type} {$payment->period}, kwota {$payment->amount} {$payment->currency}";
            PPM_Email::send_payment_created($payment->user_email, get_option('admin_email'), $details);
        }

        $overdue = $wpdb->get_results("SELECT p.*, u.user_email FROM {$wpdb->prefix}ppm_payments p LEFT JOIN {$wpdb->users} u ON p.tenant_id = u.ID WHERE p.status = 'overdue' AND p.admin_only = 0");
        foreach ($overdue as $payment) {
            if (!$payment->tenant_id || !$payment->user_email) {
                continue;
            }
            $details = "{$payment->type} {$payment->period}, kwota {$payment->amount} {$payment->currency}";
            PPM_Email::send_overdue_notice($payment->user_email, $details);
        }
    }
}

add_action('ppm_daily_cron', function () {
    PPM_Cron::run_daily();
});
