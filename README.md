# Contao Grid Plugin

[![License](https://img.shields.io/packagist/l/think-digital-agency/contao-grid-plugin.svg)](LICENSE)

**[English]** A lightweight multi-breakpoint grid for Contao content elements.
Adds *column* and *offset* settings (xs–xl) to every content element and a grid
*wrapper start / stop* pair, emitting Bootstrap-style `col-*` / `offset-*`
classes. Drop-in replacement for `dma/dma_simple_grid` — **without** its
`menatwork/contao-multicolumnwizard-bundle` dependency. Contao 5 and 6.

```bash
composer require think-digital-agency/contao-grid-plugin
```

---

**Ein schlankes Breakpoint-Grid für Contao-Inhaltselemente.** Jedes Element
bekommt *Spalten-* und *Offset-Einstellungen* (xs–xl), dazu ein *Wrapper-Paar*
(Start/Stop) für Grid-Zeilen. Ausgabe: `col-*` / `offset-*` (Bootstrap-Stil).

## Funktionsweise

- **Spalten-/Offset-Einstellungen** — zwei Felder (`gridSettingsWizard`, je fünf
  Breakpoint-Selects) werden in jede Inhaltselement-Palette mit `cssID`
  eingehängt. Gespeichert im Feld `dma_simplegrid_columnsettings` /
  `dma_simplegrid_offsetsettings` (Namen aus DMA übernommen → keine Migration).
- **`simple_grid_classes()`** — Twig-Funktion, liefert die Grid-Klassen der
  aktuellen Zeile (`context.data`). Name und Verhalten wie bei DMA.
- **`parseTemplate`-Hook** — hängt dieselben Klassen an `$template->class`, für
  Legacy-Templates die das flache `class` lesen.
- **Wrapper `dma_simplegrid_wrapper_start` / `_stop`** — Fragment-Controller,
  über `$GLOBALS['TL_WRAPPERS']` als Start/Stop-Paar registriert.

## Grid-Konfiguration

Ein festes Grid (`GridConfig`), entspricht dem `bootstrap4`-Preset von DMA:

| Breakpoint | Spalte      | Offset         |
|------------|-------------|----------------|
| xs         | `col-N`     | `offset-N`     |
| sm/md/lg/xl| `col-{bp}-N`| `offset-{bp}-N`|

Spaltenwert `hide` → `d-{bp}-none`, Offsetwert `reset` → `offset[-{bp}]-0`.
Kein Framework-Auswahl-Dialog (DMA `tl_settings`) — bewusst genau ein Grid.

## Voraussetzungen

- PHP 8.2+
- Contao 5.3 – 6.x

## Migration von `dma/dma_simple_grid`

```bash
composer remove dma/dma_simple_grid
composer require think-digital-agency/contao-grid-plugin
php bin/console cache:clear && php bin/console cache:warmup
php bin/console contao:migrate
```

Feld- und Elementtyp-Namen bleiben identisch → **kein** Content-Update nötig.
Nicht portiert (0 Nutzung im Ziel-Theme): row-/column-Elemente, Formularfeld-
Typen, push/pull/offset-right/block-grid/zusätzliche Klassen. Die zugehörigen
Spalten bleiben erhalten (SQL-only) und werden von einer späteren Migration
entfernt.

## Lizenz

LGPL-3.0-or-later — siehe [LICENSE](LICENSE). `dma/dma_simple_grid` (LGPL-3.0+)
diente als Vorlage.

Entwickelt von [Think Digital Agency](https://think-digital.agency).
