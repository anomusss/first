<?php

if (!defined('ABSPATH')) {
    exit;
}

add_action('admin_menu', function () {
    add_menu_page('Portfel nieruchomości', 'Nieruchomości', 'manage_options', 'ppm_dashboard', 'ppm_render_dashboard', 'dashicons-admin-home');
    add_submenu_page('ppm_dashboard', 'Apartamenty', 'Apartamenty', 'manage_options', 'ppm_apartments', 'ppm_render_apartments');
    add_submenu_page('ppm_dashboard', 'Najemcy', 'Najemcy', 'manage_options', 'ppm_tenants', 'ppm_render_tenants');
    add_submenu_page('ppm_dashboard', 'Płatności', 'Płatności', 'manage_options', 'ppm_payments', 'ppm_render_payments');
    add_submenu_page('ppm_dashboard', 'Raporty', 'Raporty', 'manage_options', 'ppm_reports', 'ppm_render_reports');
    add_submenu_page('ppm_dashboard', 'Ustawienia', 'Ustawienia', 'manage_options', 'ppm_settings', 'ppm_render_settings');
});

add_action('admin_enqueue_scripts', function ($hook) {
    if (strpos($hook, 'ppm_') !== false) {
        wp_enqueue_style('ppm_admin', PPM_PLUGIN_URL . 'assets/admin.css', [], PPM_VERSION);
    }
});

add_action('admin_post_ppm_save_apartment', 'ppm_handle_save_apartment');
add_action('admin_post_ppm_send_invite', 'ppm_handle_send_invite');
add_action('admin_post_ppm_save_recurring', 'ppm_handle_save_recurring');
add_action('admin_post_ppm_save_settings', 'ppm_handle_save_settings');
add_action('admin_post_ppm_create_payment', 'ppm_handle_create_payment');
add_action('admin_post_ppm_confirm_payment', 'ppm_handle_confirm_payment');
add_action('admin_post_ppm_reject_payment', 'ppm_handle_reject_payment');

function ppm_render_dashboard()
{
    $stats = PPM_DB::get_dashboard_stats();
    ?>
    <div class="wrap">
        <h1>Panel główny</h1>
        <div class="ppm-grid">
            <div class="ppm-card">Łącznie mieszkań: <strong><?php echo esc_html($stats['total_apartments']); ?></strong></div>
            <div class="ppm-card">Wynajęte: <strong><?php echo esc_html($stats['rented']); ?></strong></div>
            <div class="ppm-card">Wolne: <strong><?php echo esc_html($stats['vacant']); ?></strong></div>
            <div class="ppm-card">Płatności wkrótce: <strong><?php echo esc_html($stats['due_soon']); ?></strong></div>
            <div class="ppm-card">Zaległe: <strong><?php echo esc_html($stats['overdue']); ?></strong></div>
            <div class="ppm-card">Do potwierdzenia: <strong><?php echo esc_html($stats['waiting_confirmation']); ?></strong></div>
            <div class="ppm-card">Przychód (rent): <strong><?php echo esc_html(number_format($stats['month_income'], 2)); ?> <?php echo esc_html(PPM_CURRENCY); ?></strong></div>
            <div class="ppm-card">Czynsz HOA: <strong><?php echo esc_html(number_format($stats['month_hoa'], 2)); ?> <?php echo esc_html(PPM_CURRENCY); ?></strong></div>
            <div class="ppm-card">Media: <strong><?php echo esc_html(number_format($stats['month_utilities'], 2)); ?> <?php echo esc_html(PPM_CURRENCY); ?></strong></div>
        </div>
    </div>
    <?php
}

function ppm_render_apartments()
{
    $apartments = PPM_DB::get_apartments(true);
    ?>
    <div class="wrap">
        <h1>Apartamenty</h1>
        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="ppm-form">
            <?php wp_nonce_field('ppm_save_apartment'); ?>
            <input type="hidden" name="action" value="ppm_save_apartment">
            <h2>Dodaj / edytuj apartament</h2>
            <div class="ppm-grid">
                <label>Nazwa <input type="text" name="name" required></label>
                <label>Adres <input type="text" name="address" required></label>
                <label>Miasto <input type="text" name="city" required></label>
                <label>Status
                    <select name="status">
                        <option value="vacant">Wolne</option>
                        <option value="rented">Wynajęte</option>
                        <option value="notice">Wypowiedzenie</option>
                    </select>
                </label>
                <label>Data startu <input type="date" name="start_date"></label>
                <label>Data końca <input type="date" name="end_date"></label>
            </div>
            <label>Notatki
                <textarea name="notes" rows="3"></textarea>
            </label>
            <label><input type="checkbox" name="archived" value="1"> Zarchiwizowany</label>
            <button class="button button-primary" type="submit">Zapisz</button>
        </form>

        <h2>Lista</h2>
        <table class="widefat">
            <thead>
            <tr>
                <th>Nazwa</th>
                <th>Adres</th>
                <th>Miasto</th>
                <th>Status</th>
                <th>Start</th>
                <th>Koniec</th>
                <th>Archiwum</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($apartments as $apartment) : ?>
                <tr>
                    <td><?php echo esc_html($apartment->name); ?></td>
                    <td><?php echo esc_html($apartment->address); ?></td>
                    <td><?php echo esc_html($apartment->city); ?></td>
                    <td><?php echo esc_html($apartment->status); ?></td>
                    <td><?php echo esc_html($apartment->start_date); ?></td>
                    <td><?php echo esc_html($apartment->end_date); ?></td>
                    <td><?php echo $apartment->archived ? 'Tak' : 'Nie'; ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php
}

