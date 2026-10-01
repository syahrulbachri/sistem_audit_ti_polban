<?php

namespace App\Cells;

class NotificationCell
{
    public function render()
    {
        $db = \Config\Database::connect();
        $today = date('Y-m-d');

        // Ambil periode yang statusnya draft DAN tanggal mulainya sudah hari ini atau lewat
        $periodes = $db->table('periodes')
            ->where('tanggal_mulai <=', $today)
            ->where('status', 'draft')
            ->orderBy('tanggal_mulai', 'ASC')
            ->get()
            ->getResult();

        // Kirim data ke view kecil khusus notifikasi
        return view('layouts/partials/notification_dropdown', [
            'periodes' => $periodes
        ]);
    }
}