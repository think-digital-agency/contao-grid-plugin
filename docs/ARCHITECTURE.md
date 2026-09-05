# ARCHITECTURE.md – Contao Grid Plugin

## Tree

```
contao-grid-plugin-bundle/
├── composer.json                       think-digital-agency/contao-grid-plugin
├── contao/
│   ├── config/config.php               TL_WRAPPERS + BE_FFL['gridSettingsWizard']
│   ├── dca/tl_content.php               grid fields, wrapper palettes, SQL-only legacy columns
│   ├── dca/tl_form_field.php            SQL-only legacy columns (feature not ported)
│   └── languages/{de,en}/{default,tl_content}.php
├── docs/
│   ├── ARCHITECTURE.md · DECISIONS.md
│   └── package-metadata/{de,en,logo.svg}
├── public/backend/
│   ├── grid-settings.css               back-end widget styles
│   └── grid-settings.js                live preview + click/drag bars
│                                       (served as bundles/contaogridplugin/…,
│                                        registered via TL_CSS / TL_JAVASCRIPT)
└── src/
    ├── ContaoGridPluginBundle.php
    ├── ContaoManager/Plugin.php
    ├── DependencyInjection/ContaoGridPluginExtension.php
    ├── Resources/config/services.yaml  autowire scan (excludes Grid/GridConfig, Widget/)
    ├── Grid/
    │   ├── GridConfig.php               the one fixed grid (static)
    │   └── GridClasses.php              row -> "col-* offset-*"  (service)
    ├── Twig/GridClassesExtension.php    simple_grid_classes()
    ├── EventListener/
    │   ├── ParseTemplateListener.php    #[AsHook('parseTemplate')]
    │   └── ContentPaletteListener.php   #[AsCallback] config.onload + config.onsubmit
    ├── Widget/GridSettingsWizard.php    BE_FFL 'gridSettingsWizard'
    └── Controller/ContentElement/
        ├── GridWrapperStartController.php   dma_simplegrid_wrapper_start
        └── GridWrapperStopController.php    dma_simplegrid_wrapper_stop
```

The wrapper Twig templates (`content_element/grid_wrapper_{start,stop}.html.twig`)
live in the **consuming theme's** `templates/` (project-root, global namespace),
not in the bundle: their markup (`u-root` / `u-width-medium`) is theme-specific,
and the back-end element preview has no theme context (Design+ ADR-10). A generic
default template may be added to the bundle later.

## Data flow

```
tl_content row
  ├─ dma_simplegrid_columnsettings  (a:1:{i:0;a:5:{xs..xl}})
  └─ dma_simplegrid_offsetsettings
        │
        ▼
   GridClasses::forRow($row)  ──►  "col-md-6 col-lg-4 offset-md-1"
        │                                   │
        │ parseTemplate hook                │ simple_grid_classes() Twig fn
        ▼                                   ▼
   legacy templates ($template->class)   modern fragment templates (designplus_*)
```

The Design+ theme also has its own `_macros/grid.html.twig` that parses the same
columns independently (for templates extending `_base.html.twig`) — it does not
call into this bundle. The bootstrap4 mapping matches on both sides.

## Value format

`GridSettingsWizard` reads/writes exactly what the previous single-row
MultiColumnWizard field did:
`serialize([ ['xs' => '', 'sm' => '6', 'md' => '', 'lg' => '3', 'xl' => ''] ])`.
So existing content needs no migration.
