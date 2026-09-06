<?php

declare(strict_types=1);

/*
 * The predecessor grid solution also added grid columns and form-field types
 * to tl_form_field. This bundle does not port the form-field feature (0 rows in use). The columns are kept SQL-only
 * for rollback safety (ADR-4b pattern) and dropped by a dedicated later
 * migration.
 */
foreach ([
    'dma_simplegrid_columnsettings' => 'text NULL',
    'dma_simplegrid_offsetsettings' => 'text NULL',
    'dma_simplegrid_pushsettings' => 'text NULL',
    'dma_simplegrid_pullsettings' => 'text NULL',
    'dma_simplegrid_additionalrowclasses' => 'blob NULL',
] as $field => $sql) {
    $GLOBALS['TL_DCA']['tl_form_field']['fields'][$field]['sql'] = $sql;
}
