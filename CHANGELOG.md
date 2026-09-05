# Changelog

All notable changes to this project will be documented in this file.
The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/).

## [1.0.0] - 2026-09-03

Initial release — replaces `dma/dma_simple_grid` in the Contao Design+ theme
(Contao 6 migration, WP-2). Removes the transitive
`menatwork/contao-multicolumnwizard-bundle` dependency.

### Added
- `GridConfig` / `GridClasses` — one fixed grid definition (DMA `bootstrap4`
  preset, columns + offset), `col-*` / `offset-*` output, `hide` → `d-{bp}-none`,
  `reset` → `offset[-{bp}]-0`
- `simple_grid_classes()` Twig function (`GridClassesExtension`) — name and
  behaviour kept identical to DMA
- `ParseTemplateListener` — `parseTemplate` hook, appends grid classes to
  `$template->class` for legacy templates
- `GridWrapperStartController` / `GridWrapperStopController` — fragment
  controllers for `dma_simplegrid_wrapper_start` / `_stop`, registered as a
  `TL_WRAPPERS` start/stop pair
- `GridSettingsWizard` — `gridSettingsWizard` back-end widget, a five-select
  MultiColumnWizard replacement; value stored in the identical
  `a:1:{i:0;a:5:{…}}` format
- `ContentPaletteListener` — injects the column/offset fields into every content
  element palette with `cssID`, auto-creates the wrapper stop element
- Dual compatibility: `contao/core-bundle: ^5.3 || ^6.0`

### Not ported from `dma/dma_simple_grid`
- Row / column start-stop content elements (0 rows in use)
- Form field types + `loadFormField` hook (0 rows in use)
- push / pull / offset-right / block grid / additional-classes features
- The `tl_settings` grid-framework picker (~12 presets) — one fixed grid instead

The DB columns for the unported features are kept SQL-only for rollback safety
and dropped by a later migration.
