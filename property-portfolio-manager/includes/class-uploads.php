<?php

if (!defined('ABSPATH')) {
    exit;
}

class PPM_Uploads
{
    public static function ensure_upload_dir()
    {
        $upload = wp_upload_dir();
        $dir = trailingslashit($upload['basedir']) . PPM_UPLOAD_SUBDIR;
        if (!file_exists($dir)) {
            wp_mkdir_p($dir);
        }
        $htaccess = $dir . '/.htaccess';
        if (!file_exists($htaccess)) {
            file_put_contents($htaccess, "Deny from all\n");
        }
        $index = $dir . '/index.php';
        if (!file_exists($index)) {
            file_put_contents($index, "<?php\n// Silence is golden.\n");
        }
        return $dir;
    }

    public static function handle_upload($file)
    {
        self::ensure_upload_dir();
        $allowed = [
            'application/pdf' => 'pdf',
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
        ];
        if ($file['size'] > PPM_MAX_UPLOAD_MB * 1024 * 1024) {
            return new WP_Error('ppm_upload_size', 'Plik jest zbyt duży.');
        }
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        if (!isset($allowed[$mime])) {
            return new WP_Error('ppm_upload_type', 'Nieprawidłowy typ pliku.');
        }
        $extension = $allowed[$mime];
        $filename = wp_generate_password(20, false, false) . '.' . $extension;
        $upload = wp_upload_dir();
        $target = trailingslashit($upload['basedir']) . PPM_UPLOAD_SUBDIR . '/' . $filename;
        if (!move_uploaded_file($file['tmp_name'], $target)) {
            return new WP_Error('ppm_upload_failed', 'Nie udało się zapisać pliku.');
        }
        return $filename;
    }

    public static function handle_proof_download()
    {
        if (!is_user_logged_in()) {
            wp_die('Brak dostępu.');
        }
        $payment_id = intval($_GET['ppm_download_proof']);
        $payment = PPM_DB::get_payment($payment_id);
        if (!$payment || empty($payment->proof_file)) {
            wp_die('Plik nie istnieje.');
        }

        $user = wp_get_current_user();
        $is_admin = current_user_can('manage_options');
        if (!$is_admin && intval($payment->tenant_id) !== $user->ID) {
            wp_die('Brak dostępu.');
        }

        $upload = wp_upload_dir();
        $path = trailingslashit($upload['basedir']) . PPM_UPLOAD_SUBDIR . '/' . $payment->proof_file;
        if (!file_exists($path)) {
            wp_die('Plik nie istnieje.');
        }
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($payment->proof_file) . '"');
        readfile($path);
        exit;
    }
}
