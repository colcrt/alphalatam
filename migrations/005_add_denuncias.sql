-- -----------------------------------------------------------
-- Denuncias ciudadanas
-- El formulario público /denuncias captura denuncias de corrupción.
-- El equipo editorial las revisa en /admin/denuncias y puede
-- convertirlas en un borrador de artículo del blog (blog_posts).
-- Aplicar manualmente desde phpMyAdmin.
-- -----------------------------------------------------------

CREATE TABLE IF NOT EXISTS `denuncias` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `titulo` VARCHAR(255) NOT NULL,
    `hechos` TEXT NOT NULL,
    `evidencias` TEXT NULL DEFAULT NULL,
    `email_contacto` VARCHAR(255) NULL DEFAULT NULL,
    `acepta_terminos` TINYINT(1) NOT NULL DEFAULT 0,
    `status` VARCHAR(20) NOT NULL DEFAULT 'pendiente',
    `blog_post_id` BIGINT UNSIGNED NULL DEFAULT NULL,
    `revisado_por` BIGINT UNSIGNED NULL DEFAULT NULL,
    `revisado_at` TIMESTAMP NULL DEFAULT NULL,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `denuncias_status_index` (`status`),
    KEY `denuncias_blog_post_index` (`blog_post_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
