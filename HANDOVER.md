# Przekazanie projektu — Typer piłkarski (TheGameApp)

**Data sporządzenia:** 2026-06-05  
**Gałąź robocza:** `claude/review-staging-branch-3BwW3`  
**Źródło wiedzy:** gałąź `staging` (origin/staging)

---

## 1. Identyfikacja projektu

| Pole | Wartość |
|------|---------|
| Nazwa | TheGameApp / „Typer piłkarski" |
| Produkcja | https://typowanie.jakiwynik.com |
| Staging | https://staging.jakiwynik.com |
| Repozytorium | hitchhikerttg/thegameapp |
| Serwer | cPanel, `/home/remedium/winiwoni/app/` |
| Baza danych | MySQL, baza: `remedium_ci1` |
| Framework | CodeIgniter 4 (^4.7.2) |
| PHP | 8.1+ |

---

## 2. Stack techniczny

- **Backend:** CodeIgniter 4 MVC, PHP 8.1+
- **Frontend:** Bootstrap 5.3.3, jQuery 3.6.0, Google Fonts (Bebas Neue, DM Sans)
- **Baza danych:** MySQL przez MySQLi
- **Email:** Postmark (własna biblioteka `Libraries/Postmark.php`)
- **Cache:** pliki JSON na dysku (`WRITEPATH`)
- **API zewnętrzne:** livescore-api.com (14 500 req/dzień)
- **Deploy:** cPanel + `.cpanel.yml`; wsparcie Replit (`.replit`)

Zmienne środowiskowe:
- `$_ENV['lskey']` — klucz LiveScore API
- `$_ENV['lsscr']` — sekret LiveScore API

---

## 3. Kluczowe pliki i ich role

```
Controllers/
  TheGame.php          ← strona główna, archiwum, typowanie
  AdminDash.php        ← panel admina (/hell)
  Serwisant.php        ← narzędzia techniczne admina (zamykanie meczów, przeliczanie)
  LiveScore.php        ← wrapper LiveScore API
  Auth.php             ← rejestracja, logowanie, reset hasła

Services/
  MeczService.php      ← orkiestracja danych meczowych (główna logika)

Models/
  TerminarzModel.php   ← tabela `terminarz` (mecze)
  TypyModel.php        ← tabela `typy` (predykcje użytkowników)
  UserModel.php        ← tabela `uzytkownicy`
  TabelaModel.php      ← tabela `tabela` (ranking)
  PytaniaModel.php     ← tabela `pytania` (quiz)

Views/ukladanka/sg/
  znowumecze.php       ← główny komponent listy meczów z typowaniem
  zakonczoneMecze.php  ← archiwum zakończonych meczów
  SkryptTypowania.php  ← jQuery: stepper, złota piłka, AJAX submit, collapse

Config/
  Routes.php           ← wszystkie routy (jawne, z filtrem authcheck)
```

---

## 4. Struktura danych — pliki JSON (cache)

| Ścieżka | Zawartość | Kiedy tworzone/aktualizowane |
|---------|-----------|------------------------------|
| `WRITEPATH/mecze/{turniejID}/{ApiID}.json` | Dane meczu: drużyny, czas, status, wynik, kurs | `MeczService::manageJsonFiles()` → gdy starsze niż 1 dzień |
| `WRITEPATH/typy/{Id}.json` | Zagregowane typy graczy dla meczu: types[], summary, zakonczone | `MeczService::wygenerujTypyDlaMeczu()` → gdy mecz rozpoczęty |
| `WRITEPATH/odpowiedzi/{id}.json` | Odpowiedzi graczy na pytanie | `TheGame::wygenerujOdpowiedziNaPytanie()` |
| `WRITEPATH/ActiveTournament.json` | Konfiguracja aktywnego turnieju: id, name, competitionId | `AdminDash::zmienAktywnyTurniej()` |
| `WRITEPATH/api_counter.json` | Licznik wywołań API (per dzień) | `LiveScore::logApiRequest()` ← *do dodania* |
| `WRITEPATH/live_throttle_{turniejID}.json` | Timestamp ostatniego live update | `MeczService::odswiezLiveMecze()` ← *do dodania* |

---

## 5. Kluczowe flow danych

### 5a. Strona główna — `GET /`

