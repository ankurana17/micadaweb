# MICADA Website

This repository is a WordPress site with a custom Astra child theme and a single regular plugin for MICADA content management.

## Project boundaries

```text
WordPress core and configuration
└── wp-content
    ├── plugins/micada-cms/       CMS modules, schema, migrations, and initial content snapshot
    ├── themes/astra-child/       public templates, styles, scripts, and theme integrations
    └── uploads/                  media uploaded through WordPress
```

The **MICADA CMS** plugin owns admin screens, custom content tables, content migrations, and post types such as Officials and Banners. The child theme owns presentation and public page templates. The theme reads CMS data through plugin functions; it should not recreate CMS storage or seed database records.

## Local setup

1. Install WordPress and configure the local database in `wp-config.php`.
2. Copy the `wp-content` project files into the installation.
3. In WordPress Admin, activate Astra, Astra Child, and **MICADA CMS**.
4. Open the homepage and the CMS menus to confirm the site is available.

Keep database credentials in `wp-config.php` or environment-specific configuration. Do not commit that file.

## Deployment flow

1. Back up the source WordPress database and `wp-content/uploads/`.
2. Configure the destination database credentials and `$table_prefix` in the destination `wp-config.php`.
3. Deploy the child theme, the complete `wp-content/plugins/micada-cms/` folder, and uploaded media.
4. Remove any older MICADA CMS PHP modules from `wp-content/mu-plugins/` so each module loads once.
5. Activate **MICADA CMS** from **Plugins**. Activation creates or upgrades its tables, runs legacy migrations, and can import the bundled initial snapshot into empty CMS tables and post types.
6. Review the homepage and CMS content, then take a fresh destination database backup.

The bundled snapshot is a one-time bootstrap for an empty destination. The WordPress database and uploads directory are the source of truth after activation; export/import them for future deployments to include later edits. Existing destination content is not overwritten by snapshot import.

## Data and media

CMS rows are stored in the database. Images and documents remain in WordPress uploads; the database stores their URLs or attachment references. Copy uploads with the database so media continues to resolve. Internal links are resolved against the current WordPress site where the CMS renderer supports it.

See [the CMS plugin README](wp-content/plugins/micada-cms/README.md) and [the child theme README](wp-content/themes/astra-child/README.md) for component details.
