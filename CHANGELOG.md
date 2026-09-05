# Changelog

All notable changes to this project will be documented in this file.
The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/).

## [2.0.1] - 2026-09-05

### Changed
- Removed remaining references to the previous grid extension this bundle
  replaces from code comments and docs (kept: the `dma_simplegrid_*` field
  and element-type names, which are real DB/DCA identifiers needed for
  backward compatibility, not a package reference).
- Removed `docs/PUBLISHING.md` — publishing is complete, and the two sibling
  bundles never carried this file either.

## [2.0.0] - 2026-09-05

Initial release — built for the Contao Design+ theme (Contao 6 migration,
WP-2) as a lightweight multi-breakpoint grid for content elements. Removes
the transitive `menatwork/contao-multicolumnwizard-bundle` dependency.
Package version starts at 2.0.0 rather than 1.0.0 — see `docs/DECISIONS.md`
ADR-008.

### Added
- `GridConfig` / `GridClasses` — one fixed grid definition (`bootstrap4`-style
  preset, columns + offset), `col-*` / `offset-*` output, `hide` → `d-{bp}-none`,
  `reset` → `offset[-{bp}]-0`
- `simple_grid_classes()` Twig function (`GridClassesExtension`)
- `ParseTemplateListener` — `parseTemplate` hook, appends grid classes to
  `$template->class` for legacy templates
- `GridWrapperStartController` / `GridWrapperStopController` — fragment
  controllers for `dma_simplegrid_wrapper_start` / `_stop`, registered as a
  `TL_WRAPPERS` start/stop pair
- `GridSettingsWizard` — `gridSettingsWizard` back-end widget, a five-select
  MultiColumnWizard replacement; value stored in the same `a:1:{i:0;a:5:{…}}`
  format as before
- `ContentPaletteListener` — injects the column/offset fields into every content
  element palette with `cssID`, auto-creates the wrapper stop element
- Dual compatibility: `contao/core-bundle: ^5.3 || ^6.0`

### Not ported from the previous grid solution
- Row / column start-stop content elements (0 rows in use)
- Form field types + `loadFormField` hook (0 rows in use)
- push / pull / offset-right / block grid / additional-classes features
- The `tl_settings` grid-framework picker (~12 presets) — one fixed grid instead

The DB columns for the unported features are kept SQL-only for rollback safety
and dropped by a later migration.
