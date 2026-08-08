-- ============================================================
-- Solicitudes de borrado de artículos (moderación)
-- Un editor solicita el borrado de un artículo; el admin lo
-- aprueba (soft delete) o lo rechaza (se limpian los campos).
-- ============================================================

SET NAMES utf8mb4;

ALTER TABLE `blog_posts`
    ADD COLUMN `delete_requested_at` TIMESTAMP NULL DEFAULT NULL AFTER `fuentes`,
    ADD COLUMN `delete_requested_by` BIGINT UNSIGNED NULL DEFAULT NULL AFTER `delete_requested_at`;

ALTER TABLE `blog_posts`
    ADD KEY `blog_posts_delete_requested_index` (`delete_requested_at`);
