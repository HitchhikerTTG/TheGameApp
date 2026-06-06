<!doctype html>
<html lang="pl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Poranny digest</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    <style>body { background: #f8f9fa; }</style>
</head>
<body>
<div class="container py-4" style="max-width:640px">

<?php
$session = \Config\Services::session();
$sukces  = $session->getFlashData('success');
$fail    = $session->getFlashData('fail');
?>
<?php if ($sukces): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        ✅ <?= esc($sukces) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php elseif ($fail): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        ❌ <?= esc($fail) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif ?>

<div class="d-flex justify-content-between align-items-baseline mb-4">
    <h4 class="mb-0">📧 Poranny digest</h4>
    <a href="/hell/kampanie" class="btn btn-sm btn-outline-secondary">← Kampanie</a>
</div>

<p class="text-muted small mb-4">
    Wyśle spersonalizowany email do <strong><?= (int)$activeCount ?></strong> aktywnych graczy z:
    wynikami meczów z ostatniej doby, nadchodzącymi spotkaniami i aktywnym pytaniem.
</p>

<div class="card border-0 shadow-sm">
    <div class="card-body p-4">
        <form method="post" action="/hell/digest/wyslij" id="formDigest">
            <?= csrf_field() ?>

            <div class="mb-4">
                <label class="form-label fw-semibold">
                    Komentarz admina
                    <span class="text-muted fw-normal">(opcjonalny, maks. 200 znaków)</span>
                </label>
                <input type="text" name="komentarz" class="form-control" maxlength="200"
                       placeholder="np. Dobry wieczór! Dziś wielkie mecze…">
                <div class="form-text">Pojawi się jako wyróżniony akapit na początku emaila.</div>
            </div>

            <div class="bg-light rounded p-3 small mb-4">
                <div class="row mb-1">
                    <div class="col-5 text-muted">Turniej</div>
                    <div class="col-7 fw-semibold"><?= esc($config['activeTournamentName'] ?? '—') ?></div>
                </div>
                <div class="row mb-1">
                    <div class="col-5 text-muted">Odbiorcy</div>
                    <div class="col-7">Gracze aktywnego turnieju <span class="badge bg-secondary"><?= (int)$activeCount ?></span></div>
                </div>
                <div class="row">
                    <div class="col-5 text-muted">Temat emaila</div>
                    <div class="col-7">Dzień dobry, {nick}! Co w trawce piszczy?</div>
                </div>
            </div>

            <button type="button" class="btn btn-primary w-100" onclick="openModal()">
                Wyślij digest do <?= (int)$activeCount ?> graczy →
            </button>
        </form>
    </div>
</div>

</div>

<!-- Modal: potwierdzenie -->
<div class="modal fade" id="modalPotwierdzenie" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h6 class="modal-title fw-semibold">Potwierdzenie wysyłki digestu</h6>
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

<script>
function openModal() {
    new bootstrap.Modal(document.getElementById('modalPotwierdzenie')).show();
}
function submitDigest() {
    document.getElementById('formDigest').submit();
}
</script>

</body>
</html>
