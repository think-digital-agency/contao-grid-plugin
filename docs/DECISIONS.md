# DECISIONS.md – Contao Grid Plugin

Bundle-level ADRs. The theme-level rationale is Design+ `CONTAO6_MIGRATION.md`
ADR-14 (which this mirrors).

## ADR-001 · Replace `dma/dma_simple_grid`, don't fork it

DMA is stuck at `contao/core-bundle: ^4.13 || ^5.0` and pulls
`menatwork/contao-multicolumnwizard-bundle` (`^5.7`, no Contao 6). Both block the
Contao 6 upgrade. We reimplement only what the theme uses, with modern APIs
(fragment controllers, `#[AsHook]`, `#[AsCallback]`, `strict_types`,
constructor injection).

## ADR-002 · Keep field and element-type names

`dma_simplegrid_columnsettings`, `dma_simplegrid_offsetsettings`,
`dma_simplegrid_wrapper_start`, `dma_simplegrid_wrapper_stop` are kept verbatim so
existing content needs **no migration**. The names are ugly but harmless; renaming
would mean a key-matched migration across 100–300 customer sites for no gain.

## ADR-003 · One fixed grid = DMA `bootstrap4`

The theme runs `dmaSimpleGridType: 'bootstrap4'` (`config/config.yml`). That is
hard-coded in `GridConfig`; the `tl_settings` framework picker and the ~12 presets
are dropped. `tl_settings` does not even exist as a table in this Contao 5 setup.

## ADR-004 · Columns + offset only

DB check on the theme demo: 753 rows use column settings, 723 use offset, **0**
use push / pull / offset-right / block grid / additional-classes. Those features
and their `loadFormField` / additional-class plumbing are not ported. The columns
stay SQL-only (rollback safety) and a later migration drops them.

## ADR-005 · Own back-end widget instead of MultiColumnWizard

`GridSettingsWizard` is a `Contao\Widget` subclass rendering five `<select>`s
(xs–xl) and serialising to the identical `a:1:{i:0;a:5:{…}}` shape. This is the
whole reason `menatwork/contao-multicolumnwizard-bundle` can be removed.

## ADR-006 · Wrapper templates live in the consuming theme

The wrapper markup is theme-specific (`u-root`, `u-width-medium`, `row`). The
controllers reference `content_element/grid_wrapper_{start,stop}`; the theme
provides those files in its project-root `templates/` (resolvable in the back-end
preview too — Design+ ADR-10). A neutral fallback template in the bundle is a
possible later addition.

## ADR-007 · `parseTemplate` hook AND Twig function

Two independent consumers in the theme need the classes:
`parseTemplate` for legacy `.html.twig` reading flat `class`
(`block_searchable`, `mod_calendar`, the wrapper), and `simple_grid_classes()`
for the modern `designplus_*` fragment templates (the hook does not fire for
fragments). Both delegate to the same `GridClasses` service.
