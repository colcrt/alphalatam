-- ============================================================
-- Blog de Corrupción — Esquema consolidado MySQL
-- PHP 8 vanilla (sin Laravel)
-- ============================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- -----------------------------------------------------------
-- USERS
-- -----------------------------------------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(255) NOT NULL,
    `email` VARCHAR(255) NOT NULL,
    `email_verified_at` TIMESTAMP NULL DEFAULT NULL,
    `password` VARCHAR(255) NOT NULL,
    `role` VARCHAR(20) NOT NULL DEFAULT 'editor',
    `two_factor_secret` TEXT NULL DEFAULT NULL,
    `two_factor_recovery_codes` TEXT NULL DEFAULT NULL,
    `remember_token` VARCHAR(100) NULL DEFAULT NULL,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------
-- BLOG CATEGORIAS
-- -----------------------------------------------------------
DROP TABLE IF EXISTS `blog_categorias`;
CREATE TABLE `blog_categorias` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `nombre` VARCHAR(255) NOT NULL,
    `slug` VARCHAR(255) NOT NULL,
    `descripcion` TEXT NULL DEFAULT NULL,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `blog_categorias_slug_unique` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------
-- BLOG POSTS
-- tipo: noticia | opinion | investigacion
-- -----------------------------------------------------------
DROP TABLE IF EXISTS `blog_posts`;
CREATE TABLE `blog_posts` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `titulo` VARCHAR(255) NOT NULL,
    `slug` VARCHAR(255) NOT NULL,
    `extracto` TEXT NULL DEFAULT NULL,
    `contenido` LONGTEXT NOT NULL,
    `tipo` VARCHAR(30) NOT NULL DEFAULT 'noticia',
    `categoria_id` BIGINT UNSIGNED NULL DEFAULT NULL,
    `autor_id` BIGINT UNSIGNED NOT NULL,
    `imagen_destacada_path` VARCHAR(500) NULL DEFAULT NULL,
    `status` VARCHAR(20) NOT NULL DEFAULT 'borrador',
    `published_at` TIMESTAMP NULL DEFAULT NULL,
    `meta_title` VARCHAR(255) NULL DEFAULT NULL,
    `meta_description` VARCHAR(500) NULL DEFAULT NULL,
    `canonical_url` VARCHAR(500) NULL DEFAULT NULL,
    `og_image_path` VARCHAR(500) NULL DEFAULT NULL,
    `schema_json` JSON NULL DEFAULT NULL,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    `deleted_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `blog_posts_slug_unique` (`slug`),
    KEY `blog_posts_status_published_index` (`status`, `published_at`),
    KEY `blog_posts_tipo_index` (`tipo`),
    FULLTEXT KEY `blog_posts_titulo_extracto_fulltext` (`titulo`, `extracto`),
    CONSTRAINT `blog_posts_categoria_fk` FOREIGN KEY (`categoria_id`) REFERENCES `blog_categorias` (`id`) ON DELETE SET NULL,
    CONSTRAINT `blog_posts_autor_fk` FOREIGN KEY (`autor_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------
-- BLOG POST VERSIONES
-- -----------------------------------------------------------
DROP TABLE IF EXISTS `blog_post_versiones`;
CREATE TABLE `blog_post_versiones` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `blog_post_id` BIGINT UNSIGNED NOT NULL,
    `contenido` LONGTEXT NOT NULL,
    `editado_por` BIGINT UNSIGNED NULL DEFAULT NULL,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `bpv_post_index` (`blog_post_id`),
    CONSTRAINT `bpv_post_fk` FOREIGN KEY (`blog_post_id`) REFERENCES `blog_posts` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------
-- BLOG COMENTARIOS
-- -----------------------------------------------------------
DROP TABLE IF EXISTS `blog_comentarios`;
CREATE TABLE `blog_comentarios` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `blog_post_id` BIGINT UNSIGNED NOT NULL,
    `parent_id` BIGINT UNSIGNED NULL DEFAULT NULL,
    `autor_nombre` VARCHAR(255) NOT NULL,
    `autor_email` VARCHAR(255) NOT NULL,
    `contenido` TEXT NOT NULL,
    `status` VARCHAR(20) NOT NULL DEFAULT 'pendiente',
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `bc_post_index` (`blog_post_id`),
    KEY `bc_parent_index` (`parent_id`),
    KEY `bc_status_index` (`status`),
    CONSTRAINT `bc_post_fk` FOREIGN KEY (`blog_post_id`) REFERENCES `blog_posts` (`id`) ON DELETE CASCADE,
    CONSTRAINT `bc_parent_fk` FOREIGN KEY (`parent_id`) REFERENCES `blog_comentarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------
-- ETIQUETAS (tagging polimorfico)
-- -----------------------------------------------------------
DROP TABLE IF EXISTS `etiquetas`;
CREATE TABLE `etiquetas` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `nombre` VARCHAR(255) NOT NULL,
    `slug` VARCHAR(255) NOT NULL,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `etiquetas_slug_unique` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `etiquetables`;
CREATE TABLE `etiquetables` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `etiqueta_id` BIGINT UNSIGNED NOT NULL,
    `etiquetable_id` BIGINT UNSIGNED NOT NULL,
    `etiquetable_type` VARCHAR(255) NOT NULL,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `etiqueta_id_index` (`etiqueta_id`),
    KEY `etiquetable_type_id_index` (`etiquetable_type`, `etiquetable_id`),
    CONSTRAINT `etiquetables_etiqueta_fk` FOREIGN KEY (`etiqueta_id`) REFERENCES `etiquetas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------
-- SISTEMA
-- -----------------------------------------------------------
DROP TABLE IF EXISTS `activity_log`;
CREATE TABLE `activity_log` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `log_name` VARCHAR(255) NULL DEFAULT NULL,
    `description` TEXT NOT NULL,
    `subject_type` VARCHAR(255) NULL DEFAULT NULL,
    `subject_id` BIGINT UNSIGNED NULL DEFAULT NULL,
    `causer_type` VARCHAR(255) NULL DEFAULT NULL,
    `causer_id` BIGINT UNSIGNED NULL DEFAULT NULL,
    `properties` JSON NULL DEFAULT NULL,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `activity_log_log_name_index` (`log_name`),
    KEY `activity_log_subject_type_subject_id_index` (`subject_type`, `subject_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `configuraciones`;
CREATE TABLE `configuraciones` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `clave` VARCHAR(255) NOT NULL,
    `valor` JSON NULL DEFAULT NULL,
    `grupo` VARCHAR(100) NOT NULL DEFAULT 'general',
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `configuraciones_clave_unique` (`clave`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `redirecciones`;
CREATE TABLE `redirecciones` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `desde` VARCHAR(500) NOT NULL,
    `hacia` VARCHAR(500) NOT NULL,
    `codigo` SMALLINT UNSIGNED NOT NULL DEFAULT 301,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `redirecciones_desde_unique` (`desde`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
