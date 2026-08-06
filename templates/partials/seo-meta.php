<?php
declare(strict_types=1);

/**
 * SEO meta tags partial.
 *
 * Expected variables (from parent scope):
 *  string $metaTitle        (optional) overrides <title>
 *  string $metaDescription  (optional) meta description
 *  string $canonical        (optional) canonical URL
 *  string $ogImage          (optional) Open Graph image
 *  array  $jsonLd           (optional) JSON-LD structured data
 */
$_seo_title       = $metaTitle ?? ($pageTitle ?? 'AlphaLatam');
$_seo_description = $metaDescription ?? '';
$_seo_canonical   = $canonical ?? '';
$_seo_og_image    = $ogImage ?? '';
$_seo_json_ld     = $jsonLd ?? [];
$_seo_preload_img = $preloadImage ?? '';
?>

<?php if ($_seo_preload_img): ?>
<link rel="preload" as="image" href="<?= esc($_seo_preload_img) ?>">
<?php endif; ?>

<?php if ($_seo_description): ?>
<meta name="description" content="<?= esc($_seo_description) ?>">
<?php endif; ?>

<?php if ($_seo_canonical): ?>
<link rel="canonical" href="<?= esc($_seo_canonical) ?>">
<?php endif; ?>

<!-- Open Graph -->
<meta property="og:type" content="website">
<meta property="og:title" content="<?= esc($_seo_title) ?>">
<?php if ($_seo_description): ?>
<meta property="og:description" content="<?= esc($_seo_description) ?>">
<?php endif; ?>
<?php if ($_seo_canonical): ?>
<meta property="og:url" content="<?= esc($_seo_canonical) ?>">
<?php endif; ?>
<?php if ($_seo_og_image): ?>
<meta property="og:image" content="<?= esc($_seo_og_image) ?>">
<?php endif; ?>
<meta property="og:site_name" content="AlphaLatam">
<meta property="og:locale" content="es_CO">

<!-- Twitter Card -->
<meta name="twitter:card" content="<?= $_seo_og_image ? 'summary_large_image' : 'summary' ?>">
<meta name="twitter:title" content="<?= esc($_seo_title) ?>">
<?php if ($_seo_description): ?>
<meta name="twitter:description" content="<?= esc($_seo_description) ?>">
<?php endif; ?>
<?php if ($_seo_og_image): ?>
<meta name="twitter:image" content="<?= esc($_seo_og_image) ?>">
<?php endif; ?>

<?php if (!empty($_seo_json_ld)): ?>
<?= json_ld($_seo_json_ld) ?>
<?php endif; ?>
