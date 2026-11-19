<?php
// Theme ERP – lib.php
// SCSS build callbacks for Moodle's theme compiler.

defined('MOODLE_INTERNAL') || die();

/**
 * Main SCSS content.
 *
 * Moodle compiles ONE combined SCSS string. We must prepend Boost's
 * preset (which contains all Bootstrap variables and mixins) BEFORE
 * our own erp.scss — otherwise the compiler has no variables to
 * resolve and produces empty/broken CSS.
 *
 * @param theme_config $theme
 * @return string
 */
function theme_erp_get_main_scss_content($theme) {
    global $CFG;

    // ── Step 1: Boost preset SCSS ─────────────────────────────────────────
    // This file defines all Bootstrap $variables, mixins and functions
    // that our erp.scss depends on indirectly via Boost's own SCSS.
    $boostpreset = $CFG->dirroot . '/theme/boost/scss/preset/default.scss';

    if (!file_exists($boostpreset)) {
        // Fallback: try the other Boost preset name used in some versions.
        $boostpreset = $CFG->dirroot . '/theme/boost/scss/preset/plain.scss';
    }

    $scss = '';

    if (file_exists($boostpreset)) {
        $scss .= file_get_contents($boostpreset);
        $scss .= "\n";
    }

    // ── Step 2: Our theme SCSS ────────────────────────────────────────────
    $erpscss = $CFG->dirroot . '/theme/erp/scss/erp.scss';

    if (file_exists($erpscss)) {
        $scss .= file_get_contents($erpscss);
    }

    return $scss;
}

/**
 * Pre-SCSS variables injected BEFORE everything else.
 * Use this to override Bootstrap Sass variables if needed.
 *
 * @param theme_config $theme
 * @return string
 */
function theme_erp_get_pre_scss($theme) {
    // We set our tokens as CSS custom properties in erp.scss itself,
    // so no Sass variable overrides are needed here.
    // Add any $variable overrides here if needed in future.
    return '';
}

/**
 * Extra SCSS appended AFTER the main stylesheet.
 * Admin can inject custom SCSS via theme settings.
 *
 * @param theme_config $theme
 * @return string
 */
function theme_erp_get_extra_scss($theme) {
    $extra = '';

    if (!empty($theme->settings->scss)) {
        $extra .= (string) $theme->settings->scss;
    }

    return $extra;
}