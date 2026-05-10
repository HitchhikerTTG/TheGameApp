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
