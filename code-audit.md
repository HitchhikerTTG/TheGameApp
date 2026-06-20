# Code Audit — TheGameApp Live Scoring Refactor

## Kontekst

Sesja planowania + weryfikacji refaktoru systemu live scoringowego (Kroki 1–7).
Zmiany wdrożone na branchu `staging`. Niniejszy dokument zbiera wyniki audytu kodu.

---

## Znalezione błędy

### 1. `Commands/UpdateLivescores.php` — Case B: zła zmienna w `fetchGoals()`

**Lokalizacja:** metoda `run()`, sekcja Case B (mecz live / IN PLAY)

```php
// BŁĄD — $hm pochodzi z Case A (history), w Case B nie jest zdefiniowane:
'goals' => $this->fetchGoals((string)($hm['id'] ?? '')),

// POPRAWNIE:
'goals' => $this->fetchGoals((string)($lm['id'] ?? '')),
```

Skutek: dla meczów na żywo `fetchGoals()` zawsze dostaje pusty string → zwraca `[]` → strzelcy nie są zapisywani do `live/{ApiID}.json`.

---

### 2. `Commands/UpdateLivescores.php` — Case C: zła nazwa zmiennej

**Lokalizacja:** metoda `run()`, sekcja Case C (fallback — brak meczu w API, czas minął)

```php
// BŁĄD — zmienna nazywa się $existingLive, nie $existing:
'goals' => $existing['goals'] ?? [],

// POPRAWNIE:
'goals' => $existingLive['goals'] ?? [],
```

Skutek: PHP notice / warning przy każdym przebiegu crona dla meczu w fallback. Goals przepisywane jako `[]` zamiast zachować poprzednie.

---

### 3. `Views/typowanie/header.php` — `initTheme()` wywołane przed renderowaniem DOM

**Lokalizacja:** linia 30 (`initTheme()` call) vs linia 52 (`#themeToggle` button)

```javascript
// BŁĄD — getElementById zwraca null, bo <body> jeszcze nie sparsowany:
function initTheme() {
  var saved = localStorage.getItem('theme') || 'light';
  document.documentElement.setAttribute('data-bs-theme', saved);
  document.getElementById('themeToggle').textContent = saved === 'dark' ? '🌙' : '☀️'; // ← crash
}
initTheme(); // wywoływane w <head>

// POPRAWNIE — dodać null check:
function initTheme() {
  var saved = localStorage.getItem('theme') || 'light';
  document.documentElement.setAttribute('data-bs-theme', saved);
  var btn = document.getElementById('themeToggle');
  if (btn) btn.textContent = saved === 'dark' ? '🌙' : '☀️';
}
function toggleTheme() {
  var current = document.documentElement.getAttribute('data-bs-theme');
  var next = current === 'dark' ? 'light' : 'dark';
  document.documentElement.setAttribute('data-bs-theme', next);
  localStorage.setItem('theme', next);
  var btn = document.getElementById('themeToggle');
  if (btn) btn.textContent = next === 'dark' ? '🌙' : '☀️';
}
```

Skutek: `TypeError: null is not an object` w konsoli przeglądarki. W Safari/WebKit blokuje dalsze wykonanie skryptów na stronie (brak działania formularzy). W Chrome/Firefox efekt wizualny — brak aktualizacji ikony motywu przy ciemnym motywie z localStorage.

---

## Stan implementacji (staging)

| Krok | Plik | Status |
|------|------|--------|
| Krok 1 — `TerminarzModel::setZakonczony()` | `app/Models/TerminarzModel.php` | do weryfikacji |
| Krok 2 — `Commands/UpdateLivescores.php` | nowy plik | wdrożony, **2 bugi** (patrz wyżej) |
| Krok 3 — fix `MeczService::odswiezLiveMecze()` | `app/Services/MeczService.php` | do weryfikacji |
| Krok 4 — refaktor `TheGame::livePoll()` | `Controllers/TheGame.php` | wdrożony ✓ |
| Krok 5 — `AdminDash` + `Serwisant` | `Controllers/AdminDash.php`, `Serwisant.php` | do weryfikacji |
| Krok 6 — separacja live/statyczny JSON | `app/Services/MeczService.php` | do weryfikacji |
| Krok 7 — strzelcy bramek | wiele plików | wdrożony (poza bugami w Kroku 2) |

### Krok 7 — szczegóły

| Sub-krok | Plik | Status |
|---|---|---|
| 7.2 `fetchGoals()` | `Commands/UpdateLivescores.php` | wdrożony, **bug Case B i C** |
| 7.3 `goals` w `writeLiveJson()` | `Commands/UpdateLivescores.php` | wdrożony |
| 7.4 `goals` w `livePoll()` | `Controllers/TheGame.php` | wdrożony ✓ |
| 7.5 `goals` w `testIndex()` | `Controllers/TheGame.php` | wdrożony ✓ |
| 7.6 HTML strzelców | `Views/ukladanka/sg/znowumecze.php` | wdrożony ✓ |
| 7.7 JS `refreshLiveScores()` | `Views/ukladanka/sg/SkryptTypowania.php` | wdrożony ✓ |

### `SkryptTypowania.php` — weryfikacja składni JS

Kod ma niespójną indentację (styl 10-spacji vs 8-spacji po scaleniu), ale klamry są prawidłowo zbilansowane. Brak błędów składniowych. Kod działa poprawnie.

Drobna uwaga logiczna: dla meczu ze statusem `NOT STARTED` (brak pliku live) odpowiedź `/livepoll` nie zawiera `homeScore`, więc `match.homeScore !== null` daje `undefined !== null` = `true` → `parseInt(undefined)` = `NaN`. Efekt: wyświetla `NaN` w wyniku zamiast niczego. Poprawka: `match.homeScore != null` (luźne porównanie).

---

## Plik legacy do usunięcia

`Commands/RefreshLiveScores.php` — stary plik (1383 bajty), zastąpiony przez `UpdateLivescores.php`. Można bezpiecznie usunąć po potwierdzeniu że cron używa nowej komendy `live:update`.
