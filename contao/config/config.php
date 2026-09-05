<?php

declare(strict_types=1);

use ThinkDigital\ContaoGridPlugin\Widget\GridSettingsWizard;

/*
 * Grid wrapper start/stop pair. The element types themselves are registered as
 * fragment controllers via #[AsContentElement]; only the wrapper nesting is
 * declared here (CONTAO6_MIGRATION.md ADR-13 / ADR-14). Type names are kept
 * unchanged so no content migration is required.
 */
$GLOBALS['TL_WRAPPERS']['start'][] = 'dma_simplegrid_wrapper_start';
$GLOBALS['TL_WRAPPERS']['stop'][] = 'dma_simplegrid_wrapper_stop';

/*
 * Back-end form field widget: a MultiColumnWizard replacement for the column /
 * offset settings (one row of five breakpoint selects, serialised in the exact
 * a:1:{i:0;a:5:{…}} format the legacy field used).
 */
$GLOBALS['BE_FFL']['gridSettingsWizard'] = GridSettingsWizard::class;
