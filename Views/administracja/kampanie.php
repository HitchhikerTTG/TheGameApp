<!doctype html>
<html lang="pl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Kampanie email</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/css/bootstrap.min.css" rel="stylesheet"      crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    
    


    
    <style>
        body { background: #f8f9fa; }
        .form-check-label .badge { font-size: .7rem; vertical-align: middle; }
        #sentStatus { min-height: 1.4rem; }
    </style>
</head>
<body>
<div class="container py-4" style="max-width:800px">

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
    <h4 class="mb-0">Kampanie email</h4>
    <span class="text-muted small">Szablony: <code>public/maile/</code> · placeholder: <code>{nick}</code></span>
</div>

<?php if (empty($files)): ?>
    <div class="alert alert-warning">Brak plików <code>*.html</code> w <code>public/maile/</code>.</div>
<?php else: ?>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-4">
        <form method="post" id="formKampania">
            <?= csrf_field() ?>

            <div class="row g-3 mb-3">
                <div class="col-md-8">
                    <label class="form-label fw-semibold">Szablon HTML</label>
                    <select name="template_file" id="selectTemplate" class="form-select" required>
                        <option value="">-- wybierz --</option>
                        <?php foreach ($files as $f): ?>
                            <option value="<?= esc($f) ?>"><?= esc($f) ?></option>
                        <?php endforeach ?>
                    </select>
                    <div id="sentStatus" class="mt-1"></div>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <a href="#" id="btnPodglad" class="btn btn-sm btn-outline-secondary w-100 d-none"
                       onclick="openPodglad(event)">👁 Podgląd szablonu</a>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Temat emaila</label>
                <input type="text" name="subject" id="inputSubject" class="form-control" required
                       placeholder="np. Zaproszenie do Typera 2026">
            </div>

            <div class="mb-4">
                <label class="form-label fw-semibold">Odbiorcy</label>
                <div class="d-flex flex-column gap-2">
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="target_group"
                               value="active" id="tgActive" required>
                        <label class="form-check-label" for="tgActive">
                            Gracze aktywnego turnieju
                            <span class="badge bg-secondary"><?= (int)$activeCount ?></span>
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="target_group"
                               value="all" id="tgAll">
                        <label class="form-check-label" for="tgAll">
                            Wszyscy zarejestrowani
                            <span class="badge bg-secondary"><?= (int)$allCount ?></span>
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="target_group"
                               value="tournament" id="tgTournament">
                        <label class="form-check-label d-flex align-items-center gap-2" for="tgTournament">
                            Wybrany turniej -- ID:
                            <input type="number" name="tournament_id" id="inputTournamentId"
                                   class="form-control form-control-sm" style="width:80px" min="1">
                        </label>
                    </div>
                </div>
            </div>

            <div class="d-flex gap-2 pt-2 border-top">
                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="submitTest()">
                    📨 Wyślij testowo
                </button>
                <button type="button" class="btn btn-primary btn-sm ms-auto" onclick="openModal()">
                    Wyślij do wszystkich →
                </button>
            </div>
        </form>
    </div>
</div>

<?php endif ?>

<?php
$history = [];
foreach ($sentMap as $tpl => $groups) {
    foreach ($groups as $group => $row) {
        $history[] = $row;
    }
}
usort($history, fn($a, $b) => strcmp((string)$b['sent_at'], (string)$a['sent_at']));
$groupLabels = [
    'active' => 'Aktywny turniej',
    'all'    => 'Wszyscy',
];
?>
<?php if (!empty($history)): ?>
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-bottom fw-semibold py-3">Historia wysyłek</div>
    <div class="card-body p-0">
        <table class="table table-sm table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th class="ps-3">Szablon</th>
                    <th>Temat</th>
                    <th>Odbiorcy</th>
                    <th>Wysłano</th>
                    <th class="pe-3 text-end">Liczba</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($history as $h):
                    $dt = $h['sent_at'] ? date('d.m.Y H:i', strtotime($h['sent_at'])) : '--';
                    $group = $groupLabels[$h['target_group']] ?? $h['target_group'];
                ?>
                <tr>
                    <td class="ps-3 text-muted small"><?= esc($h['template_file']) ?></td>
                    <td><?= esc($h['subject']) ?></td>
                    <td><span class="badge bg-light text-dark border"><?= esc($group) ?></span></td>
                    <td class="small"><?= $dt ?></td>
                    <td class="pe-3 text-end">
                        <span class="badge bg-success"><?= (int)($h['recipients_count'] ?? 0) ?> os.</span>
                    </td>
                </tr>
                <?php endforeach ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif ?>

</div><!-- /container -->

<!-- Modal: podgląd -->
<div class="modal fade" id="modalPodglad" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header py-2">
                <span class="fw-semibold small">Podgląd: <span id="podgladNazwa"></span></span>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0" style="height:80vh">
                <iframe id="podgladIframe" src="" style="width:100%;height:100%;border:none"></iframe>
            </div>
        </div>
    </div>
</div>

<!-- Modal: potwierdzenie wysyłki -->
<div class="modal fade" id="modalPotwierdzenie" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h6 class="modal-title fw-semibold">Potwierdzenie wysyłki</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="bg-light rounded p-3 small">
                    <div class="row mb-1">
                        <div class="col-4 text-muted">Szablon</div>
                        <div class="col-8 fw-semibold" id="mTemplate"></div>
                    </div>
                    <div class="row mb-1">
                        <div class="col-4 text-muted">Temat</div>
                        <div class="col-8" id="mSubject"></div>
                    </div>
                    <div class="row">
                        <div class="col-4 text-muted">Odbiorcy</div>
                        <div class="col-8" id="mTarget"></div>
                    </div>
                </div>
                <p class="text-danger small mt-3 mb-0">⚠ Tej akcji nie można cofnąć.</p>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-sm btn-outline-secondary"
                        data-bs-dismiss="modal">Anuluj</button>
                <button type="button" class="btn btn-sm btn-primary"
                        onclick="submitSend()">Wyślij →</button>
            </div>
        </div>
    </div>
</div>

<script>
const sentMap     = <?= json_encode($sentMap) ?>;
const activeCount = <?= (int)$activeCount ?>;
const allCount    = <?= (int)$allCount ?>;
const groupLabels = { active: 'Aktywny turniej (' + activeCount + ')', all: 'Wszyscy (' + allCount + ')' };

document.getElementById('selectTemplate').addEventListener('change', function () {
    const tpl = this.value;
    const btn = document.getElementById('btnPodglad');
    const status = document.getElementById('sentStatus');

    btn.classList.toggle('d-none', !tpl);

    if (tpl && sentMap[tpl]) {
        const groups = Object.keys(sentMap[tpl])
            .filter(g => sentMap[tpl][g].sent_at)
            .map(g => groupLabels[g] || g);
        status.innerHTML = groups.length
            ? '<span class="text-warning small">⚠ Wysłano już do: ' + groups.join(', ') + '</span>'
            : '';
    } else {
        status.innerHTML = '';
    }
});

document.querySelectorAll('input[name="target_group"]').forEach(r => {
    r.addEventListener('change', function () {
        if (this.value === 'tournament') {
            document.getElementById('inputTournamentId').focus();
        }
    });
});

function validate() {
    const tpl  = document.getElementById('selectTemplate').value;
    const subj = document.getElementById('inputSubject').value.trim();
    const tg   = document.querySelector('input[name="target_group"]:checked');
    if (!tpl || !subj || !tg) {
        alert('Uzupełnij szablon, temat i odbiorców.');
        return null;
    }
    return { tpl, subj, tg: tg.value };
}

function submitTest() {
    if (!validate()) return;
    document.getElementById('formKampania').action = '/hell/kampanie/test';
    document.getElementById('formKampania').submit();
}

function openPodglad(e) {
    e.preventDefault();
    const tpl = document.getElementById('selectTemplate').value;
    if (!tpl) return;
    document.getElementById('podgladNazwa').textContent = tpl;
    document.getElementById('podgladIframe').src = '/maile/' + tpl;
    new bootstrap.Modal(document.getElementById('modalPodglad')).show();
}

function openModal() {
    const v = validate();
    if (!v) return;
    const tid    = document.getElementById('inputTournamentId').value;
    const tGroup = v.tg === 'tournament' ? 'tournament_' + tid : v.tg;

    if (sentMap[v.tpl]?.[tGroup]?.sent_at) {
        if (!confirm('Ta kampania była już wysłana (' + sentMap[v.tpl][tGroup].sent_at + ').\nWysłać jeszcze raz?')) return;
    }

    document.getElementById('mTemplate').textContent = v.tpl;
    document.getElementById('mSubject').textContent  = v.subj;
    document.getElementById('mTarget').textContent   = groupLabels[v.tg] ?? ('Turniej ID ' + tid);
    new bootstrap.Modal(document.getElementById('modalPotwierdzenie')).show();
}

function submitSend() {
    document.getElementById('formKampania').action = '/hell/kampanie/wyslij';
    document.getElementById('formKampania').submit();
}
</script>

</body>
</html>
