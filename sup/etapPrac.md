# <JakiWynik.com> — Status wdrożenia (gałąź: staging)
_Stan na 2026-05-17_

—

## ✅ Zrealizowane

### System powiadomień email

| Plik | Co zrobione |
|——|-————|
| `Models/UserModel.php` | Pola `notify_bet_saved`, `notify_reminder` w `allowedFields` i `getGameUserData()` |
| `Controllers/Profil.php` | Metoda `zapiszPreferencje()` — zapis preferencji przez POST |
| `Libraries/Postmark.php` | Biblioteka do wysyłki przez Postmark API (z parametrem `messageStream`) |
| `Services/EmailService.php` | `queueBetSaved()` — kolejka z 3-minutowym rolling window; `processQueue()` — wysyłka z kolejki; `sendReminders()` — codzienny reminder; `sendCampaignTest()` / `sendCampaign()` — kampanie broadcastowe |
| `Commands/ProcessEmailQueue.php` | Spark command `email:process` |
| `Commands/SendReminders.php` | Spark command `email:reminders` |
| Baza danych | Kolumny `notify_bet_saved`, `notify_reminder` w tabeli `uzytkownicy` |

### Panel kampanii email (admin)

| Plik | Co zrobione |
|——|-————|
| `Views/administracja/kampanie.php` | Pełne UI: wybór szablonu, temat, grupy odbiorców, historia wysyłek, modal potwierdzenia, podgląd szablonu w iframe |

### Dark mode + naprawa header.php

| Co zrobione | Szczegół |
|-————|-———|
| Bootstrap 5.2.2 → **5.3.3** | CSS i JS zaktualizowane |
| `<html data-bs-theme=„light”>` | Natywny dark mode Bootstrap 5.3 |
| `.icon-group` — dwa przyciski obok siebie | Moon toggle + hamburger bez nakładania |
| `initTheme()` / `toggleTheme()` | Preferencja zapisywana w `localStorage` |
| Dark mode CSS overrides | Topnav, accordion, score-display, betting-hints |
| Bug A naprawiony | `if (element)` guard przed `classList.add` |
| Bug B naprawiony | `##AAAE8E` → `#AAAE8E` w regule hover |

—

## ⚠️ Znane błędy na staging (do naprawy)

| # | Plik | Problem |
|—|——|———|
| 1 | `Views/typowanie/header.php` | Bootstrap Icons `@1.3.0` — `bi-moon-fill` nie istnieje w tej wersji → ikona niewidoczna. Fix: zmienić na `@1.11.3` |
| 2 | `Views/typowanie/header.php` | Duplikat tagu `<title>` — dwa tagi `<title>` w `<head>` |
| 3 | `Views/typowanie/header.php` | Osierocon y `</div>` po topnav (pozostałość komentarza `<!— MA BYĆ: —>`) |
| 4 | `Services/EmailService.php` | `getCampaignRecipients()` — kąty `<<uzytkownicy.id>>` zamiast backticks w klauzuli JOIN → błąd SQL |

—

## ❓ Niezweryfikowane (staging — kod nie był czytany)

- `Views/profil/profil.php` — czy zawiera formularz preferencji email z checkboxami?
- `Controllers/AdminDash.php` — czy metody `kampanie()`, `testKampania()`, `wyslijKampanie()` są dodane?
- `Config/Routes.php` — czy trasy `/hell/kampanie` są dodane?
- `Controllers/TheGame.php` — czy `EmailService::queueBetSaved()` jest wywołane po zapisie typu?
- `Models/TerminarzModel.php` — czy metoda `getMeczeNaReminder()` jest dodana?
- Baza danych — czy tabele `email_queue` i `email_campaigns` istnieją?
- Cron — czy zadania `email:process` (co 5 min) i `email:reminders` (17:00) są skonfigurowane?

—

## 🔜 Zaplanowane (nie zaczęte)

### Redesign wizualny
Prototyp gotowy (Bootstrap 5.3.3 + własne tokeny CSS `—ty-*` + Bebas Neue/DM Sans).  
Pliki do przerobienia:

| Plik | Co się zmienia |
|——|-—————|
| `Views/typowanie/header.php` | Nowy topbar, tokeny CSS → osobny plik `public/typer.css` |
| `Views/ukladanka/sg/znowumecze.php` | `.match-card` zamiast accordion, stepper, golden-row |
| `Views/ukladanka/sg/chat.php` | Nowe HTML shoutboxa, logika AJAX bez zmian |
| `Views/ukladanka/sg/pytania.php` | `.question-badge`, `btn-type`, logika AJAX bez zmian |
| `Views/tabela/tabela.php` | `.lb-row` zamiast `<table>`, logika JS bez zmian |
