<?= $this->extend('layouts/hell') ?>
<?= $this->section('title') ?>Poranny digest<?= $this->endSection() ?>
<?= $this->section('content') ?>

<div class="row justify-content-center">
<div class="col-lg-7">

<div class="d-flex justify-content-between align-items-baseline mb-4">
    <h4 class="mb-0">📧 Poranny digest</h4>
    <a href="/hell/kampanie" class="btn btn-sm btn-outline-secondary">← Kampanie</a>
</div>

<p class="text-muted small mb-4">
    Wyśle spersonalizowany email do <strong><?= (int)$activeCount ?></strong> aktywnych graczy z:
    wynikami meczów z ostatniej doby, nadchodzącymi spotkaniami (24h) i aktywnym pytaniem.
</p>

<div class="card border-0 shadow-sm">
    <div class="card-body p-4">
        <form method="post" action="/hell/digest/wyslij" id="formDigest">
            <?= csrf_field() ?>

            <div class="mb-3">
                <label class="form-label fw-semibold">
                    Temat emaila
                    <span class="text-muted fw-normal small">-- użyj <code>{nick}</code> jako placeholder</span>
                </label>
                <input type="text" name="subject" class="form-control" maxlength="200"
                       value="Dzień dobry, {nick}! Co w trawce piszczy?"
                       required>
            </div>

            <div class="mb-4">
                <label class="form-label fw-semibold">
                    Komentarz admina
                    <span class="text-muted fw-normal">(opcjonalny, maks. 200 znaków)</span>
                </label>
                <input type="text" name="komentarz" class="form-control" maxlength="200"
                       placeholder="np. Wieczór pełen emocji! Dziś mecz Polska–Niemcy…">
                <div class="form-text">Pojawi się jako wyróżniony akapit na początku emaila.</div>
            </div>
            
            <div class="mb-4">
                <label class="form-label fw-semibold">
                    Komentarz admina
                    <span class="text-muted fw-normal">(opcjonalny, maks. 200 znaków)</span>
                </label>
                <input type="text" name="komentarzPytanie" class="form-control" maxlength="200"
                       placeholder="A tu coś o pytaniy">
                <div class="form-text">Pojawi się jako dodatek przed pytaniem.</div>
            </div>
            <?php if (!empty($pytaniaArchiwalne)): ?>
<div class="mb-4">
    <label class="form-label fw-semibold">
        Wczorajsze pytanie(a)
        <span class="text-muted fw-normal small">(opcjonalne -- wyniki zostaną pokazane pod meczami)</span>
    </label>
    <?php foreach ($pytaniaArchiwalne as $p): ?>
    <div class="form-check">
        <input class="form-check-input" type="checkbox"
               name="pytaniaWczoraj[]"
               value="<?= (int)$p['id'] ?>"
               id="pw<?= (int)$p['id'] ?>">
        <label class="form-check-label small" for="pw<?= (int)$p['id'] ?>">
            <?= esc($p['tresc']) ?>
            <span class="text-muted">(<?= esc(substr($p['wazneDo'], 0, 10)) ?>, <?= (int)$p['pkt'] ?> pkt)</span>
        </label>
    </div>
    <?php endforeach ?>
</div>
<?php endif ?>

<?php if (!empty($pytaniaAktywne)): ?>
<div class="mb-4">
    <label class="form-label fw-semibold">
        Dzisiejsze pytanie(a)
        <span class="text-muted fw-normal small">(opcjonalne -- aktywne, gracze mogą jeszcze odpowiedzieć)</span>
    </label>
    <?php foreach ($pytaniaAktywne as $p): ?>
    <div class="form-check">
        <input class="form-check-input" type="checkbox"
               name="pytaniaDzisiaj[]"
               value="<?= (int)$p['id'] ?>"
               id="pd<?= (int)$p['id'] ?>"
               <?= $p['aktywne'] ? 'checked' : '' ?>>
        <label class="form-check-label small" for="pd<?= (int)$p['id'] ?>">
            <?= esc($p['tresc']) ?>
            <span class="text-muted">(ważne do <?= esc(substr($p['wazneDo'], 0, 10)) ?>, <?= (int)$p['pkt'] ?> pkt)</span>
        </label>
    </div>
    <?php endforeach ?>
</div>
<?php endif ?>
            <div class="mb-4">
                <label class="form-label fw-semibold">
                    Komentarz admina
                    <span class="text-muted fw-normal">(opcjonalny, maks. 200 znaków)</span>
                </label>
                <input type="text" name="komentarzClosing" class="form-control" maxlength="200"
                       placeholder="czyli co podkreślisz?">
                <div class="form-text">Co masz do powiedzenia na zakoczenie?</div>
            </div>

            <div class="bg-light rounded p-3 small mb-4">
                <div class="row mb-1">
                    <div class="col-5 text-muted">Turniej</div>
                    <div class="col-7 fw-semibold"><?= esc($config['activeTournamentName'] ?? '--') ?></div>
                </div>
                <div class="row mb-1">
                    <div class="col-5 text-muted">Odbiorcy</div>
                    <div class="col-7">Aktywni gracze <span class="badge bg-secondary"><?= (int)$activeCount ?></span></div>
                </div>
            </div>

            <button type="button" class="btn btn-primary w-100" onclick="openModal()">
                Wyślij digest do <?= (int)$activeCount ?> graczy →
            </button>
        </form>
    </div>
</div>

</div>
</div>

<?= $this->endSection() ?>
<?= $this->section('modals') ?>

<div class="modal fade" id="modalPotwierdzenie" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h6 class="modal-title fw-semibold">Potwierdzenie wysyłki</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="small mb-2">Wyślesz <strong><?= (int)$activeCount ?></strong> spersonalizowanych emaili.</p>
                <p class="text-danger small mb-0">⚠ Tej akcji nie można cofnąć.</p>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Anuluj</button>
                <button type="button" class="btn btn-sm btn-primary" onclick="submitDigest()">Wyślij →</button>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script>
function openModal() { new bootstrap.Modal(document.getElementById('modalPotwierdzenie')).show(); }
function submitDigest() { document.getElementById('formDigest').submit(); }
</script>
<?= $this->endSection() ?>
