-- ============================================================
-- TheGameApp -- zmiany schematu bazy danych
-- ============================================================
-- Każdą zmianę wykonuj ręcznie przez phpMyAdmin (staging i prod)
-- Format wpisu: data | opis | SQL
-- ============================================================


-- [2026-05-10] Preferencje powiadomień email
-- Pozwala użytkownikowi kontrolować czy dostaje maile
-- o zapisaniu typu i przypomnienia przed meczem
-- ----------------------------------------
ALTER TABLE uzytkownicy 
ADD COLUMN notify_bet_saved TINYINT(1) DEFAULT 1 AFTER PlaysTheActiveTournament,
ADD COLUMN notify_reminder  TINYINT(1) DEFAULT 1 AFTER notify_bet_saved;
-- ----------------------------------------

-- [2026-05-11] Kolejka emaili
CREATE TABLE email_queue (
    id INT AUTO_INCREMENT PRIMARY KEY,
    uniID VARCHAR(255) NOT NULL,
    type VARCHAR(50) NOT NULL,
    subject VARCHAR(255) NOT NULL,
    body TEXT,
    send_after DATETIME NOT NULL,
    sent TINYINT(1) DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_queue (uniID, type, sent)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- [2026-05-13] Kampanie mailowe

CREATE TABLE email_campaigns (
    id INT AUTO_INCREMENT PRIMARY KEY,
    template_file VARCHAR(255) NOT NULL,
    subject VARCHAR(255) NOT NULL,
    target_group VARCHAR(50) NOT NULL,
    test_sent_at DATETIME NULL,
    sent_at DATETIME NULL,
    recipients_count INT DEFAULT 0,
    UNIQUE KEY uq_template_target (template_file, target_group)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- [2026-05-30] Notatki / micro-posty admina
-- Krótkie ogłoszenia wyświetlane na stronie głównej, per turniej i opcjonalnie per klub
-- KlubID NULL = widoczne dla wszystkich graczy turnieju
-- ----------------------------------------
CREATE TABLE notatki (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tresc        TEXT         NOT NULL,
    opublikowana TINYINT(1)   NOT NULL DEFAULT 1,
    TurniejID    INT UNSIGNED NOT NULL,
    KlubID       INT UNSIGNED NULL DEFAULT NULL,
    created_at   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
-- ----------------------------------------

ALTER TABLE uzytkownicy
  ADD COLUMN digest_optin TINYINT(1) NOT NULL DEFAULT 1 AFTER notify_reminder;

ALTER TABLE turnieje
  ADD COLUMN okno_24h TINYINT(1) NOT NULL DEFAULT 0;
  
ALTER TABLE notatki
  ADD COLUMN wyklucz_KlubID INT UNSIGNED NULL DEFAULT NULL;

-- Dodaj kolumnę emoji (utf8mb4 -- wymagane dla 4-bajtowych znaków)
ALTER TABLE uzytkownicy
    ADD COLUMN emoji VARCHAR(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL;

-- Ujednolicenie charset całej tabeli (naprawia też kolumnę latin2)
ALTER TABLE uzytkownicy
    CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;  

ALTER TABLE pytania
    ADD COLUMN opis    TEXT NULL AFTER tresc,
    ADD COLUMN zrodlo  VARCHAR(255) NULL AFTER opis;
    
ALTER TABLE turnieje ADD COLUMN liczyDoWszechczasow TINYINT(1) NOT NULL DEFAULT 0;

ALTER TABLE uzytkownicy ADD COLUMN slug VARCHAR(100) NULL UNIQUE; 
ALTER TABLE uzytkownicy  ADD UNIQUE INDEX idx_slug (slug);

-- Aby rozróżnić zamknięty i przeliczony

ALTER TABLE terminarz
  ADD COLUMN przeliczony TINYINT(1) NOT NULL DEFAULT 0
  AFTER zakonczony;