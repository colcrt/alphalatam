# Plataforma Documental — Núcleo del sistema

Implementación del núcleo funcional descrito en el documento de arquitectura,
siguiendo el patrón completo **Repository → Service → Policy → Livewire → Vista pública → SEO**
para los módulos bandera (**Persona**, **Caso**, **Fuente/Documento**, **Blog**, **Buscador**),
con el resto de módulos (Institución, Partido, Empresa, Contrato, Sentencia, Noticia)
modelados en la base de datos y listos para replicar el mismo patrón mecánicamente.

## ⚠️ Importante sobre este entregable

Este código se escribió a mano siguiendo estrictamente las convenciones de Laravel 11
y Livewire 3, pero **no se ejecutó ni se probó en un entorno real** porque el entorno
en el que fue generado no tiene acceso a Packagist/Composer. Antes de usarlo en producción:

1. Instálalo sobre un proyecto Laravel 11 real (`composer create-project laravel/laravel`).
2. Copia estos archivos respetando la misma estructura de carpetas.
3. Ejecuta `composer install`, configura `.env`, corre `php artisan migrate`.
4. Corre `php artisan test` y revisa con `vendor/bin/pint` antes de desplegar.

## Qué está completo

- **Base de datos**: 15 migraciones, ~35 tablas, completamente normalizada, con
  índices FULLTEXT, pivotes con metadatos, y relaciones polimórficas para
  Fuentes/Documentos/Etiquetas.
- **Modelos Eloquent**: los 21 modelos con todas sus relaciones (`BelongsToMany`
  con pivote, `MorphToMany`, `MorphMany`), traits compartidos (`HasSeo`,
  `HasEstadoPublicacion`).
- **Patrón completo para Persona** (módulo bandera, replicar para el resto):
  - `PersonaRepositoryInterface` + `EloquentPersonaRepository`
  - `PersonaService` con las reglas de negocio (no publicar sin fuente,
    versionado automático de estado judicial)
  - `PersonaPolicy` + binding en `AuthServiceProvider`
  - `PersonaData` (DTO) + `EstadoJudicial` (Enum)
  - 3 componentes Livewire admin (`PersonaIndex`, `PersonaTable`, `PersonaForm`)
  - Vista pública SSR con SEO, cronología de estado judicial, y relaciones
  - `PersonaObserver` (auditoría + invalidación de caché automática)
  - `ProcesarImagenPersona` Job (conversión a WebP asíncrona)
- **Transversales**: `SeoService` (JSON-LD, meta tags), `BusquedaService`
  (buscador global FULLTEXT), `EstadisticasService` (dashboard cacheado),
  `GenerarSitemap` Command, layouts público/admin con modo oscuro,
  componentes `<x-seo-meta>` y `<x-breadcrumbs>`.
- **Blog**: modelos completos + controller público + Livewire admin básico.

## Qué falta (siguiente iteración, mecánico)

Replicar exactamente el patrón de Persona para: Caso, Institución, Partido
Político, Empresa, Contrato, Sentencia, Noticia, Fuente. Cada uno necesita:
Repository (interfaz + Eloquent), Service, Policy, 3 componentes Livewire
admin, vista pública, Observer. El modelo de datos y las rutas ya están
listos para recibirlos.

También pendiente: FormRequests explícitos si se prefiere validación fuera
de Livewire, `routes/api.php` (Fase 10), 2FA en el login de Breeze,
sistema de backups programado, y tests Feature/Unit.

## Estructura

Ver árbol de carpetas en el documento de arquitectura entregado previamente
(`arquitectura-plataforma.md`) — esta implementación lo sigue exactamente.
