<?php

/**
 * Helper untuk mencatat Log Aktivitas
 */

if (!function_exists('log_activity')) {
    /**
     * Mencatat aktivitas user ke tabel activity_logs (fungsi asli dari temanmu)
     * 
     * @param string $action     - LOGIN, LOGOUT, CREATE, UPDATE, DELETE, EXPORT
     * @param string $tableName  - Nama tabel (users, audits, periodes, dll)
     * @param string $description - Deskripsi detail aktivitas
     */
    function log_activity(string $action, string $tableName, string $description)
    {
        try {
            $db = \Config\Database::connect();
            $request = service('request');

            // Ambil data user dari session (jika ada)
            $userId = session()->get('id') ?? null;
            $username = session()->get('username') ?? 'System';
            $role = session()->get('role') ?? null;

            // Ambil IP Address (support proxy)
            $ipAddress = $request->getIPAddress();

            // Ambil User Agent
            $userAgent = $request->getUserAgent();

            // Insert ke tabel activity_logs
            $db->table('activity_logs')->insert([
                'user_id' => $userId,
                'username' => $username,
                'role' => $role,
                'action' => strtoupper($action),
                'table_name' => $tableName,
                'description' => $description,
                'ip_address' => $ipAddress,
                'user_agent' => $userAgent,
                'created_at' => date('Y-m-d H:i:s')
            ]);
        } catch (\Exception $e) {
            // Jangan biarkan error log mengganggu proses utama
            log_message('error', 'Gagal mencatat activity log: ' . $e->getMessage());
        }
    }
}

if (!function_exists('log_aktivitas')) {
    /**
     * Wrapper/Adapter untuk log_activity() - supaya kode auditee yang sudah pakai log_aktivitas() tetap jalan
     * 
     * @param string $deskripsi - Deskripsi aktivitas
     * @param string $tipe      - auth|jawaban|rtl|revisi|bukti|info
     */
    function log_aktivitas(string $deskripsi, string $tipe = 'info')
    {
        // Mapping tipe ke action & table_name
        switch ($tipe) {
            case 'auth':
                $action = (stripos($deskripsi, 'logout') !== false) ? 'LOGOUT' : 'LOGIN';
                $table = 'system';
                break;
            case 'jawaban':
                $action = 'UPDATE';
                $table = 'audit_question_assignments';
                break;
            case 'rtl':
            case 'revisi':
            case 'bukti':
                $action = 'UPDATE';
                $table = 'temuans';
                break;
            default:
                $action = 'UPDATE';
                $table = 'system';
        }

        log_activity($action, $table, $deskripsi);
    }
}

if (!function_exists('parse_user_agent')) {
    /**
     * Parse User Agent menjadi format yang mudah dibaca
     * Contoh output: "Chrome - Windows 10"
     */
    function parse_user_agent(string $userAgent): string
    {
        if (empty($userAgent) || $userAgent === 'Unknown') {
            return 'Unknown';
        }

        // Deteksi Browser
        $browser = 'Unknown';
        if (stripos($userAgent, 'Edg') !== false) {
            $browser = 'Edge';
        } elseif (stripos($userAgent, 'Chrome') !== false) {
            $browser = 'Chrome';
        } elseif (stripos($userAgent, 'Firefox') !== false) {
            $browser = 'Firefox';
        } elseif (stripos($userAgent, 'Safari') !== false) {
            $browser = 'Safari';
        } elseif (stripos($userAgent, 'Opera') !== false || stripos($userAgent, 'OPR') !== false) {
            $browser = 'Opera';
        }

        // Deteksi OS
        $os = 'Unknown';
        if (stripos($userAgent, 'Windows NT 10') !== false) {
            $os = 'Windows 10';
        } elseif (stripos($userAgent, 'Windows NT 11') !== false) {
            $os = 'Windows 11';
        } elseif (stripos($userAgent, 'Mac OS X') !== false) {
            $os = 'macOS';
        } elseif (stripos($userAgent, 'Linux') !== false) {
            $os = 'Linux';
        } elseif (stripos($userAgent, 'Android') !== false) {
            $os = 'Android';
        } elseif (stripos($userAgent, 'iPhone') !== false || stripos($userAgent, 'iPad') !== false) {
            $os = 'iOS';
        }

        return $browser . ' - ' . $os;
    }
}