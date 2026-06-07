<?php
$validation = \Config\Services::validation();
$activeConfig = get_active_tournament_config();
?>

<hr class="my-4">
<h2>Nowa notatka / ogłoszenie</h2>
<p class="text-muted">Turniej: <strong><?= esc($activeConfig['activeTournamentName']) ?></strong></p>

<div class="row">
  <div class="col-md-6">
    <form method="post" action="<?= site_url('AdminDash/dodajNotatke') ?>">
      <?= csrf_field() ?>

      <div class="mb-3">
        <label for="notatka-tresc" class="form-label">Treść (markdown)</label>
        <textarea id="notatka-tresc" name="tresc" rows="6"
                  class="form-control font-monospace"
                  placeholder="## Tytuł&#10;&#10;Treść ogłoszenia w **markdown**…"
                  oninput="notatkiPreview()"><?= old('tresc') ?></textarea>
        <?php if ($validation->hasError('tresc')): ?>
          <div class="alert alert-danger mt-1"><?= $validation->getError('tresc') ?></div>
        <?php endif; ?>
      </div>

      <div class="mb-3">
        <label for="notatka-klub" class="form-label">Do kogo?</label>
        <select id="notatka-klub" name="KlubID" class="form-select">
          <option value="">Wszyscy</option>
          <?php foreach ($allKluby as $klub): ?>
            <option value="<?= (int)$klub['id'] ?>"><?= esc($klub['Nazwa']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="mb-3 form-check">
        <input type="checkbox" class="form-check-input" id="notatka-pub"
               name="opublikowana" value="1" checked>
        <label class="form-check-label" for="notatka-pub">Opublikuj od razu</label>
      </div>

      <button type="submit" class="btn btn-primary">Dodaj notatkę</button>
    </form>
  </div>

  <div class="col-md-6">
    <label class="form-label">Podgląd</label>
    <div id="notatka-preview"
         class="border rounded p-3"
         style="min-height:140px;background:var(--bs-body-bg);">
      <em class="text-muted">Podgląd pojawi się tu…</em>
    </div>
  </div>
</div>

<?php if (!empty($notatki)): ?>
<h4 class="mt-4">Ostatnie notatki (aktywny turniej)</h4>
<table class="table table-sm">
  <thead>
    <tr>
      <th>#</th><th>Data</th><th>Klub</th><th>Fragment</th><th>Pub.</th><th></th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($notatki as $n):
      $klubNazwa = '--';
      if (!empty($n['KlubID'])) {
          foreach ($allKluby as $k) {
              if ((int)$k['id'] === (int)$n['KlubID']) { $klubNazwa = esc($k['Nazwa']); break; }
          }
      } else {
          $klubNazwa = '<em>Wszyscy</em>';
      }
    ?>
    <tr>
      <td><?= (int)$n['id'] ?></td>
      <td><?= esc(substr($n['created_at'], 0, 16)) ?></td>
      <td><?= $klubNazwa ?></td>
      <td><?= esc(mb_substr($n['tresc'], 0, 60)) ?>…</td>
      <td><?= $n['opublikowana'] ? '✓' : '–' ?></td>
      <td>
        <?php if ($n['opublikowana']): ?>
        <form method="post" action="<?= site_url('AdminDash/ukryjNotatke/' . (int)$n['id']) ?>"
              style="display:inline;">
          <?= csrf_field() ?>
          <button type="submit" class="btn btn-sm btn-outline-secondary"
                  onclick="return confirm('Ukryć tę notatkę?')">Ukryj</button>
        </form>
        <?php endif; ?>
      </td>
    </tr>
    <?php endforeach; ?>
  </tbody>
</table>
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/marked@9/marked.min.js"></script>
<script>
function notatkiPreview() {
  var src = document.getElementById('notatka-tresc').value;
  document.getElementById('notatka-preview').innerHTML =
    src.trim()
      ? marked.parse(src)
      : '<em class="text-muted">Podgląd pojawi się tu…</em>';
}
</script>