function ppm_handle_save_apartment()
{
    if (!current_user_can('manage_options')) {
        wp_die('Brak dostępu');
    }
    check_admin_referer('ppm_save_apartment');
    PPM_DB::upsert_apartment($_POST);
    wp_redirect(admin_url('admin.php?page=ppm_apartments'));
    exit;
}

function ppm_render_tenants()
{
    $apartments = PPM_DB::get_apartments();
    $tenants = PPM_DB::get_tenants();
    ?>
    <div class="wrap">
        <h1>Najemcy</h1>
        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="ppm-form">
            <?php wp_nonce_field('ppm_send_invite'); ?>
            <input type="hidden" name="action" value="ppm_send_invite">
            <h2>Zaproś najemcę</h2>
            <div class="ppm-grid">
                <label>Email <input type="email" name="email" required></label>
                <label>Apartament
                    <select name="apartment_id" required>
                        <option value="">Wybierz</option>
                        <?php foreach ($apartments as $apartment) : ?>
                            <option value="<?php echo esc_attr($apartment->id); ?>"><?php echo esc_html($apartment->name); ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
            </div>
            <button class="button button-primary" type="submit">Wyślij zaproszenie</button>
        </form>

        <h2>Lista</h2>
        <table class="widefat">
            <thead>
            <tr>
                <th>Najemca</th>
                <th>Email</th>
                <th>Apartament</th>
                <th>Start</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($tenants as $tenant) : ?>
                <tr>
                    <td><?php echo esc_html($tenant->display_name); ?></td>
                    <td><?php echo esc_html($tenant->user_email); ?></td>
                    <td><?php echo esc_html($tenant->apartment_name); ?></td>
                    <td><?php echo esc_html($tenant->start_date); ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php
}

function ppm_handle_send_invite()
{
    if (!current_user_can('manage_options')) {
        wp_die('Brak dostępu');
    }
    check_admin_referer('ppm_send_invite');
    $email = sanitize_email($_POST['email']);
    $apartment_id = intval($_POST['apartment_id']);
    [$token] = PPM_DB::create_invite($email, $apartment_id);
    PPM_Email::send_invite($email, $token);
    wp_redirect(admin_url('admin.php?page=ppm_tenants&invite=sent'));
    exit;
}

