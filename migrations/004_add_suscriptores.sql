-- -----------------------------------------------------------
-- Suscriptores del boletín diario
-- El hero del home captura emails de suscripción al boletín.
-- Aplicar manualmente desde phpMyAdmin.
-- -----------------------------------------------------------

CREATE TABLE IF NOT EXISTS `suscriptores` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `email` VARCHAR(255) NOT NULL,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `suscriptores_email_unique` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
