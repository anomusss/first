<?php

if (!defined('ABSPATH')) {
    exit;
}

add_action('admin_post_ppm_export_csv', 'ppm_handle_export_csv');

function ppm_render_reports()
{
    $apartments = PPM_DB::get_apartments(true);
    $selected_apartment = isset($_GET['apartment_id']) ? intval($_GET['apartment_id']) : 0;
    $from = isset($_GET['from']) ? sanitize_text_field($_GET['from']) : '';
    $to = isset($_GET['to']) ? sanitize_text_field($_GET['to']) : '';

    $report = ppm_get_profit_report($selected_apartment, $from, $to);
    ?>
    <div class="wrap">
        <h1>Raporty i rentowność</h1>
        <form method="get" class="ppm-form">
            <input type="hidden" name="page" value="ppm_reports">
            <div class="ppm-grid">
                <label>Apartament
                    <select name="apartment_id">
                        <option value="0">Wszystkie</option>
                        <?php foreach ($apartments as $apartment) : ?>
                            <option value="<?php echo esc_attr($apartment->id); ?>" <?php selected($selected_apartment, $apartment->id); ?>><?php echo esc_html($apartment->name); ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label>Od (YYYY-MM) <input type="text" name="from" value="<?php echo esc_attr($from); ?>"></label>
                <label>Do (YYYY-MM) <input type="text" name="to" value="<?php echo esc_attr($to); ?>"></label>
            </div>
            <button class="button">Filtruj</button>
            <a class="button" href="<?php echo esc_url(admin_url('admin-post.php?action=ppm_export_csv&apartment_id=' . $selected_apartment . '&from=' . $from . '&to=' . $to)); ?>">Eksport CSV</a>
        </form>

        <table class="widefat">
            <thead>
            <tr>
                <th>Miesiąc</th>
                <th>Przychód (rent)</th>
                <th>Koszty (HOA + media + admin)</th>
                <th>Zysk</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($report as $row) : ?>
                <tr>
                    <td><?php echo esc_html($row['period']); ?></td>
                    <td><?php echo esc_html(number_format($row['income'], 2)); ?></td>
                    <td><?php echo esc_html(number_format($row['costs'], 2)); ?></td>
                    <td><?php echo esc_html(number_format($row['profit'], 2)); ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php
}

function ppm_get_profit_report($apartment_id = 0, $from = '', $to = '')
{
    global $wpdb;
    $sql = "SELECT period,
            SUM(CASE WHEN type = 'rent' THEN amount ELSE 0 END) AS income,
            SUM(CASE WHEN type IN ('hoa','utilities','admin_only_tax','admin_only_other','other') THEN amount ELSE 0 END) AS costs
        FROM {$wpdb->prefix}ppm_payments WHERE 1=1";
    $params = [];
    if ($apartment_id) {
        $sql .= " AND apartment_id = %d";
        $params[] = $apartment_id;
    }
    if ($from) {
        $sql .= " AND period >= %s";
        $params[] = $from;
    }
    if ($to) {
        $sql .= " AND period <= %s";
        $params[] = $to;
    }
    $sql .= " GROUP BY period ORDER BY period ASC";

    $rows = $params ? $wpdb->get_results($wpdb->prepare($sql, $params), ARRAY_A) : $wpdb->get_results($sql, ARRAY_A);
    $report = [];
    foreach ($rows as $row) {
        $income = floatval($row['income']);
        $costs = floatval($row['costs']);
        $report[] = [
            'period' => $row['period'],
            'income' => $income,
            'costs' => $costs,
            'profit' => $income - $costs,
        ];
    }
    return $report;
}

function ppm_handle_export_csv()
{
    if (!current_user_can('manage_options')) {
        wp_die('Brak dostępu');
    }
    $apartment_id = isset($_GET['apartment_id']) ? intval($_GET['apartment_id']) : 0;
    $from = isset($_GET['from']) ? sanitize_text_field($_GET['from']) : '';
    $to = isset($_GET['to']) ? sanitize_text_field($_GET['to']) : '';

    $report = ppm_get_profit_report($apartment_id, $from, $to);
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="ppm_report.csv"');
    $output = fopen('php://output', 'w');
    fputcsv($output, ['period', 'income', 'costs', 'profit']);
    foreach ($report as $row) {
        fputcsv($output, [$row['period'], $row['income'], $row['costs'], $row['profit']]);
    }
    fclose($output);
    exit;
}
