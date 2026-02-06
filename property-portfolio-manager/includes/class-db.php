<?php

if (!defined('ABSPATH')) {
    exit;
}

class PPM_DB
{
    public static function maybe_upgrade()
    {
        // Placeholder for future upgrades.
    }

    public static function get_apartments($include_archived = false)
    {
        global $wpdb;
        $sql = "SELECT * FROM {$wpdb->prefix}ppm_apartments";
        if (!$include_archived) {
            $sql .= " WHERE archived = 0";
        }
        $sql .= " ORDER BY id DESC";
        return $wpdb->get_results($sql);
    }

    public static function get_apartment($id)
    {
        global $wpdb;
        return $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}ppm_apartments WHERE id = %d", $id));
    }

    public static function upsert_apartment($data)
    {
        global $wpdb;
        $now = current_time('mysql');
        $payload = [
            'name' => sanitize_text_field($data['name']),
            'address' => sanitize_text_field($data['address']),
            'city' => sanitize_text_field($data['city']),
            'notes' => sanitize_textarea_field($data['notes']),
            'status' => sanitize_text_field($data['status']),
            'start_date' => $data['start_date'] ?: null,
            'end_date' => $data['end_date'] ?: null,
            'archived' => isset($data['archived']) ? 1 : 0,
            'updated_at' => $now,
        ];

        if (!empty($data['id'])) {
            $wpdb->update("{$wpdb->prefix}ppm_apartments", $payload, ['id' => intval($data['id'])]);
            return intval($data['id']);
        }

        $payload['created_at'] = $now;
        $wpdb->insert("{$wpdb->prefix}ppm_apartments", $payload);
        return $wpdb->insert_id;
    }

    public static function create_invite($email, $apartment_id)
    {
        global $wpdb;
        $token = wp_generate_password(32, false, false);
        $token_hash = wp_hash_password($token);
        $expires = gmdate('Y-m-d H:i:s', strtotime('+' . PPM_INVITE_TTL_DAYS . ' days'));
        $now = current_time('mysql');
        $wpdb->insert("{$wpdb->prefix}ppm_invites", [
            'email' => sanitize_email($email),
            'token_hash' => $token_hash,
            'apartment_id' => $apartment_id ? intval($apartment_id) : null,
            'expires_at' => $expires,
            'created_at' => $now,
        ]);
        return [$token, $wpdb->insert_id];
    }

    public static function get_invite_by_token($token)
    {
        global $wpdb;
        $invites = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}ppm_invites WHERE used_at IS NULL AND expires_at >= NOW()");
        foreach ($invites as $invite) {
            if (wp_check_password($token, $invite->token_hash)) {
                return $invite;
            }
        }
        return null;
    }

    public static function mark_invite_used($id)
    {
        global $wpdb;
        $wpdb->update("{$wpdb->prefix}ppm_invites", [
            'used_at' => current_time('mysql'),
        ], ['id' => intval($id)]);
    }

    public static function assign_tenant($user_id, $apartment_id, $start_date = null)
    {
        global $wpdb;
        $now = current_time('mysql');
        $wpdb->insert("{$wpdb->prefix}ppm_tenants", [
            'user_id' => intval($user_id),
            'apartment_id' => intval($apartment_id),
            'start_date' => $start_date,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        return $wpdb->insert_id;
    }

    public static function get_tenant_apartments($user_id)
    {
        global $wpdb;
        $sql = "SELECT t.*, a.name, a.address, a.city FROM {$wpdb->prefix}ppm_tenants t
                INNER JOIN {$wpdb->prefix}ppm_apartments a ON t.apartment_id = a.id
                WHERE t.user_id = %d";
        return $wpdb->get_results($wpdb->prepare($sql, $user_id));
    }

    public static function get_tenants()
    {
        global $wpdb;
        $sql = "SELECT t.*, u.user_email, u.display_name, a.name AS apartment_name FROM {$wpdb->prefix}ppm_tenants t
                INNER JOIN {$wpdb->users} u ON t.user_id = u.ID
                INNER JOIN {$wpdb->prefix}ppm_apartments a ON t.apartment_id = a.id
                ORDER BY t.id DESC";
        return $wpdb->get_results($sql);
    }

    public static function upsert_recurring_template($data)
    {
        global $wpdb;
        $now = current_time('mysql');
        $payload = [
            'apartment_id' => intval($data['apartment_id']),
            'type' => sanitize_text_field($data['type']),
            'amount' => floatval($data['amount']),
            'currency' => sanitize_text_field($data['currency']),
            'start_month' => sanitize_text_field($data['start_month']),
            'end_month' => $data['end_month'] ? sanitize_text_field($data['end_month']) : null,
            'updated_at' => $now,
        ];

        if (!empty($data['id'])) {
            $wpdb->update("{$wpdb->prefix}ppm_recurring_templates", $payload, ['id' => intval($data['id'])]);
            return intval($data['id']);
        }

        $payload['created_at'] = $now;
        $wpdb->insert("{$wpdb->prefix}ppm_recurring_templates", $payload);
        return $wpdb->insert_id;
    }

    public static function get_recurring_templates($apartment_id = null)
    {
        global $wpdb;
        $sql = "SELECT * FROM {$wpdb->prefix}ppm_recurring_templates";
        if ($apartment_id) {
            $sql .= $wpdb->prepare(" WHERE apartment_id = %d", $apartment_id);
        }
        $sql .= " ORDER BY id DESC";
        return $wpdb->get_results($sql);
    }

    public static function create_payment($data)
    {
        global $wpdb;
        $now = current_time('mysql');
        $payload = [
            'apartment_id' => intval($data['apartment_id']),
            'tenant_id' => $data['tenant_id'] ? intval($data['tenant_id']) : null,
            'type' => sanitize_text_field($data['type']),
            'period' => sanitize_text_field($data['period']),
            'created_at' => $now,
            'due_date' => sanitize_text_field($data['due_date']),
            'amount' => floatval($data['amount']),
            'currency' => sanitize_text_field($data['currency']),
            'status' => sanitize_text_field($data['status'] ?? 'pending'),
            'admin_only' => !empty($data['admin_only']) ? 1 : 0,
            'note' => sanitize_textarea_field($data['note'] ?? ''),
            'updated_at' => $now,
        ];
        $wpdb->insert("{$wpdb->prefix}ppm_payments", $payload);
        $payment_id = $wpdb->insert_id;
        self::add_payment_log($payment_id, 'created', null);
        return $payment_id;
    }

    public static function get_payments($filters = [])
    {
        global $wpdb;
        $sql = "SELECT p.*, a.name AS apartment_name, u.user_email FROM {$wpdb->prefix}ppm_payments p
                INNER JOIN {$wpdb->prefix}ppm_apartments a ON p.apartment_id = a.id
                LEFT JOIN {$wpdb->users} u ON p.tenant_id = u.ID WHERE 1=1";
        $params = [];
        if (!empty($filters['tenant_id'])) {
            $sql .= " AND p.tenant_id = %d";
            $params[] = intval($filters['tenant_id']);
        }
        if (!empty($filters['status'])) {
            $sql .= " AND p.status = %s";
            $params[] = $filters['status'];
        }
        if (!empty($filters['period'])) {
            $sql .= " AND p.period = %s";
            $params[] = $filters['period'];
        }
        $sql .= " ORDER BY p.due_date ASC";
        if ($params) {
            return $wpdb->get_results($wpdb->prepare($sql, $params));
        }
        return $wpdb->get_results($sql);
    }

    public static function get_payment($id)
    {
        global $wpdb;
        return $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}ppm_payments WHERE id = %d", $id));
    }

    public static function update_payment($id, $data)
    {
        global $wpdb;
        $data['updated_at'] = current_time('mysql');
        $wpdb->update("{$wpdb->prefix}ppm_payments", $data, ['id' => intval($id)]);
    }

    public static function add_payment_log($payment_id, $action, $note = null, $user_id = null)
    {
        global $wpdb;
        $wpdb->insert("{$wpdb->prefix}ppm_payment_logs", [
            'payment_id' => intval($payment_id),
            'action' => sanitize_text_field($action),
            'note' => $note ? sanitize_textarea_field($note) : null,
            'created_at' => current_time('mysql'),
            'user_id' => $user_id ? intval($user_id) : null,
        ]);
    }

    public static function get_logs($payment_id)
    {
        global $wpdb;
        return $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}ppm_payment_logs WHERE payment_id = %d ORDER BY id DESC", $payment_id));
    }

    public static function get_dashboard_stats()
    {
        global $wpdb;
        $stats = [];
        $stats['total_apartments'] = intval($wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}ppm_apartments WHERE archived = 0"));
        $stats['rented'] = intval($wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}ppm_apartments WHERE status = 'rented' AND archived = 0"));
        $stats['vacant'] = intval($wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}ppm_apartments WHERE status = 'vacant' AND archived = 0"));
        $stats['due_soon'] = intval($wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}ppm_payments WHERE status = 'pending' AND due_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 3 DAY)"));
        $stats['overdue'] = intval($wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}ppm_payments WHERE status = 'overdue'"));
        $stats['waiting_confirmation'] = intval($wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}ppm_payments WHERE status = 'marked_paid_by_tenant'"));

        $month = gmdate('Y-m');
        $stats['month_income'] = floatval($wpdb->get_var($wpdb->prepare(
            "SELECT SUM(amount) FROM {$wpdb->prefix}ppm_payments WHERE period = %s AND type = 'rent'",
            $month
        )));
        $stats['month_hoa'] = floatval($wpdb->get_var($wpdb->prepare(
            "SELECT SUM(amount) FROM {$wpdb->prefix}ppm_payments WHERE period = %s AND type = 'hoa'",
            $month
        )));
        $stats['month_utilities'] = floatval($wpdb->get_var($wpdb->prepare(
            "SELECT SUM(amount) FROM {$wpdb->prefix}ppm_payments WHERE period = %s AND type = 'utilities'",
            $month
        )));

        return $stats;
    }
}
