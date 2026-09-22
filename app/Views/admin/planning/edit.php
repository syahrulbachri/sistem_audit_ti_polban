<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<h4 class="fw-bold mb-4" style="color: var(--primary-navy);"><?= $page_title ?></h4>

<div class="card border-0 shadow-sm" style="border-radius: 12px; max-width: 800px;">
    <div class="card-body p-4">
        <?php if (session()->getFlashdata('errors')): ?>
            <div class="alert alert-danger">
                <ul class="mb-0"><?php foreach (session()->getFlashdata('errors') as $error): ?><li><?= $error ?></li><?php endforeach; ?></ul>
            </div>
        <?php endif; ?>

        <form action="/admin/planning/update/<?= $audit->id ?>" method="post">
            <?= csrf_field() ?>

            <div class="mb-3">
                <label class="form-label fw-bold">Judul Audit </label>
                <input type="text" name="title" class="form-control" value="<?= old('title', $audit->title) ?>" required>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Periode Audit </label>
                    <select name="periode_id" class="form-select" required>
                        <option value="">-- Pilih Periode --</option>
                        <?php foreach ($periodes as $p): ?>
                            <option value="<?= $p->id ?>" <?= old('periode_id', $audit->periode_id) == $p->id ? 'selected' : '' ?>><?= esc($p->nama_periode) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Framework </label>
                    <select name="framework" class="form-select" required>
                        <option value="">-- Pilih Framework --</option>
                        <?php if (!empty($frameworks)): ?>
                            <?php foreach ($frameworks as $fw): ?>
                                <option value="<?= esc($fw->nama) ?>"
                                    <?= old('framework', $audit->framework) == $fw->nama ? 'selected' : '' ?>>
                                    <?= esc($fw->nama) ?>
                                    (<?= $fw->scoring_type === 'binary' ? 'Biner' : 'Skala 0-' . $fw->max_score ?>)
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Auditee (Unit TI) </label>
                    <select name="auditee_id" class="form-select" required>
                        <option value="">-- Pilih Auditee --</option>
                        <?php foreach ($auditees as $u): ?>
                            <option value="<?= $u->id ?>" <?= old('auditee_id', $audit->auditee_id) == $u->id ? 'selected' : '' ?>><?= esc($u->fullname) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Auditor Penanggung Jawab </label>
                    <select name="created_by_auditor" class="form-select" required>
                        <option value="">-- Pilih Auditor --</option>
                        <?php foreach ($auditors as $u): ?>
                            <option value="<?= $u->id ?>" <?= old('created_by_auditor', $audit->created_by_auditor) == $u->id ? 'selected' : '' ?>><?= esc($u->fullname) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label fw-bold">Deadline Audit </label>
                <input type="date" name="deadline" class="form-control" value="<?= old('deadline', $audit->deadline) ?>" required>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary"><i class="bi bi-save me-2"></i>Update Rencana</button>
                <a href="/admin/planning" class="btn btn-outline-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>
<?= $this->endSection() ?>