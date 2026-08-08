-- -----------------------------------------------------------
-- Agrega soporte de media animada / video en tarjeta a blog_posts.
-- media_destacada_path : GIF animado, MP4 (H.264) o WebM que se
--   reproduce en la tarjeta del artículo (casos excepcionales).
-- media_destacada_tipo : 'gif' | 'video' (null si no hay media).
-- card_embed_url       : URL (Instagram/Twitter/etc.) para reproducir
--   un embed oEmbed en la tarjeta en lugar de la imagen/media.
-- card_embed_html      : HTML del embed resuelto y sanitizado al guardar.
-- Aplicar manualmente desde phpMyAdmin o con la CLI de MySQL.
-- -----------------------------------------------------------

ALTER TABLE `blog_posts`
    ADD COLUMN `media_destacada_path` VARCHAR(500) NULL AFTER `imagen_destacada_path`,
    ADD COLUMN `media_destacada_tipo` VARCHAR(20) NULL AFTER `media_destacada_path`,
    ADD COLUMN `card_embed_url` VARCHAR(500) NULL AFTER `media_destacada_tipo`,
    ADD COLUMN `card_embed_html` TEXT NULL AFTER `card_embed_url`;
