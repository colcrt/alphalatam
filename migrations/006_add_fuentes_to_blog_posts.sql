-- ============================================================
-- Añade la columna `fuentes` a blog_posts
-- Texto libre con las fuentes del artículo (referencias/citas)
-- ============================================================

SET NAMES utf8mb4;

ALTER TABLE `blog_posts`
    ADD COLUMN `fuentes` LONGTEXT NULL DEFAULT NULL AFTER `schema_json`;
