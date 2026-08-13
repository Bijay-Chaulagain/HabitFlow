-- HABIT TRACKER — Migration: Add per-day repetition count to habit_completions
-- Enables quantitative daily targets (habits.target).
-- Existing completion rows remain valid: they keep count = 1.
-- Run once against an existing database:
--   mysql -u root habit_tracker < migration_add_daily_target_count.sql
--
-- NOTE: On MariaDB this can be made idempotent with:
--   ADD COLUMN IF NOT EXISTS `count` INT NOT NULL DEFAULT 1 AFTER `completion_date`

ALTER TABLE `habit_completions`
  ADD COLUMN `count` INT NOT NULL DEFAULT 1 AFTER `completion_date`;