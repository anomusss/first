<?php

if (!defined('ABSPATH')) {
    exit;
}

class PPM_Email
{
    public static function send_invite($email, $token)
    {
        $page_id = intval(get_option('ppm_portal_page_id'));
        $base = $page_id ? get_permalink($page_id) : home_url('/');
        $link = add_query_arg(['ppm_invite' => $token], $base);
        $subject = 'Zaproszenie do portalu najemcy';
        $message = "Witaj!\n\nZostałeś(aś) zaproszony(a) do portalu najemcy.\n\nKliknij link, aby ustawić hasło: $link\n\nLink jest ważny przez " . PPM_INVITE_TTL_DAYS . " dni.";
        return wp_mail($email, $subject, $message);
    }

    public static function send_payment_created($tenant_email, $admin_email, $details)
    {
        $subject = 'Nowe opłaty w portalu';
        $message = "Dodano nowe opłaty: {$details}. Zaloguj się do portalu, aby je zobaczyć.";
        wp_mail($tenant_email, $subject, $message);
        wp_mail($admin_email, $subject, $message);
    }

    public static function send_payment_marked($admin_email, $details)
    {
        $subject = 'Najemca oznaczył płatność jako opłaconą';
        $message = "Najemca oznaczył płatność jako opłaconą: {$details}.";
        wp_mail($admin_email, $subject, $message);
    }

    public static function send_payment_confirmed($tenant_email, $details)
    {
        $subject = 'Płatność potwierdzona';
        $message = "Płatność została potwierdzona: {$details}.";
        wp_mail($tenant_email, $subject, $message);
    }

    public static function send_payment_rejected($tenant_email, $details, $reason)
    {
        $subject = 'Płatność odrzucona';
        $message = "Płatność została odrzucona: {$details}. Powód: {$reason}.";
        wp_mail($tenant_email, $subject, $message);
    }

    public static function send_overdue_notice($tenant_email, $details)
    {
        $subject = 'Zaległa płatność';
        $message = "Masz zaległą płatność: {$details}.";
        wp_mail($tenant_email, $subject, $message);
    }
}