```
TheGame::testIndex()
  └─ MeczService::meczeUzytkownikaWTurnieju($user, $turniejID, $compID, "najblizsze")
      ├─ TerminarzModel::getNajblizszeMecze()         → lista ID meczów
      ├─ manageJsonFiles()                             → tworzy/odświeża JSON meczu jeśli >1 dzień
      ├─ getUserTypesForMatches()                      → dodaje typy usera
      └─ foreach: czyRozpoczety() + wygenerujTypyDlaMeczu() jeśli potrzeba
  └─ foreach mecz: czytaj JSON meczu + JSON typów
  └─ widok: znowumecze.php
```

### 5b. Zamknięcie meczu przez admina

```
Admin → GET /serwisant/zapiszWynikMeczu     ← wyświetla formularz z listą meczów
Admin → POST /serwisant/zapiszWynikMeczu    ← zapisuje ScoreHome/ScoreAway + zakonczony=1
  └─ redirect → /przeliczMecz/{id}
      └─ Serwisant::policzPunktyDlaMeczu($id)
          ├─ oblicz punkty per user (1 za kierunek, 3 za dokładny)
          ├─ update tabela `typy` (pole `pkt`)
          └─ updateJsonFile() → aktualizuje mecze/{TurniejID}/{ApiID}.json (status=Zakonczony, wyniki)
```

### 5c. Live score (po wdrożeniu `odswiezLiveMecze`)

```
Każde wejście na / lub /wszystkieMecze:
  MeczService::meczeUzytkownikaWTurnieju()
    └─ odswiezLiveMecze() ← uruchamia się max co 2 min (throttle)
        ├─ LiveScore::getLivescoresSimple() → API: scores/live.json
        ├─ LiveScore::getHistory()          → API: scores/history.json
        └─ dla każdego rozpoczętego meczu: aktualizuje JSON z wynikiem i minutą
```

---

## 6. Stany meczu (4-state model)

| Stan | Warunek | Badge | Wyświetlanie |
|------|---------|-------|--------------|
| `$isUpcoming` | `Rozpoczety=0` AND `zakonczony=0` | „Przyjmuje typy" | Formularz z stepperem + złota piłka |
| `$isLive` | `Rozpoczety=1` AND status ≠ Zakonczony AND `zakonczony=0` | „Na żywo" | Wynik 0:0+ z animacją, minuta |
| `$isFinished` | status = Zakonczony (API) AND `zakonczony=0` | „Zakończony / +X pkt" | Wynik + typ gracza |
| `$isScored` | `zakonczony=1` (DB, admin zatwierdził) | „Przeliczony / +X pkt" | Wynik + typ gracza + punkty |

Kolumny DB:
- `terminarz.Rozpoczety` (0/1) — ustawiany automatycznie przez `czyRozpoczety()` przy pierwszej wizycie po godzinie meczu
- `terminarz.zakonczony` (0/1) — ustawiany przez admina w `zapiszWynikMeczu`

---

## 7. Zmiany proponowane — nieprzdrożone

> Wszystkie poniższe zmiany zostały zaproponowane w trakcie sesji ale **nie są jeszcze w kodzie na staging**.  
> Należy wdrożyć je ręcznie na serwerze lub przez commit.

---

### 7a. `Models/TerminarzModel.php`

**Zmiana 1 — `czyRozpoczety()` (linia 214):** zastąpić całą metodę wersją z auto-detekcją:

```php
public function czyRozpoczety($gameID) {
    $result = $this->where('Id', $gameID)->select('Rozpoczety, Date, Time')->first();
    if (!$result) return null;
    if ($result['Rozpoczety'] == 1) return 1;
    $matchTime = strtotime($result['Date'] . ' ' . $result['Time'] . ' UTC');
    if (time() > $matchTime) {
        $this->update($gameID, ['Rozpoczety' => 1]);
        return 1;
    }
    return 0;
}
```

**Zmiana 2 — `getMeczeByDateAndTurniejId()` (linia 137):**
```php
// PRZED:
$query->select('Id, ApiID');
// PO:
$query->select('Id, ApiID, HomeID, AwayID, zakonczony');
```

**Zmiana 3 — `getMeczeNajblizszegoDniaByTurniejId()` (linia 155):**
```php
// PRZED:
$query->select('Id, ApiID');
// PO:
$query->select('Id, ApiID, HomeID, AwayID, zakonczony');
```

**Zmiana 4 — `getMeczeDoRozegrania()` (linia 170):**
```php
// PRZED:
$query->select('Id, ApiID,Date,Time');
// PO:
$query->select('Id, ApiID, Date, Time, HomeID, AwayID, zakonczony');
```

---

### 7b. `Controllers/LiveScore.php`

Dodać po metodzie `getLivescores()` (linia 28):

