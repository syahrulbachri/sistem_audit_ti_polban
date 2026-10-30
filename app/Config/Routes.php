<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->get('/', 'Auth::index');
$routes->post('/auth/process', 'Auth::process');
$routes->get('/logout', 'Auth::logout');

// Profile Routes (Shared untuk Admin, Auditor, Auditee, Pimpinan)
$routes->get('/profile', 'ProfileController::index');
$routes->post('/profile/update', 'ProfileController::update');

// ====================================================================
// ADMIN ROUTES
// ====================================================================
$routes->get('/admin/dashboard', 'Admin\DashboardAdminController::index');
$routes->get('/admin/dashboard/chart-data', 'Admin\DashboardAdminController::getChartData');
$routes->get('/admin/users', 'Admin\UserController::index');
$routes->get('/admin/users/create', 'Admin\UserController::create');
$routes->post('/admin/users/store', 'Admin\UserController::store');
$routes->get('/admin/users/edit/(:num)', 'Admin\UserController::edit/$1');
$routes->post('/admin/users/update/(:num)', 'Admin\UserController::update/$1');
$routes->get('/admin/users/delete/(:num)', 'Admin\UserController::delete/$1');

$routes->get('/admin/questions', 'Admin\QuestionTemplateController::index');
$routes->get('/admin/questions/create', 'Admin\QuestionTemplateController::create');
$routes->post('/admin/questions/store', 'Admin\QuestionTemplateController::store');
$routes->get('/admin/questions/edit/(:num)', 'Admin\QuestionTemplateController::edit/$1');
$routes->post('/admin/questions/update/(:num)', 'Admin\QuestionTemplateController::update/$1');
$routes->get('/admin/questions/delete/(:num)', 'Admin\QuestionTemplateController::delete/$1');

$routes->get('/admin/periodes', 'Admin\PeriodeController::index');
$routes->get('/admin/periodes/create', 'Admin\PeriodeController::create');
$routes->post('/admin/periodes/store', 'Admin\PeriodeController::store');
$routes->get('/admin/periodes/edit/(:num)', 'Admin\PeriodeController::edit/$1');
$routes->post('/admin/periodes/update/(:num)', 'Admin\PeriodeController::update/$1');
$routes->get('/admin/periodes/toggle/(:num)', 'Admin\PeriodeController::toggle/$1');
$routes->get('/admin/periodes/aktifkan/(:num)', 'Admin\PeriodeController::aktifkan/$1');
$routes->get('/admin/periodes/delete/(:num)', 'Admin\PeriodeController::delete/$1');

$routes->get('/admin/frameworks', 'Admin\FrameworkController::index');
$routes->get('/admin/frameworks/create', 'Admin\FrameworkController::create');
$routes->post('/admin/frameworks/store', 'Admin\FrameworkController::store');
$routes->get('/admin/frameworks/edit/(:num)', 'Admin\FrameworkController::edit/$1');
$routes->post('/admin/frameworks/update/(:num)', 'Admin\FrameworkController::update/$1');
$routes->get('/admin/frameworks/toggle/(:num)', 'Admin\FrameworkController::toggle/$1');

$routes->get('/admin/planning', 'Admin\PlanningController::index');
$routes->get('/admin/planning/create', 'Admin\PlanningController::create');
$routes->post('/admin/planning/store', 'Admin\PlanningController::store');
$routes->get('/admin/planning/edit/(:num)', 'Admin\PlanningController::edit/$1');
$routes->post('/admin/planning/update/(:num)', 'Admin\PlanningController::update/$1');
$routes->get('/admin/planning/delete/(:num)', 'Admin\PlanningController::delete/$1');
$routes->get('/admin/planning/detail/(:num)', 'Admin\PlanningController::detail/$1');
$routes->post('/admin/planning/perpanjang-deadline', 'Admin\PlanningController::perpanjangDeadline');

