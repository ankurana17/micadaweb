# MICADA CMS Plugin

Unified WordPress CMS plugin for MICADA administration, database storage, modules, and content migrations.

## Structure

- `micada-cms.php`: Single entry point for the plugin, loading all modules and migration routines.
- `modules/`: Modular functional components:
  - `banners.php`: Home banners and captions (Custom Post Type: `micada_banner`).
  - `officials.php`: Leadership cards and official photos (Custom Post Type: `micada_official`).
  - `quick-links.php`: Homepage quick links marquee and administration (`wp_quick_links`).
  - `news-events.php`: Homepage news & events ticker and administration (`wp_news_events`).
  - `faqs.php`: Bilingual FAQ PDF documents and navigation (`wp_micada_faq_documents`).
  - `important-links.php`: Footer important partner links and logos (`wp_micada_important_links`).
  - `galleries.php`: Photo and video galleries (`wp_photo_gallery`, `wp_video_gallery`).
  - `homepage-messages.php`: Scrolling tickers, notices, and important messages (`wp_homepage_messages`, `wp_homepage_notices`, `wp_important_messages`).
  - `homepage-popup.php`: Homepage opening modal notices (`wp_homepage_popup`).
  - `organisation.php`: Head office executives, field executives, and office orders (`wp_micada_head_office_executives`, `wp_micada_field_executives`, `wp_micada_office_orders`).
  - `rti.php`: State Public Information Officers list (`wp_state_public_information_officers`).
  - `cms-trash.php`: Shared soft-trash, restore, and permanent deletion controls.
  - `image-dimension-rules.php`: Slot-based image dimension guidance and validation.
- `includes/`:
  - `activation.php`: Plugin activation hook, child theme/homepage initialization, and table installer dispatcher.
  - `migration.php`: Content snapshot migration, legacy table migration, column mapper, and admin migration tools.
- `data/`:
  - `initial-content.json`: Bundled initial content snapshot for all tables and post types.

## Migration & Database Table Management

When activated (or through the Admin Migration Tool at **Tools > MICADA CMS Migration**):
1. **Creates Tables if Not Present**: Executes `dbDelta()` schema creation for all 11 modules and registers custom post types.
2. **Migrates Data if Table is Present**:
   - If a legacy table exists (e.g. `wp_micada_quick_links`, `wp_homepage_ticker`, `wp_rti_officers`, `wp_micada_organisation_items`), data is automatically migrated into the current table structure with column transformation.
   - If the destination table has 0 rows (or on forced re-sync), records from `data/initial-content.json` are imported.
   - For `quick_links`: Automatically transforms legacy fields (`title_en` -> `label_en`, `title_hd` -> `label_hi`, `target` -> `new_tab`, `is_new` -> `show_new`, `display_order` -> `sort_order`, `status` -> `display_status`), ensuring quick links display immediately on the homepage.
3. **Display Status & Trash Support**: Automatically updates and indexes the `display_status` column across all CMS tables.
