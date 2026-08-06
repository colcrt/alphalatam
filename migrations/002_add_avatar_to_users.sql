-- ============================================================
-- Agregar campo avatar_path a users
-- ============================================================

ALTER TABLE `users`
    ADD COLUMN `avatar_path` VARCHAR(500) NULL DEFAULT NULL AFTER `role`;
