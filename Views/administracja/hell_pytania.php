<?= $this->extend('layouts/hell') ?>
<?= $this->section('title') ?>Pytania<?= $this->endSection() ?>
<?= $this->section('content') ?>

<div class="d-flex justify-content-between align-items-baseline mb-4">
  <h4 class="mb-0">Pytania</h4>
  <button class="btn btn-sm btn-primary" type="button" data-bs-toggle="collapse" data-bs-target="#formDodaj">
    + Dodaj pytanie
  </button>
</div>

<div class="collapse mb-4" id="formDodaj">
<div class="card border-0 shadow-sm">
  <div class="card-body">
    <form method="post" action="<?= site_url('AdminDash/dodajPytanie') ?>">
      <?= csrf_field() ?>
      <div class="row g-3">
        <div class="col-md-8">
          <label class="form-label">Treść pytania</label>
          <input type="text" name="tresc" class="form-control" required value="<?= old('tresc') ?>">
        </div>

        <div class="col-md-4">
          <label class="form-label">Punkty</label>
          <input type="number" name="pkt" class="form-control" value="<?= old('pkt', 3) ?>" min="1">
        </div>
        <div class="mb-3">
            <label class="form-label">Opis (opcjonalnie)</label>
            <textarea name="opis" class="form-control" rows="2"
                      placeholder="Krótkie wyjaśnienie lub kontekst pytania"><?= old('opis') ?></textarea>
        </div>

        <div class="mb-3">
            <label class="form-label">Źródło prawdy (opcjonalnie)</label>
            <input type="text" name="zrodlo" class="form-control" maxlength="255"
                   placeholder="np. FIFA.com" value="<?= old('zrodlo') ?>">
        </div>
        <div class="col-md-6">
          <label class="form-label">Prawidłowa odpowiedź <span class="text-muted">(opcjonalna)</span></label>
          <input type="text" name="odpowiedz" class="form-control" value="<?= old('odpowiedz') ?>"
                 placeholder="np. Bayern Monachium">
        </div>
        <div class="col-md-6">
          <label class="form-label">Ważne do</label>
          <input type="datetime-local" name="wazneDo" class="form-control" required
                 value="<?= old('wazneDo') ?>">
        </div>
      </div>
      <button type="submit" class="btn btn-primary mt-3">Dodaj</button>
    </form>
  </div>
</div>
</div>

<div class="card border-0 shadow-sm">
  <div class="card-body p-0">
    <table class="table table-sm table-hover mb-0">
      <thead class="table-light">
        <tr><th class="ps-3">Treść</th><th>Odpowiedź</th><th>Pkt</th><th>Ważne do</th><th>Aktywne</th><th></th></tr>
      </thead>
      <tbody>
        <?php foreach ($pytania as $p): ?>
        <tr>
          <td class="ps-3"><?= esc(mb_substr($p['tresc'],0,60)) ?><?= mb_strlen($p['tresc'])>60?'…':'' ?></td>
          <td class="small text-muted"><?= $p['odpowiedz'] ? esc($p['odpowiedz']) : '--' ?></td>
          <td><?= (int)$p['pkt'] ?></td>
          <td class="small"><?= esc(substr($p['wazneDo'],0,16)) ?></td>
          <td>
            <form method="post" action="<?= site_url('AdminDash/updateQuestionStatus') ?>">
              <?= csrf_field() ?>
              <input type="hidden" name="question_id" value="<?= (int)$p['id'] ?>">
              <input type="hidden" name="aktywne" value="<?= $p['aktywne'] ? 0 : 1 ?>">
              <button class="btn btn-sm <?= $p['aktywne'] ? 'btn-success' : 'btn-outline-secondary' ?>">
                <?= $p['aktywne'] ? '✓' : '–' ?>
              </button>
            </form>
          </td>
          <td>
            <a href="/hell/pytania/odpowiedzi/<?= (int)$p['id'] ?>" class="btn btn-sm btn-outline-primary">
              Odpowiedzi
            </a>
          </td>
        </tr>
        <?php endforeach ?>
      </tbody>
    </table>
  </div>
</div>

<?= $this->endSection() ?>
