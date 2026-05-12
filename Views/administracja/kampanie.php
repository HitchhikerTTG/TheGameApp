<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>A po maturze, chodziłem wysyłać maile</title>
    <link href="<https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/css/bootstrap.min.css>" rel="stylesheet" integrity="sha384-Zenh87qX5JnK2Jl0vWa8Ck2rdkQ2Bzep5IDxbcnCeuOxjzrPF/et3URy9Bv1WTRi" crossorigin="anonymous">
    <script src="<https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js>" integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p" crossorigin="anonymous"></script>
</head>




<body class="p-4">

<?php
$session = \Config\Services::session();
$sukces  = $session->getFlashData('success');
$fail    = $session->getFlashData('fail');
?>
<?php if ($sukces): ?>
  <div class="alert alert-success"><?= esc($sukces) ?></div>
<?php elseif ($fail): ?>
  <div class="alert alert-danger"><?= esc($fail) ?></div>
<?php endif ?>

<h2 class="mb-4">📧 Kampanie email</h2>
<p class="text-muted">Szablony HTML wrzuć do <code>public/maile/</code>. Używaj <code>{nick}</code> jako placeholderu.</p>

<?php if (empty($files)): ?>
  <div class="alert alert-warning">Brak plików <code>*.html</code> w <code>public/maile/</code>.</div>
<?php else: ?>

<div class="card mb-4">
  <div class="card-header fw-bold">Wyślij kampanię</div>
  <div class="card-body">
    <form method="post" id="formKampania">
      <?= csrf_field() ?>

      <div class="mb-3">
        <label class="form-label fw-bold">Szablon HTML</label>
        <select name="template_file" id="selectTemplate" class="form-select" required>
          <option value="">-- wybierz plik --</option>
          <?php foreach ($files as $f): ?>
            <option value="<?= esc($f) ?>"><?= esc($f) ?></option>
          <?php endforeach ?>
        </select>
      </div>

      <div class="mb-3">
        <label class="form-label fw-bold">Temat emaila</label>
        <input type="text" name="subject" id="inputSubject" class="form-control" required
               placeholder="np. Zaproszenie do Typera 2026">
      </div>

      <div class="mb-3">
        <label class="form-label fw-bold">Odbiorcy</label>
        <div class="form-check">
          <input class="form-check-input" type="radio" name="target_group" value="active" id="tgActive" required>
          <label class="form-check-label" for="tgActive">
            Gracze aktywnego turnieju
            <span class="badge bg-secondary"><?= (int)$activeCount ?></span>
          </label>
        </div>
        <div class="form-check">
          <input class="form-check-input" type="radio" name="target_group" value="all" id="tgAll">
          <label class="form-check-label" for="tgAll">
            Wszyscy zarejestrowani
            <span class="badge bg-secondary"><?= (int)$allCount ?></span>
          </label>
        </div>
        <div class="form-check">
          <input class="form-check-input" type="radio" name="target_group" value="tournament" id="tgTournament">
          <label class="form-check-label" for="tgTournament">
            Wybrany turniej -- ID:
            <input type="number" name="tournament_id" id="inputTournamentId"
                   class="form-control form-control-sm d-inline-block ms-1" style="width:80px" min="1">
          </label>
        </div>
      </div>

      <div class="d-flex gap-2 mt-3">
        <button type="button" class="btn btn-outline-secondary" onclick="submitTest()">
          📨 Wyślij testowo (na wit@nirski.com)
        </button>
        <button type="button" class="btn btn-success" onclick="openModal()">
          🚀 Wyślij do wszystkich
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
?>
<?php if (!empty($history)): ?>
<div class="card">
  <div class="card-header fw-bold">Historia wysyłek</div>
  <div class="card-body p-0">
    <table class="table table-sm mb-0">
      <thead class="table-light">
        <tr>
          <th>Szablon</th><th>Temat</th><th>Odbiorcy</th>
          <th>Data wysyłki</th><th>Wysłano do</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($history as $h): ?>
        <tr>
          <td><?= esc($h['template_file']) ?></td>
          <td><?= esc($h['subject']) ?></td>
          <td><?= esc($h['target_group']) ?></td>
          <td><?= esc($h['sent_at'] ?? '--') ?></td>
          <td><?= (int)($h['recipients_count'] ?? 0) ?> os.</td>
        </tr>
        <?php endforeach ?>
      </tbody>
    </table>
  </div>
</div>
<?php endif ?>

<!-- Modal potwierdzenia -->
<div class="modal fade" id="modalPotwierdzenie" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Potwierdź wysyłkę</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <p><strong>Szablon:</strong> <span id="mTemplate"></span></p>
        <p><strong>Temat:</strong> <span id="mSubject"></span></p>
        <p><strong>Odbiorcy:</strong> <span id="mTarget"></span></p>
        <p class="text-danger fw-bold mt-3">⚠️ Tej akcji nie można cofnąć.</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Anuluj</button>
        <button type="button" class="btn btn-success" onclick="submitSend()">Wyślij →</button>
      </div>
    </div>
  </div>
</div>

<script>
const sentMap     = <?= json_encode($sentMap) ?>;
const activeCount = <?= (int)$activeCount ?>;
const allCount    = <?= (int)$allCount ?>;

function validate() {
    const tpl  = document.getElementById('selectTemplate').value;
    const subj = document.getElementById('inputSubject').value.trim();
    const tg   = document.querySelector('input[name="target_group"]:checked');
    if (!tpl || !subj || !tg) { alert('Uzupełnij szablon, temat i odbiorców.'); return null; }
    return { tpl, subj, tg: tg.value };
}

function submitTest() {
    if (!validate()) return;
    document.getElementById('formKampania').action = '/hell/kampanie/test';
    document.getElementById('formKampania').submit();
}

function openModal() {
    const v = validate();
    if (!v) return;
    const tid    = document.getElementById('inputTournamentId').value;
    const tGroup = v.tg === 'tournament' ? 'tournament_' + tid : v.tg;

    if (sentMap[v.tpl] && sentMap[v.tpl][tGroup] && sentMap[v.tpl][tGroup].sent_at) {
        alert('Ta kampania (' + v.tpl + ' → ' + tGroup + ') została już wysłana dnia '
              + sentMap[v.tpl][tGroup].sent_at + '!');
        return;
    }

    const labels = {
        active:     'Gracze aktywnego turnieju (' + activeCount + ')',
        all:        'Wszyscy zarejestrowani (' + allCount + ')',
        tournament: 'Turniej ID ' + tid
    };
    document.getElementById('mTemplate').textContent = v.tpl;
    document.getElementById('mSubject').textContent  = v.subj;
    document.getElementById('mTarget').textContent   = labels[v.tg] || tGroup;

    new bootstrap.Modal(document.getElementById('modalPotwierdzenie')).show();
}

function submitSend() {
    document.getElementById('formKampania').action = '/hell/kampanie/wyslij';
    document.getElementById('formKampania').submit();
}
</script>

</body>
</html>