function ppm_render_payments()
{
    $payments = PPM_DB::get_payments();
    $apartments = PPM_DB::get_apartments();
    ?>
    <div class="wrap">
        <h1>Płatności</h1>
        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="ppm-form">
            <?php wp_nonce_field('ppm_create_payment'); ?>
            <input type="hidden" name="action" value="ppm_create_payment">
            <h2>Dodaj opłatę ad-hoc</h2>
            <div class="ppm-grid">
                <label>Apartament
                    <select name="apartment_id" required>
                        <option value="">Wybierz</option>
                        <?php foreach ($apartments as $apartment) : ?>
                            <option value="<?php echo esc_attr($apartment->id); ?>"><?php echo esc_html($apartment->name); ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label>Typ
                    <select name="type">
                        <option value="rent">Czynsz/odstępne</option>
                        <option value="hoa">Czynsz do wspólnoty</option>
                        <option value="utilities">Media</option>
                        <option value="other">Inne</option>
                        <option value="admin_only_tax">Podatki (tylko admin)</option>
                        <option value="admin_only_other">Inne (tylko admin)</option>
                    </select>
                </label>
                <label>Okres (YYYY-MM) <input type="text" name="period" required></label>
                <label>Kwota <input type="number" step="0.01" name="amount" required></label>
                <label>Termin płatności <input type="date" name="due_date" required></label>
                <label>Waluta <input type="text" name="currency" value="<?php echo esc_attr(PPM_CURRENCY); ?>" required></label>
            </div>
            <label>Notatka <textarea name="note" rows="2"></textarea></label>
            <button class="button button-primary" type="submit">Dodaj</button>
        </form>

        <h2>Lista</h2>
        <table class="widefat">
            <thead>
            <tr>
                <th>Apartament</th>
                <th>Najemca</th>
                <th>Typ</th>
                <th>Okres</th>
                <th>Kwota</th>
                <th>Termin</th>
                <th>Status</th>
                <th>Akcje</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($payments as $payment) : ?>
                <tr>
                    <td><?php echo esc_html($payment->apartment_name); ?></td>
                    <td><?php echo esc_html($payment->user_email); ?></td>
                    <td><?php echo esc_html($payment->type); ?></td>
                    <td><?php echo esc_html($payment->period); ?></td>
                    <td><?php echo esc_html(number_format($payment->amount, 2)); ?> <?php echo esc_html($payment->currency); ?></td>
                    <td><?php echo esc_html($payment->due_date); ?></td>
                    <td><?php echo esc_html($payment->status); ?></td>
                    <td>
                        <?php if ($payment->status === 'marked_paid_by_tenant') : ?>
                            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="ppm-inline">
                                <?php wp_nonce_field('ppm_confirm_payment'); ?>
                                <input type="hidden" name="action" value="ppm_confirm_payment">
                                <input type="hidden" name="payment_id" value="<?php echo esc_attr($payment->id); ?>">
                                <button class="button">Potwierdź</button>
                            </form>
                            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="ppm-inline">
                                <?php wp_nonce_field('ppm_reject_payment'); ?>
                                <input type="hidden" name="action" value="ppm_reject_payment">
                                <input type="hidden" name="payment_id" value="<?php echo esc_attr($payment->id); ?>">
                                <input type="text" name="reason" placeholder="Powód" required>
                                <button class="button">Odrzuć</button>
                            </form>
                        <?php endif; ?>
                        <?php if ($payment->proof_file) : ?>
                            <a class="button" href="<?php echo esc_url(add_query_arg(['ppm_download_proof' => $payment->id], admin_url('admin.php?page=ppm_payments'))); ?>">Pobierz dowód</a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php
}

function ppm_handle_create_payment()
{
    if (!current_user_can('manage_options')) {
        wp_die('Brak dostępu');
    }
    check_admin_referer('ppm_create_payment');
    $data = [
        'apartment_id' => intval($_POST['apartment_id']),
        'tenant_id' => null,
        'type' => sanitize_text_field($_POST['type']),
        'period' => sanitize_text_field($_POST['period']),
        'due_date' => sanitize_text_field($_POST['due_date']),
        'amount' => floatval($_POST['amount']),
        'currency' => sanitize_text_field($_POST['currency']),
        'note' => sanitize_textarea_field($_POST['note']),
        'admin_only' => in_array($_POST['type'], ['admin_only_tax', 'admin_only_other'], true),
    ];
    global $wpdb;
    $tenant_id = $wpdb->get_var($wpdb->prepare(
        "SELECT user_id FROM {$wpdb->prefix}ppm_tenants WHERE apartment_id = %d ORDER BY id DESC LIMIT 1",
        $data['apartment_id']
    ));
    $data['tenant_id'] = $tenant_id;
    $payment_id = PPM_DB::create_payment($data);

    if ($tenant_id) {
        $user = get_user_by('id', $tenant_id);
        $admin_email = get_option('admin_email');
        $details = "{$data['type']} {$data['period']} kwota {$data['amount']} {$data['currency']}";
        PPM_Email::send_payment_created($user->user_email, $admin_email, $details);
    }

    wp_redirect(admin_url('admin.php?page=ppm_payments'));
    exit;
}

function ppm_handle_confirm_payment()
{
    if (!current_user_can('manage_options')) {
        wp_die('Brak dostępu');
    }
    check_admin_referer('ppm_confirm_payment');
    $payment_id = intval($_POST['payment_id']);
    $payment = PPM_DB::get_payment($payment_id);
    if (!$payment) {
        wp_die('Nie znaleziono płatności');
    }
    PPM_DB::update_payment($payment_id, ['status' => 'confirmed_paid_by_admin']);
    PPM_DB::add_payment_log($payment_id, 'confirmed', null, get_current_user_id());
    if ($payment->tenant_id) {
        $user = get_user_by('id', $payment->tenant_id);
        $details = "{$payment->type} {$payment->period} kwota {$payment->amount} {$payment->currency}";
        PPM_Email::send_payment_confirmed($user->user_email, $details);
    }
    wp_redirect(admin_url('admin.php?page=ppm_payments'));
    exit;
}

