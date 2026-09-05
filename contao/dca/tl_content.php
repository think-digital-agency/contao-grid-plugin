<?php

declare(strict_types=1);

use Contao\ArrayUtil;

/*
 * Grey grid-class hint in the element list (restores DMA's show_simplegrid_infos).
 * The button_callback is wired via #[AsCallback] on ContentPaletteListener.
 */
$GLOBALS['TL_DCA']['tl_content']['list']['operations'] ??= [];
ArrayUtil::arrayInsert($GLOBALS['TL_DCA']['tl_content']['list']['operations'], 0, [
    'grid_info' => [
        'label' => &$GLOBALS['TL_LANG']['tl_content']['grid_info'],
    ],
]);

/*
 * Grid column / offset settings (CONTAO6_MIGRATION.md ADR-14). Field names kept
 * identical to dma/dma_simple_grid so existing content needs no migration; the
 * palette injection happens in ContentPaletteListener. The remaining
 * dma_simplegrid_* columns stay SQL-only elsewhere (theme DCA / ADR-4b) until a
 * dedicated drop migration.
 */
$GLOBALS['TL_DCA']['tl_content']['fields']['dma_simplegrid_columnsettings'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_content']['dma_simplegrid_columnsettings'],
    'exclude' => true,
    'inputType' => 'gridSettingsWizard',
    'eval' => ['tl_class' => 'w50'],
    'sql' => 'text NULL',
];

$GLOBALS['TL_DCA']['tl_content']['fields']['dma_simplegrid_offsetsettings'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_content']['dma_simplegrid_offsetsettings'],
    'exclude' => true,
    'inputType' => 'gridSettingsWizard',
    'eval' => ['tl_class' => 'w50'],
    'sql' => 'text NULL',
];

/*
 * Legacy dma/dma_simple_grid columns that this bundle no longer uses (push, pull,
 * offset-right, block grid, additional-classes — 0 rows on the demo, features
 * dropped per ADR-14). Kept SQL-only for rollback / cross-site verification, the
 * same way WP-3 kept rsce_data (ADR-4b); a dedicated bundle migration drops them
 * once verified across customer sites.
 */
foreach ([
    'dma_simplegrid_pushsettings' => 'text NULL',
    'dma_simplegrid_pullsettings' => 'text NULL',
    'dma_simplegrid_offsetrightsettings' => 'text NULL',
    'dma_simplegrid_blocksettings' => 'text NULL',
    'dma_simplegrid_additionalwrapperclasses' => 'blob NULL',
    'dma_simplegrid_additionalcolumnclasses' => 'blob NULL',
    'dma_simplegrid_additionalrowclasses' => 'blob NULL',
] as $field => $sql) {
    $GLOBALS['TL_DCA']['tl_content']['fields'][$field]['sql'] = $sql;
}

/*
 * Wrapper start/stop palettes (verbatim from dma/dma_simple_grid).
 */
$GLOBALS['TL_DCA']['tl_content']['palettes']['dma_simplegrid_wrapper_start'] = '{type_legend},type;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID;{invisible_legend:hide},invisible,start,stop';
$GLOBALS['TL_DCA']['tl_content']['palettes']['dma_simplegrid_wrapper_stop'] = '{type_legend},type;{protected_legend:hide},protected;{expert_legend:hide},guests;{invisible_legend:hide},invisible,start,stop';
