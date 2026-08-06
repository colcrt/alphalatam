-- -----------------------------------------------------------
-- Agrega el flag "Nota principal" (estelar) a blog_posts
-- El home muestra la nota marcada más reciente publicada.
-- Aplicar manualmente:  php -r "..."  o desde phpMyAdmin.
-- -----------------------------------------------------------

ALTER TABLE `blog_posts`
    ADD COLUMN `destacado` TINYINT(1) NOT NULL DEFAULT 0 AFTER `status`;

CREATE INDEX `blog_posts_destacado_status_published_index`
    ON `blog_posts` (`destacado`, `status`, `published_at`);