```php
public function getLivescoresSimple($params = []) {
    $url = $this->_buildUrl('scores/live.json', $params);
    $data = $this->_makeRequest($url);
    return $data['match'] ?? [];
}

public function getHistory($params = []) {
    $url = $this->_buildUrl('scores/history.json', $params);
    $data = $this->_makeRequest($url);
    return $data['match'] ?? [];
}

public function logApiRequest(): void {
    $file = WRITEPATH . 'api_counter.json';
    $today = date('Y-m-d');
    $data = file_exists($file) ? (json_decode(file_get_contents($file), true) ?? []) : [];
    $data[$today] = ($data[$today] ?? 0) + 1;
    file_put_contents($file, json_encode($data));
}
```

W metodzie `_makeRequest()` (linia 48) dodać na początku ciała:
```php
$this->logApiRequest();
```

---

### 7c. `Services/MeczService.php`

**Zmiana 1 — fix `max([])` crash (linia 513):**
```php
// PRZED:
$mostPopularType = array_search(max($typeCounts), $typeCounts);
$mostPopularTypeCount = $typeCounts[$mostPopularType];

// PO:
if (!empty($typeCounts)) {
    $mostPopularType = array_search(max($typeCounts), $typeCounts);
    $mostPopularTypeCount = $typeCounts[$mostPopularType];
} else {
    $mostPopularType = 'Brak typów';
    $mostPopularTypeCount = 0;
}
```

**Zmiana 2 — wywołanie live refresh (linia 118, przed `return $wypelniona_lista;`):**
```php
if (in_array($filtr, ['najblizsze', 'do_rozegrania'])) {
    $this->odswiezLiveMecze($wypelniona_lista, $turniejID, $zewnetrznyTurniejID);
}
return $wypelniona_lista;
```

**Zmiana 3 — nowe metody prywatne (przed zamykającym `}` klasy, linia 552):**

```php
private function odswiezLiveMecze(array $mecze, int $turniejID, string $competitionApiId): void {
    $startedMecze = array_filter($mecze, fn($m) => !empty($m['rozpoczety']) && empty($m['zakonczony']));
    if (empty($startedMecze)) return;

    $throttleFile = WRITEPATH . "live_throttle_{$turniejID}.json";
    if (file_exists($throttleFile)) {
        $t = json_decode(file_get_contents($throttleFile), true);
        if (time() - ($t['time'] ?? 0) < 120) return;
    }

    try {
        $liveScore = new \App\Controllers\LiveScore();

        $liveMatches    = $liveScore->getLivescoresSimple(['competition_id' => $competitionApiId]);
        $historyMatches = $liveScore->getHistory([
            'competition_id' => $competitionApiId,
            'from'           => date('Y-m-d', strtotime('-1 day')),
            'to'             => date('Y-m-d'),
        ]);

        $liveIndex = [];
        foreach ($liveMatches as $lm) {
            $key = ($lm['home_id'] ?? '') . '_' . ($lm['away_id'] ?? '');
            $liveIndex[$key] = $lm;
        }
        $historyIndex = [];
        foreach ($historyMatches as $hm) {
            $key = ($hm['home_id'] ?? '') . '_' . ($hm['away_id'] ?? '');
            $historyIndex[$key] = $hm;
        }

        foreach ($startedMecze as $mecz) {
            $key      = $mecz['HomeID'] . '_' . $mecz['AwayID'];
            $jsonPath = WRITEPATH . "mecze/$turniejID/{$mecz['ApiID']}.json";
            if (!file_exists($jsonPath)) continue;

            $matchData = json_decode(file_get_contents($jsonPath), true);
            if (!is_array($matchData)) continue;

            if (isset($historyIndex[$key])) {
                $hm = $historyIndex[$key];
                [$hs, $as] = $this->parseScoreString($hm['score'] ?? '0 - 0');
                $matchData['status']             = 'Zakonczony';
                $matchData['home_team']['score'] = $hs;
                $matchData['away_team']['score'] = $as;
                unset($matchData['minute']);
            } elseif (isset($liveIndex[$key])) {
                $lm = $liveIndex[$key];
                [$hs, $as] = $this->parseScoreString($lm['score'] ?? '0 - 0');
                $matchData['status']             = 'Live';
                $matchData['home_team']['score'] = $hs;
                $matchData['away_team']['score'] = $as;
                $matchData['minute']             = isset($lm['time']) ? (int)$lm['time'] : null;
            } elseif (time() > strtotime(($matchData['date'] ?? '') . ' ' . ($matchData['time'] ?? '') . ' UTC') + 3 * 3600) {
                $matchData['status'] = 'Zakonczony';
            }

            $matchData['OstatniaAktualizacja'] = date('Y-m-d H:i:s');
            file_put_contents($jsonPath, json_encode($matchData, JSON_PRETTY_PRINT));
        }

        file_put_contents($throttleFile, json_encode(['time' => time()]));

    } catch (\Exception $e) {
        log_message('error', 'odswiezLiveMecze: ' . $e->getMessage());
    }
}

private function parseScoreString(string $score): array {
    $parts = array_map('trim', explode('-', $score));
    return [(int)($parts[0] ?? 0), (int)($parts[1] ?? 0)];
}
```