$routes->get('/admin/findings', 'Admin\FindingsController::index');
$routes->get('/admin/findings/detail/(:num)', 'Admin\FindingsController::detail/$1');

$routes->get('/admin/completed-audits', 'Admin\CompletedAuditsController::index');
$routes->get('/admin/completed-audits/detail/(:num)', 'Admin\CompletedAuditsController::detail/$1');
$routes->get('/admin/completed-audits/log-export/(:num)', 'Admin\CompletedAuditsController::logExport/$1');

$routes->get('/admin/activity-logs', 'Admin\ActivityLogController::index');
$routes->get('/admin/activity-logs/delete/(:num)', 'Admin\ActivityLogController::delete/$1');
$routes->get('/admin/activity-logs/clear-old', 'Admin\ActivityLogController::clearOld');

// ====================================================================
// AUDITOR ROUTES
// ====================================================================
$routes->get('/dashboard', '\App\Controllers\Auditor\DashboardAuditorController::index');
$routes->get('/auditor/list', 'Auditor\AuditListController::index');
$routes->get('/auditor/history', 'Auditor\AuditListController::history');
$routes->get('/audit/detail/(:num)', 'Auditor\DashboardAuditorController::detail/$1');
$routes->get('/audit/nilai/(:num)', 'Auditor\DashboardAuditorController::nilai/$1');
$routes->post('/audit/simpan-nilai/(:num)', 'Auditor\DashboardAuditorController::simpanNilai/$1');
$routes->post('/audit/save-assignments/(:num)', 'Auditor\DashboardAuditorController::saveAssignments/$1');
$routes->get('/auditor/view-evidence/(:num)', 'Auditor\DashboardAuditorController::viewEvidence/$1');
$routes->get('/auditor/monitoring-temuan', 'Auditor\MonitoringTemuanController::index');
$routes->get('/auditor/temuan/(:num)', 'Auditor\MonitoringTemuanController::detail/$1');
$routes->post('/auditor/temuan/review-rtl/(:num)', 'Auditor\MonitoringTemuanController::reviewRTL/$1');
$routes->post('/auditor/temuan/verifikasi-bukti/(:num)', 'Auditor\MonitoringTemuanController::verifikasiBukti/$1');
$routes->get('/auditor/activity-logs', 'Auditor\ActivityLogController::index');
$routes->post('/audit/minta-revisi/(:num)', 'Auditor\DashboardAuditorController::mintaRevisi/$1');

// ====================================================================
// AUDITEE ROUTES
// ====================================================================
$routes->get('/auditee/dashboard', 'Auditee\DashboardAuditeeController::index');
$routes->get('/auditee/daftar-audit', 'Auditee\DashboardAuditeeController::daftarAudit');
$routes->get('/auditee/riwayat', 'Auditee\DashboardAuditeeController::riwayat');
$routes->get('/auditee/fill/(:num)', 'Auditee\FillAuditeeController::fill/$1');
$routes->post('/auditee/save-answer/(:num)', 'Auditee\FillAuditeeController::saveAnswer/$1');
$routes->get('/auditee/log-aktivitas', 'Auditee\DashboardAuditeeController::logAktivitas');

$routes->get('/auditee/rtl/(:num)', 'Auditee\TindakLanjutController::rtl/$1');
$routes->post('/auditee/rtl/save/(:num)', 'Auditee\TindakLanjutController::saveRtl/$1');
$routes->get('/auditee/bukti/(:num)', 'Auditee\TindakLanjutController::bukti/$1');
$routes->post('/auditee/bukti/save/(:num)', 'Auditee\TindakLanjutController::saveBukti/$1');

