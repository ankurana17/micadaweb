# MICADA Astra child theme

The child theme owns the public website presentation: page templates, homepage markup, styles, browser interactions, and theme integrations. CMS data and admin workflows belong to the regular **MICADA CMS** plugin.

## Main locations

```text
astra-child/
├── functions.php                  Theme bootstrap
├── page-micada.php                Homepage template entry point
├── inc/
│   ├── cms.php                    Shared public helpers, language, and assets
│   ├── navigation.php             WordPress menus and application link routing
│   └── home.php                   Homepage rendering/data adapters
├── page-templates/                Public page templates
├── template-parts/
│   ├── header.php                 Shared site header
│   ├── footer.php                 Shared site footer
│   └── home/                      Homepage sections
└── assets/
    ├── css/                       Theme and responsive styles
    ├── js/                        Frontend interactions
    ├── images/                    Theme-provided graphics and defaults
    ├── fonts/                     Local fonts
    ├── documents/                 Theme-provided documents
    └── data/                      Translations and non-CMS defaults
```

## Common edits

| Task | Location |
| --- | --- |
| Homepage structure | `page-micada.php`, `template-parts/home/` |
| Public page layout | `page-templates/` |
| Header and footer | `template-parts/header.php`, `template-parts/footer.php` |
| Homepage styles | `assets/css/home.css`, `assets/css/home-typography.css` |
| Shared public styles | `assets/css/public.css` |
| Homepage interactions | `assets/js/home.js` |
| Language and navigation | `assets/js/public.js`, `inc/navigation.php` |

Uploaded CMS images and documents belong in WordPress uploads and are managed by the CMS plugin. Keep page presentation here; do not duplicate CMS database queries, table installers, or admin handlers in the theme.

Deployment and database migration steps are documented in the repository [README](../../../README.md) and [MICADA CMS plugin README](../../plugins/micada-cms/README.md).