> **Uwaga:** Format pola `score` w API (`"2 - 1"` vs obiekt) należy zweryfikować przy pierwszym uruchomieniu podczas żywego meczu. Zalogować `$lm['score']` i dostosować `parseScoreString` jeśli potrzeba.

---

### 7d. `Views/ukladanka/sg/znowumecze.php`

**Zmiana 1 — logika statusów (linie 11–14):**
```php
// PRZED:
$statusRaw  = $match['details']['status'] ?? '';
$isFinished = ($statusRaw === 'Zakonczony');
$isLive     = ($match['rozpoczety'] == 1 && !$isFinished);
$isUpcoming = ($match['rozpoczety'] == 0);

// PO:
$statusRaw  = $match['details']['status'] ?? '';
$isScored   = !empty($match['zakonczony']);
$isFinished = ($statusRaw === 'Zakonczony') && !$isScored;
$isLive     = ($match['rozpoczety'] == 1 && !$isFinished && !$isScored);
$isUpcoming = ($match['rozpoczety'] == 0 && !$isScored);
```

**Zmiana 2 — etykieta czasu (linie 47–49):**
```php
// PRZED:
<?php elseif ($isFinished): ?>
// PO:
<?php elseif ($isFinished || $isScored): ?>
```

**Zmiana 3 — badge statusu (linie 51–61):** dodać stan `$isScored`:
```php
<?php if ($isUpcoming): ?>
  <span class="status-badge status-upcoming">Przyjmuje typy</span>
<?php elseif ($isLive): ?>
  <span class="status-badge status-live">Na żywo</span>
<?php elseif ($isFinished): ?>
  <span class="status-badge status-done">
    <?= $userPkt !== null ? '+' . $userPkt . ' pkt' : 'Zakończony' ?>
  </span>
<?php elseif ($isScored): ?>
  <span class="status-badge status-scored">
    <?= $userPkt !== null ? '+' . $userPkt . ' pkt' : 'Przeliczony' ?>
  </span>
<?php else: ?>
  <span class="status-badge status-locked">Zamknięty</span>
<?php endif; ?>
```

**Zmiana 4 — blok wyniku dla live/finished (linie 138–148):** wynik live pokazuje 0 zamiast pustki, klasa `score-live`:
```php
<div class="d-grid mb-2" style="grid-template-columns:1fr auto 1fr; gap:12px; align-items:center;">
  <div class="text-center">
    <div class="team-name mb-1"><?= esc($homeTeamName) ?></div>
    <?php if ($isLive || $homeScore !== null): ?>
      <div class="ff-bebas score-display<?= $isLive ? ' score-live' : '' ?>">
        <?= $homeScore !== null ? (int)$homeScore : 0 ?>
      </div>
    <?php endif; ?>
  </div>
  <div class="ff-bebas vs-div<?= $isLive ? ' score-live' : '' ?>">:</div>
  <div class="text-center">
    <div class="team-name mb-1"><?= esc($awayTeamName) ?></div>
    <?php if ($isLive || $awayScore !== null): ?>
      <div class="ff-bebas score-display<?= $isLive ? ' score-live' : '' ?>">
        <?= $awayScore !== null ? (int)$awayScore : 0 ?>
      </div>
    <?php endif; ?>
  </div>
</div>
```

**Zmiana 5 — etykieta collapse (linia 164):**
```php
// PRZED:
<span><?= $isFinished ? 'Wyniki graczy' : 'Jak typowali inni?' ?></span>
// PO:
<span><?= ($isFinished || $isScored) ? 'Wyniki graczy' : 'Jak typowali inni?' ?></span>
```