// ====================================================================
// PIMPINAN ROUTES (Sudah digabung menjadi 1 block agar tidak duplikat)
// ====================================================================
$routes->group('pimpinan', function ($routes) {
    $routes->get('dashboard', 'Pimpinan\DashboardPimpinanController::index');
    $routes->get('profile', 'Pimpinan\PimpinanProfileController::index');
    $routes->get('profile/edit', 'Pimpinan\PimpinanProfileController::edit');
    $routes->post('profile/update-identity', 'Pimpinan\PimpinanProfileController::updateIdentity');
    $routes->post('profile/update', 'Pimpinan\PimpinanProfileController::update');
    
    $routes->get('daftar_lha', 'Pimpinan\MonitoringLHAController::index');
    $routes->get('lha', 'Pimpinan\LhaController::index');
    $routes->get('lha/review/(:segment)', 'Pimpinan\LhaController::review/$1');
    $routes->get('lha/detail/(:segment)', 'Pimpinan\LhaController::detail/$1');
    $routes->get('lha_detail/(:num)', 'Pimpinan\MonitoringLHAController::detail/$1');
    $routes->get('lha/download-evidence/(:num)', 'Pimpinan\LhaController::downloadEvidence/$1');
    $routes->post('lha/decide/(:segment)', 'Pimpinan\LhaController::decide/$1');
    
    $routes->get('rtl', 'Pimpinan\RtlController::index');
    $routes->get('rtl/detail/(:segment)', 'Pimpinan\RtlController::detail/$1');
    $routes->get('rtl/view-bukti/(:num)', 'Pimpinan\RtlController::viewBukti/$1');
    $routes->get('rtl/download-bukti/(:num)', 'Pimpinan\RtlController::downloadBukti/$1');
    $routes->get('rtl/view-jawaban/(:num)', 'Pimpinan\RtlController::viewJawaban/$1');
    $routes->get('rtl/download-jawaban/(:num)', 'Pimpinan\RtlController::downloadJawaban/$1');
    
    $routes->get('arsip', 'Pimpinan\ArsipController::index');
    $routes->get('activity-logs', 'Pimpinan\ActivityLogController::index');
});

// ====================================================================
// ROUTE KHUSUS: PREVIEW FILE BUKTI PERBAIKAN (PDF & GAMBAR)
// ====================================================================
// Route untuk akses file bukti perbaikan - PERBAIKAN FINAL (COMPATIBLE CI4)
$routes->get('uploads/bukti_perbaikan/(:any)', function (string $filename) {
    $filename = urldecode($filename);
    $db = \Config\Database::connect();

    // LANGKAH 1: Cari di tabel audit_question_assignments
    $fileData = $db->table('audit_question_assignments')
        ->select('evidence_file, evidence_filename')
        ->where('evidence_filename', $filename)
        ->get()->getRow();

    // LANGKAH 2: Jika tidak ada di assignments, cari di folder dengan nama asli
    if (!$fileData) {
        $filePath = FCPATH . 'uploads/bukti_perbaikan/' . $filename;
        if (file_exists($filePath)) {
            // Gunakan response download CI4 dengan inline = true untuk preview
            return $this->response->download($filename, true)->setFilePath($filePath);
        }
    }

    // LANGKAH 3: Jika ada di database sebagai BLOB
    if ($fileData && !empty($fileData->evidence_file)) {
        $response = service('response');
        $response->setBody($fileData->evidence_file);
        $response->setContentType(mime_content_type($fileData->evidence_filename));
        $response->setHeader('Content-Disposition', 'inline; filename="' . $fileData->evidence_filename . '"');
        return $response;
    }

    // LANGKAH 4: Cari di folder uploads dengan pattern matching
    $folder = FCPATH . 'uploads/bukti_perbaikan/';
    $files = glob($folder . '*');
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    $matchingFiles = array_filter($files, function ($f) use ($ext) {
        return strtolower(pathinfo($f, PATHINFO_EXTENSION)) === $ext;
    });

    if (count($matchingFiles) === 1) {
        $filePath = array_values($matchingFiles)[0];
        $originalName = $filename;
        // Gunakan response download CI4
        return $this->response->download($originalName, true)->setFilePath($filePath);
    }

    // File tidak ditemukan
    throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
});