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
    Wyśle spersonalizowany email do <strong><?= (int)$activeCount ?></strong> graczy z opt-in,
    z wynikami meczów, pytaniami i statystykami.
    <?php if (!empty($szkic['savedAt'])): ?>
        <span class="ms-2 badge bg-light text-secondary border">
            Szkic z <?= esc(date('d.m H:i', strtotime($szkic['savedAt']))) ?>
        </span>
    <?php endif ?>
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
                       value="<?= esc($szkic['subject'] ?? 'Dzień dobry, {nick}! Co w trawce piszczy?') ?>"
                       required>
            </div>

            <div class="mb-4">
                <label class="form-label fw-semibold">
                    Komentarz otwierający
                    <span class="text-muted fw-normal small">(opcjonalny)</span>
                </label>
                <input type="text" name="komentarz" class="form-control" maxlength="300"
                       value="<?= esc($szkic['komentarz'] ?? '') ?>"
                       placeholder="np. Wieczór pełen emocji! Dziś mecz Polska–Niemcy…">
                <div class="form-text">Wyróżniony akapit na początku emaila.</div>
            </div>

            <div class="mb-4">
                <label class="form-label fw-semibold">
                    Komentarz przed pytaniami
                    <span class="text-muted fw-normal small">(opcjonalny)</span>
                </label>
                <input type="text" name="komentarzPytanie" class="form-control" maxlength="300"
                       value="<?= esc($szkic['komentarzPytanie'] ?? '') ?>"
                       placeholder="Coś o pytaniu dnia…">
            </div>

            <?php if (!empty($pytaniaArchiwalne)): ?>
            <div class="mb-4">
                <label class="form-label fw-semibold">
                    Wczorajsze pytanie(a)
                    <span class="text-muted fw-normal small">(wyniki pokażą się pod meczami)</span>
                </label>
                <?php foreach ($pytaniaArchiwalne as $p): ?>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox"
                           name="pytaniaWczoraj[]"
                           value="<?= (int)$p['id'] ?>"
                           id="pw<?= (int)$p['id'] ?>"
                           <?= in_array($p['id'], $szkic['pytaniaWczoraj'] ?? []) ? 'checked' : '' ?>>
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
                    <span class="text-muted fw-normal small">(aktywne, gracze mogą jeszcze odpowiadać)</span>
                </label>
                <?php foreach ($pytaniaAktywne as $p): ?>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox"
                           name="pytaniaDzisiaj[]"
                           value="<?= (int)$p['id'] ?>"
                           id="pd<?= (int)$p['id'] ?>"
                           <?= (isset($szkic['pytaniaDzisiaj']) ? in_array($p['id'], $szkic['pytaniaDzisiaj']) : $p['aktywne']) ? 'checked' : '' ?>>
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
                    Komentarz zamykający
                    <span class="text-muted fw-normal small">(opcjonalny)</span>
                </label>
                <input type="text" name="komentarzClosing" class="form-control" maxlength="300"
                       value="<?= esc($szkic['komentarzClosing'] ?? '') ?>"
                       placeholder="Co podkreślisz na zakończenie?">
            </div>

            <div class="bg-light rounded p-3 small mb-4">
                <div class="row mb-1">
                    <div class="col-5 text-muted">Turniej</div>
                    <div class="col-7 fw-semibold"><?= esc($config['activeTournamentName'] ?? '--') ?></div>
                </div>
                <div class="row">
                    <div class="col-5 text-muted">Odbiorcy</div>
                    <div class="col-7">Aktywni gracze <span class="badge bg-secondary"><?= (int)$activeCount ?></span></div>
                </div>
            </div>

            <!-- Przyciski akcji -->
            <div class="d-flex gap-2 flex-wrap">
                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="zapiszSzkic()">
                    💾 Zapisz szkic
                </button>
                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="openPodglad()">
                    👁 Podgląd
                </button>
                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="wyslijTest()">
                    📨 Test do mnie
                </button>
                <button type="button" class="btn btn-primary btn-sm ms-auto" onclick="openModal()">
                    Wyślij do <?= (int)$activeCount ?> graczy →
                </button>
            </div>
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
const form = document.getElementById('formDigest');

function zapiszSzkic() {
    form.action = '/hell/digest/szkic';
    form.target = '_self';
    form.submit();
}

function openPodglad() {
    form.action = '/hell/digest/podglad';
    form.target = '_blank';
    form.submit();
    // przywróć domyślne żeby inne przyciski działały poprawnie
    setTimeout(() => { form.action = '/hell/digest/wyslij'; form.target = '_self'; }, 300);
}

function wyslijTest() {
    form.action = '/hell/digest/test';
    form.target = '_self';
    form.submit();
}

function openModal() {
    form.action = '/hell/digest/wyslij';
    form.target = '_self';
    new bootstrap.Modal(document.getElementById('modalPotwierdzenie')).show();
}

function submitDigest() { form.submit(); }
</script>
<?= $this->endSection() ?>