**Zmiana 6 — auto-refresh (po linii 189, na końcu pliku):**
```php
<?php
$hasLive = !empty(array_filter($mecze, function($m) {
    $scored   = !empty($m['zakonczony']);
    $status   = $m['details']['status'] ?? '';
    $finished = ($status === 'Zakonczony');
    return !empty($m['rozpoczety']) && !$finished && !$scored;
}));
if ($hasLive): ?>
<script>
  setTimeout(function() { location.reload(); }, 120000);
</script>
<?php endif; ?>
```

---

### 7e. `Views/ukladanka/sg/SkryptTypowania.php`

**Zmiana 1 — linia 41 (błąd składni JS):**
```js
// PRZED:
var isChecked = $<chk.is>(':checked');
// PO:
var isChecked = $chk.is(':checked');
```

**Zmiana 2 — linia 85 (błąd składni JS):**
```js
// PRZED:
var open = $<el.is>(':visible');
// PO:
var open = $el.is(':visible');
```

---

### 7f. `Config/Routes.php`

**Zmiana — linia 127:** dodać GET route dla formularza:
```php
// PRZED:
$routes->post('serwisant/zapiszWynikMeczu', 'Serwisant::zapiszWynikMeczu');
// PO:
$routes->match(['get', 'post'], 'serwisant/zapiszWynikMeczu', 'Serwisant::zapiszWynikMeczu');
```

---

### 7g. `Controllers/Serwisant.php`

**Zmiana 1 — linia 616 (błąd zmiennej):**
```php
// PRZED:
session()->setFlashdata('error', $validation->listErrors());
// PO:
session()->setFlashdata('error', $this->validator->listErrors());
```

**Zmiana 2 — `policzPunktyDlaMeczu()` (po wywołaniu `$this->updateJsonFile()`, linia 539):**  
Skasować stary plik typów, żeby force-regenerować z nowymi punktami:
```php
$this->updateJsonFile($daneMeczu);
// DODAĆ:
$staleTypy = WRITEPATH . "typy/{$mecz}.json";
if (file_exists($staleTypy)) {
    unlink($staleTypy);
}
```

---

### 7h. CSS (`newStyle2026.css` lub podobny)

```css
.status-scored {
    background: var(--ty-accent, #0d6efd);
    color: #fff;
}

.score-live {
    color: var(--ty-red, #dc3545);
    font-size: 2.5em;
    animation: pulse-live 1.5s ease-in-out infinite;
}

@keyframes pulse-live {
    0%, 100% { opacity: 1; }
    50%       { opacity: 0.6; }
}
```

---

## 8. Znane bugi (nienaprawione)

| # | Plik | Linia | Opis | Priorytet |
|---|------|-------|------|-----------|
| 1 | `Serwisant.php` | 616 | `$validation->listErrors()` → powinno być `$this->validator->listErrors()` | Wysoki |
| 2 | `Config/Routes.php` | 127 | Brak GET route dla `/serwisant/zapiszWynikMeczu` — formularz jest niedostępny | Wysoki |
| 3 | `MeczService.php` | 513 | `max([])` crash gdy mecz nie ma żadnych typów | Wysoki |
| 4 | `TerminarzModel.php` | 214 | `czyRozpoczety()` nie ustawia `Rozpoczety=1` automatycznie — mecz nigdy nie zmienia statusu | Wysoki |
| 5 | `Serwisant.php` | 539 | `policzPunktyDlaMeczu` nie kasuje `typy/{Id}.json` → gracze widzą stare wyniki bez punktów | Średni |
| 6 | `Serwisant.php` | 513–530 | Złota piłka (mnożnik ×2) nie jest uwzględniana przy przeliczaniu punktów | Średni |
| 7 | `SkryptTypowania.php` | 41, 85 | `$<chk.is>` i `$<el.is>` — błąd składni JS, funkcje nie działają | Średni |
| 8 | `TerminarzModel.php` | 137, 155, 170, 182 | Kolumny `HomeID`, `AwayID`, `zakonczony` nie są zwracane przy `$onlyIds=true` | Wysoki |

> **Bug #6 — szczegóły złotej piłki:** `policzPunktyDlaMeczu` daje max 3 pkt za dokładny wynik. Gracze z GoldenGame=1 powinni dostać 6 pkt. Należy dodać: `if ($typ['GoldenGame'] == 1) $punkty *= 2;` po obliczeniu punktów.

---

## 9. Backlog / planowane funkcje

### 9a. Notatki admina na stronie głównej
Pełny plan w: `/root/.claude/plans/zapoznaj-si-z-repozytorium-serene-goblet.md`

