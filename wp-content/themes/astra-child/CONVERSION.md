# MICADA WordPress frontend — current status

The native public homepage now closely follows https://micada.haryana.gov.in/home. Full conversion of authenticated Angular workflows is not complete.

## Implementation

All implementation files are inside `wp-content/themes/astra-child` in the existing `C:\xampp\htdocs\micadaweb` installation. No new project, backend, database, tables or API endpoints were created. Existing page content, template assignments, Elementor records and the Angular project were retained.

- `page-micada.php` supplies the missing template already assigned to front page 1315. The native homepage is available at `http://127.0.0.1/micadaweb/`.
- `inc/home.php` supplies homepage CMS controls, reference navigation, local public-page links and in-place public content panels. `inc/cms.php` supplies native public assets, bilingual editorial fields, template metadata, nonce/capability checks and embed validation. `functions.php` loads these files.
- `template-parts/` and `template-parts/home/` contains the original header/footer, leadership cards, default About content and quick links. Source images, fonts, selected documents, compiled/scoped original CSS, translations and literal gallery data are under `assets/` (grouped by file type).
- `assets/css/home.css` and `home.js` adapt the source design to native HTML: orange navigation, five application links, maintenance notice, six rotating banners, four officials, 13 photos, ten homepage videos, scrolling links/galleries, modal notice and keyboard-capable photo preview.
- Files under `page-templates/` restore existing missing public template assignments. Their common implementation renders existing WordPress content. Original Elementor home/photo/video pages are retained; navigation uses the existing native public page records.

The current live reference includes an EOI document and maintenance notice absent from the local Angular snapshot. Both are reflected in the homepage. The EOI document and one locally missing amended vendor document link to their existing official URLs. The maintenance notice is editable; it is not a live health check.

## CMS editing

- Appearance → Customize → MICADA homepage: opening notice toggle/image, maintenance messages, per-language About/news content page selectors, officials' images and text, banner images and captions.
- Appearance → Customize → MICADA public content: per-language heading, address, telephone and email.
- Appearance → Menus: assign MICADA homepage navigation or MICADA quick links to override the default reference menus.
- Pages: edit existing About Us, RTI, officers, office orders, circulars and other public content. These retain their existing page records. RTI and officers/office-order navigation can render that content within the homepage, preserving the reference interaction.
- Native public templates also offer a page-type selector and optional approved video embed list. Existing gallery content is retained; explicit video-list metadata overrides it. Empty photo/story pages preserve the original “Available soon!” state.

Language switching uses the original `{name, code}` localStorage format (`en`/`hd`), with a query parameter for server rendering. Reference translations are reused. Existing CMS body content remains in its stored language unless the editor supplies a language-specific content page.

## Verification completed

- Inspected the live reference in headless Chrome and compared desktop screenshots against the local homepage.
- PHP syntax checks passed for every theme PHP file; native JavaScript syntax checks passed.
- Browser interaction assertions passed at desktop width 1440 and mobile width 390: automatic notice opening/closing, banner indicator selection and three-second autoplay, photo preview opening/next/previous/closing, menu dropdown opening/outside closing, mobile menu opening/closing, RTI panel loading/history update, and About panel restoration.
- Both viewports passed the horizontal-overflow check, retained all five application destinations, 13 photos and ten homepage video embeds, and reported no uncaught JavaScript exceptions.
- Browser language switching to Hindi passed URL, selector, localStorage and translated-heading checks.
- Read-only HTTP checks passed for the homepage, 21 existing CMS public pages and 50 unique referenced local theme assets.
- Read-only template checks passed in English/Hindi and retained all 19 standalone video defaults, including the original duplicate entry.
- No production forms were submitted and no application API records were created or changed. Embedded third-party playback, authenticated role workflows, public CMS editing round trips, and every external document/link have not been fully verified.

Reproducible checks: `php tools/verify-public.php`, `node tools/verify-pages.cjs`. Run from the theme directory, using the installed XAMPP PHP binary where necessary. Browser tooling uses a dedicated headless Chrome instance on port 9227 and the existing Angular project's `ws` package. Asset importers read the existing Angular checkout and its installed TypeScript/Sass dependencies; run the public importer before the home importer because the latter expands the translation bundle.

Pretty-path requests returned HTTP 404 in the existing local server configuration. Native links therefore use working WordPress page-ID URLs; no permalink/server configuration was changed.

## Remaining work and limits

The five application buttons, login/vendor-registration links, target report and portal-opening-status link currently open the existing official application. This preserves access to those services but is not a native WordPress conversion of them. Cross-origin sessionStorage cannot be shared between localhost and the official application; the original application initializes its own login state.

Authentication/OTP, protected dashboards, business forms and validation, protected searches/filters, uploads, maps, charts, reports and exports remain Angular functionality. The source API base remains `https://micada.haryana.gov.in:8443/api/`; no replacement API or WordPress authentication layer has been introduced. These workflows require dedicated ports and nonproduction role-based verification before claiming full functionality parity.

The installed WordPress accessibility widget remains enabled and is visible locally; it is not part of the reference design. Existing public CMS tables may contain older editorial data and do not imply a live API-backed report. No claim of pixel-perfect equivalence, complete frontend conversion or 100% end-to-end functionality parity is made.

## Typography follow-up

Updated `assets/css/home.css` using computed styles measured from the live reference. Corrected menu sizing, application button weight, heading/caption line heights, Inter font declarations, Arial contact text, footer sizes, and responsive typography. Compared 29 sampled text/style elements across font family, size, weight, style, line height and letter spacing: zero mismatches at viewport widths 1440 and 390. Browser runs reported no uncaught JavaScript errors or horizontal overflow. The measurement helper is `tools/measure-typography.js`; no backend or content changes were required.


## Approved folder reorganization

Assets are now grouped under css, js, images, fonts, documents and data. Shared PHP logic is under inc/cms.php, inc/navigation.php and inc/home.php. Homepage sections are under template-parts/home; shared header/footer are directly under template-parts. Page-template assignments and CMS data were retained. Import utilities now target the organized paths. See README.md for the developer map. This structural change does not complete any previously outstanding application migration work.

Reorganization verification: PHP/importer syntax checks passed; the homepage, 21 public pages and 50 referenced local assets returned HTTP 200. Before/after DOM comparisons passed for the English/Hindi homepage, Activities, standalone video gallery and RTI panel after accounting for relocated asset URLs. Stylesheet font/resource paths resolve. Desktop and mobile interaction assertions passed, including popup, carousel, photo preview, menus and content panels.

A pre-existing parent-Astra script issue was observed: `astScrollToTopHandler` calls `getComputedStyle` on the removed back-to-top element while Astra's `is_scroll_to_top` setting remains enabled. The pre-reorganization output already contains that configuration and lacks the element. It was not changed under the folder-only approval.
