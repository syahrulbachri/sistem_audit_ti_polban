<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<h4 class="fw-bold mb-4" style="color: var(--primary-navy);"><?= $page_title ?></h4>

<div class="card border-0 shadow-sm" style="border-radius: 12px; max-width: 800px;">
    <div class="card-body p-4">
        <?php if(session()->getFlashdata('errors')): ?>
            <div class="alert alert-danger"><ul class="mb-0"><?php foreach(session()->getFlashdata('errors') as $error): ?><li><?= $error ?></li><?php endforeach; ?></ul></div>
        <?php endif; ?>

        <form action="/admin/questions/update/<?= $question->id ?>" method="post">
            <?= csrf_field() ?>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Framework</label>
                    <select name="framework" class="form-select" required>
                        <option value="">-- Pilih Framework --</option>
                        <?php if(!empty($frameworks)): ?>
                            <?php foreach($frameworks as $fw): ?>
                                <option value="<?= esc($fw->nama) ?>" 
                                        <?= old('framework', $question->framework) == $fw->nama ? 'selected' : '' ?>>
                                    <?= esc($fw->nama) ?> 
                                    (<?= $fw->scoring_type === 'binary' ? 'Biner' : 'Skala 0-' . $fw->max_score ?>)
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Kode Klausul <span class="text-muted fw-normal">(Opsional)</span></label>
                    <input type="text" name="clause_code" class="form-control" 
                           value="<?= old('clause_code', $question->clause_code ?? '') ?>" 
                           placeholder="Contoh: 5.1 atau A.8.1.1">
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label fw-bold">Teks Pertanyaan</label>
                <textarea name="question_text" class="form-control" rows="4" required 
                          placeholder="Tulis pertanyaan audit di sini..."><?= old('question_text', $question->question_text ?? '') ?></textarea>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary"><i class="bi bi-save me-2"></i>Update</button>
                <a href="/admin/questions" class="btn btn-outline-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>
<?= $this->endSection() ?>