Skrót:
- Nowa tabela `notatki` (tresc, opublikowana, TurniejID, KlubID)
- Model `NotatkiModel.php`
- Widok `Views/ukladanka/sg/notatki.php` — karta z prev/next, markdown via marked.js CDN
- Widok `Views/administracja/dodajNotatke.php` — formularz + podgląd live
- Zmiany w `TheGame.php`, `AdminDash.php`, `Routes.php`

SQL (dodać na końcu `sup/changes_in_db.sql`):
```sql
CREATE TABLE notatki (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tresc        TEXT         NOT NULL,
    opublikowana TINYINT(1)   NOT NULL DEFAULT 1,
    TurniejID    INT UNSIGNED NOT NULL,
    KlubID       INT UNSIGNED NULL DEFAULT NULL,
    created_at   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### 9b. Archiwum meczów — fallback gdy brak JSON
Część meczów historycznych nie ma pliku JSON → widok `zakonczoneMecze.php` crashuje na `$match['details']`.

W `TheGame::archiwum()` (linia 416–419), gdy `$mecz['details'] = null`, dodać fallback z bazy:
```php
if (!file_exists($jsonPath)) {
    $dbMecz = model(TerminarzModel::class)->getMeczById($mecz['Id']);
    if ($dbMecz) {
        $mecz['details'] = [
            'date'      => $dbMecz['Date'],
            'time'      => $dbMecz['Time'],
            'naszCzas'  => $dbMecz['Time'],
            'home_team' => ['name' => $dbMecz['HomeName'], 'score' => $dbMecz['ScoreHome']],
            'away_team' => ['name' => $dbMecz['AwayName'], 'score' => $dbMecz['ScoreAway']],
            'status'    => $dbMecz['zakonczony'] ? 'Zakonczony' : '',
            'competition' => $dbMecz['CompetitionName'] ?? '',
        ];
    } else {
        $mecz['details'] = null;
    }
}
```

---

## 10. LiveScore API — notatki

- **Base URL:** `https://livescore-api.com/api-client/`
- **Auth:** `?key={lskey}&secret={lsscr}` (dołączane przez `_buildUrl()`)
- **Limit:** 14 500 req/dzień
- **Throttle live update:** co 120 sekund per turniej (plik `live_throttle_{turniejID}.json`)

Endpointy używane:

| Endpoint | Metoda | Kiedy |
|----------|--------|-------|
| `fixtures/matches.json` | `getFixtures()` | Import harmonogramu do DB |
| `scores/live.json` | `getLivescoresSimple()` ← *do dodania* | Live update co 2 min |
| `scores/history.json` | `getHistory()` ← *do dodania* | Wyniki zakończonych meczów |

> **Ważne:** W API, `id` z fixtures ≠ `id` z live. Dopasowanie meczu odbywa się po parze `home_id + away_id`, nie po ID meczu.

Format score — do weryfikacji przy pierwszym live meczu. Metoda `parseScoreString()` zakłada format `"2 - 1"`. Jeśli API zwraca inaczej (np. obiekt `{home: 2, away: 1}`), dostosować w `MeczService::parseScoreString()`.

---

## 11. Wdrażanie zmian

1. Edytować pliki bezpośrednio na serwerze staging przez cPanel File Manager lub SSH
2. Testować na staging.jakiwynik.com
3. Po weryfikacji — merge do `main` i deploy na produkcję

Kolejność wdrożenia zmian z sekcji 7:
```
1. TerminarzModel.php  (7a) — baza pipeline
2. Routes.php          (7f) — Serwisant dostępny przez GET
3. Serwisant.php       (7g) — naprawa walidacji i typy cache
4. LiveScore.php       (7b) — nowe metody API
5. MeczService.php     (7c) — odswiezLiveMecze + fix max()
6. znowumecze.php      (7d) — 4-state + live display + auto-refresh
7. SkryptTypowania.php (7e) — fix JS
8. CSS                 (7h) — .status-scored + .score-live
```

---

## 12. Gałęzie git

| Gałąź | SHA (staging) | Opis |
|-------|---------------|------|
| `main` | — | Produkcja |
| `staging` | origin/staging | Staging — zawiera zmiany przed wdrożeniem |
| `claude/review-staging-branch-3BwW3` | — | Gałąź robocza z tej sesji analitycznej |

Staging **wyprzedza main** — są niezatwierdzone zmiany czekające na merge.

---

*Dokument sporządzony na podstawie analizy kodu gałęzi `staging` i sesji pracy z Claude Code.*
