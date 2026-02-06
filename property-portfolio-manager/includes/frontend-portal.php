<?php

if (!defined('ABSPATH')) {
    exit;
}

add_shortcode('property_portal', 'ppm_render_portal');

add_action('wp_enqueue_scripts', function () {
    wp_enqueue_style('ppm_portal', PPM_PLUGIN_URL . 'assets/portal.css', [], PPM_VERSION);
});

add_action('admin_post_nopriv_ppm_portal_login', 'ppm_handle_portal_login');
add_action('admin_post_ppm_portal_login', 'ppm_handle_portal_login');
add_action('admin_post_nopriv_ppm_accept_invite', 'ppm_handle_accept_invite');
add_action('admin_post_ppm_accept_invite', 'ppm_handle_accept_invite');
add_action('admin_post_ppm_mark_paid', 'ppm_handle_mark_paid');

function ppm_render_portal()
{
    $token = isset($_GET['ppm_invite']) ? sanitize_text_field($_GET['ppm_invite']) : null;
    if ($token) {
        return ppm_render_invite_form($token);
    }

    if (!is_user_logged_in()) {
        return ppm_render_login_form();
    }

    $user = wp_get_current_user();
    if (!in_array(PPM_ROLE_TENANT, $user->roles, true) && !current_user_can('manage_options')) {
        return '<p>Brak dostępu.</p>';
    }

    $payments = PPM_DB::get_payments([
        'tenant_id' => $user->ID,
    ]);
    ob_start();
    ?>
    <div class="ppm-portal">
        <h2>Twoje płatności</h2>
        <table class="ppm-table">
            <thead>
            <tr>
                <th>Typ</th>
                <th>Okres</th>
                <th>Kwota</th>
                <th>Termin</th>
                <th>Status</th>
                <th>Akcja</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($payments as $payment) : ?>
                <?php if ($payment->admin_only) : continue; endif; ?>
                <tr>
                    <td><?php echo esc_html($payment->type); ?></td>
                    <td><?php echo esc_html($payment->period); ?></td>
                    <td><?php echo esc_html(number_format($payment->amount, 2)); ?> <?php echo esc_html($payment->currency); ?></td>
                    <td><?php echo esc_html($payment->due_date); ?></td>
                    <td><?php echo esc_html($payment->status); ?></td>
                    <td>
                        <?php if ($payment->status === 'pending' || $payment->status === 'overdue') : ?>
                            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" enctype="multipart/form-data">
                                <?php wp_nonce_field('ppm_mark_paid_' . $payment->id); ?>
                                <input type="hidden" name="action" value="ppm_mark_paid">
                                <input type="hidden" name="payment_id" value="<?php echo esc_attr($payment->id); ?>">
                                <input type="file" name="proof" required>
                                <input type="text" name="note" placeholder="Notatka opcjonalna">
                                <button class="ppm-button" type="submit">Oznacz jako opłacone</button>
                            </form>
                        <?php else : ?>
                            <span>—</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php
    return ob_get_clean();
}

function ppm_render_login_form()
{
    ob_start();
    ?>
    <div class="ppm-portal">
        <h2>Logowanie najemcy</h2>
        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
            <?php wp_nonce_field('ppm_portal_login'); ?>
            <input type="hidden" name="action" value="ppm_portal_login">
            <label>Email <input type="email" name="log" required></label>
            <label>Hasło <input type="password" name="pwd" required></label>
            <button class="ppm-button" type="submit">Zaloguj</button>
        </form>
    </div>
    <?php
    return ob_get_clean();
}

