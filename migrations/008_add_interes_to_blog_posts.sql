-- -----------------------------------------------------------
-- Agrega el flag "Artículo de interés" a blog_posts
-- El home muestra hasta 3 de los marcados más recientes publicados.
-- Aplicar manualmente:  php -r "..."  o desde phpMyAdmin.
-- -----------------------------------------------------------

ALTER TABLE `blog_posts`
    ADD COLUMN `interes` TINYINT(1) NOT NULL DEFAULT 0 AFTER `destacado`;

CREATE INDEX `blog_posts_interes_status_published_index`
    ON `blog_posts` (`interes`, `status`, `published_at`);