function ppm_handle_reject_payment()
{
    if (!current_user_can('manage_options')) {
        wp_die('Brak dostępu');
    }
    check_admin_referer('ppm_reject_payment');
    $payment_id = intval($_POST['payment_id']);
    $reason = sanitize_text_field($_POST['reason']);
    $payment = PPM_DB::get_payment($payment_id);
    if (!$payment) {
        wp_die('Nie znaleziono płatności');
    }
    PPM_DB::update_payment($payment_id, ['status' => 'pending']);
    PPM_DB::add_payment_log($payment_id, 'rejected', $reason, get_current_user_id());
    if ($payment->tenant_id) {
        $user = get_user_by('id', $payment->tenant_id);
        $details = "{$payment->type} {$payment->period} kwota {$payment->amount} {$payment->currency}";
        PPM_Email::send_payment_rejected($user->user_email, $details, $reason);
    }
    wp_redirect(admin_url('admin.php?page=ppm_payments'));
    exit;
}

function ppm_render_settings()
{
    $templates = PPM_DB::get_recurring_templates();
    $apartments = PPM_DB::get_apartments();
    $portal_page_id = intval(get_option('ppm_portal_page_id'));
    ?>
    <div class="wrap">
        <h1>Ustawienia</h1>
        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="ppm-form">
            <?php wp_nonce_field('ppm_save_settings'); ?>
            <input type="hidden" name="action" value="ppm_save_settings">
            <h2>Ogólne</h2>
            <label>Strona portalu najemcy</label>
            <?php
            wp_dropdown_pages([
                'name' => 'portal_page_id',
                'show_option_none' => 'Wybierz stronę',
                'option_none_value' => '',
                'selected' => $portal_page_id,
            ]);
            ?>
            <button class="button button-primary" type="submit">Zapisz ustawienia</button>
        </form>

        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="ppm-form">
            <?php wp_nonce_field('ppm_save_recurring'); ?>
            <input type="hidden" name="action" value="ppm_save_recurring">
            <h2>Szablony cykliczne</h2>
            <div class="ppm-grid">
                <label>Apartament
                    <select name="apartment_id" required>
                        <option value="">Wybierz</option>
                        <?php foreach ($apartments as $apartment) : ?>
                            <option value="<?php echo esc_attr($apartment->id); ?>"><?php echo esc_html($apartment->name); ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label>Typ
                    <select name="type">
                        <option value="rent">Czynsz/odstępne</option>
                        <option value="hoa">Czynsz do wspólnoty</option>
                        <option value="utilities">Media</option>
                        <option value="admin_only_tax">Podatki (tylko admin)</option>
                        <option value="admin_only_other">Inne (tylko admin)</option>
                    </select>
                </label>
                <label>Kwota <input type="number" step="0.01" name="amount" required></label>
                <label>Waluta <input type="text" name="currency" value="<?php echo esc_attr(PPM_CURRENCY); ?>" required></label>
                <label>Miesiąc start (YYYY-MM) <input type="text" name="start_month" required></label>
                <label>Miesiąc end (YYYY-MM) <input type="text" name="end_month"></label>
            </div>
            <button class="button button-primary" type="submit">Zapisz</button>
        </form>

        <h2>Lista szablonów</h2>
        <table class="widefat">
            <thead>
            <tr>
                <th>Apartament</th>
                <th>Typ</th>
                <th>Kwota</th>
                <th>Start</th>
                <th>Koniec</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($templates as $template) : ?>
                <?php $apartment = PPM_DB::get_apartment($template->apartment_id); ?>
                <tr>
                    <td><?php echo esc_html($apartment ? $apartment->name : '-'); ?></td>
                    <td><?php echo esc_html($template->type); ?></td>
                    <td><?php echo esc_html(number_format($template->amount, 2)); ?> <?php echo esc_html($template->currency); ?></td>
                    <td><?php echo esc_html($template->start_month); ?></td>
                    <td><?php echo esc_html($template->end_month); ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php
}

function ppm_handle_save_recurring()
{
    if (!current_user_can('manage_options')) {
        wp_die('Brak dostępu');
    }
    check_admin_referer('ppm_save_recurring');
    PPM_DB::upsert_recurring_template($_POST);
    wp_redirect(admin_url('admin.php?page=ppm_settings'));
    exit;
}

function ppm_handle_save_settings()
{
    if (!current_user_can('manage_options')) {
        wp_die('Brak dostępu');
    }
    check_admin_referer('ppm_save_settings');
    $portal_page_id = intval($_POST['portal_page_id']);
    update_option('ppm_portal_page_id', $portal_page_id);
    wp_redirect(admin_url('admin.php?page=ppm_settings'));
    exit;
}