function ppm_handle_portal_login()
{
    check_admin_referer('ppm_portal_login');
    $ip = sanitize_text_field($_SERVER['REMOTE_ADDR'] ?? '');
    $key = PPM_LOGIN_RATE_LIMIT_KEY . '_' . md5($ip);
    $attempts = intval(get_transient($key));
    if ($attempts >= 5) {
        wp_die('Zbyt wiele prób. Spróbuj ponownie później.');
    }

    $creds = [
        'user_login' => sanitize_text_field($_POST['log']),
        'user_password' => $_POST['pwd'],
        'remember' => true,
    ];
    $user = wp_signon($creds, false);
    if (is_wp_error($user)) {
        set_transient($key, $attempts + 1, 15 * MINUTE_IN_SECONDS);
        wp_die('Nieprawidłowe dane logowania.');
    }
    delete_transient($key);
    wp_redirect(wp_get_referer() ?: home_url('/'));
    exit;
}

function ppm_render_invite_form($token)
{
    ob_start();
    ?>
    <div class="ppm-portal">
        <h2>Aktywacja konta</h2>
        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
            <?php wp_nonce_field('ppm_accept_invite'); ?>
            <input type="hidden" name="action" value="ppm_accept_invite">
            <input type="hidden" name="token" value="<?php echo esc_attr($token); ?>">
            <label>Imię i nazwisko <input type="text" name="display_name" required></label>
            <label>Hasło <input type="password" name="password" required></label>
            <button class="ppm-button" type="submit">Aktywuj</button>
        </form>
    </div>
    <?php
    return ob_get_clean();
}

function ppm_handle_accept_invite()
{
    check_admin_referer('ppm_accept_invite');
    $token = sanitize_text_field($_POST['token']);
    $invite = PPM_DB::get_invite_by_token($token);
    if (!$invite) {
        wp_die('Nieprawidłowe lub wygasłe zaproszenie.');
    }

    $email = sanitize_email($invite->email);
    $password = $_POST['password'];
    $display_name = sanitize_text_field($_POST['display_name']);

    $user = get_user_by('email', $email);
    if (!$user) {
        $user_id = wp_create_user($email, $password, $email);
        if (is_wp_error($user_id)) {
            wp_die('Nie udało się utworzyć konta.');
        }
        wp_update_user([
            'ID' => $user_id,
            'display_name' => $display_name,
        ]);
        $user = get_user_by('id', $user_id);
    } else {
        wp_set_password($password, $user->ID);
        wp_update_user([
            'ID' => $user->ID,
            'display_name' => $display_name,
        ]);
    }

    $user->set_role(PPM_ROLE_TENANT);
    if ($invite->apartment_id) {
        PPM_DB::assign_tenant($user->ID, $invite->apartment_id, current_time('Y-m-d'));
    }

    PPM_DB::mark_invite_used($invite->id);
    wp_set_current_user($user->ID);
    wp_set_auth_cookie($user->ID);

    wp_redirect(home_url('/'));
    exit;
}

function ppm_handle_mark_paid()
{
    if (!is_user_logged_in()) {
        wp_die('Brak dostępu');
    }
    $payment_id = intval($_POST['payment_id']);
    check_admin_referer('ppm_mark_paid_' . $payment_id);

    $payment = PPM_DB::get_payment($payment_id);
    if (!$payment) {
        wp_die('Nie znaleziono płatności');
    }

    $user_id = get_current_user_id();
    if (intval($payment->tenant_id) !== $user_id) {
        wp_die('Brak dostępu');
    }

    if (empty($_FILES['proof']['name'])) {
        wp_die('Wymagany dowód płatności.');
    }

    $upload = PPM_Uploads::handle_upload($_FILES['proof']);
    if (is_wp_error($upload)) {
        wp_die($upload->get_error_message());
    }

    $note = sanitize_textarea_field($_POST['note'] ?? '');
    PPM_DB::update_payment($payment_id, [
        'status' => 'marked_paid_by_tenant',
        'proof_file' => $upload,
        'note' => $note,
    ]);
    PPM_DB::add_payment_log($payment_id, 'marked_paid_by_tenant', $note, $user_id);

    $admin_email = get_option('admin_email');
    $details = "{$payment->type} {$payment->period} kwota {$payment->amount} {$payment->currency}";
    PPM_Email::send_payment_marked($admin_email, $details);

    wp_redirect(wp_get_referer() ?: home_url('/'));
    exit;
}
