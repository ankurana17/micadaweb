<?php
/**
 * Plugin Name: MICADA CMS
 * Description: Unified CMS administration, storage, modules, and database migrations for MICADA.
 * Version: 2.1.0
 * Author: MICADA
 * Text Domain: micada
 */

defined( 'ABSPATH' ) || exit;

define( 'MICADA_CMS_VERSION', '2.1.0' );
define( 'MICADA_CMS_PLUGIN_FILE', __FILE__ );
define( 'MICADA_CMS_PLUGIN_DIR', __DIR__ );

// Core shared tools
require_once __DIR__ . '/modules/cms-trash.php';
require_once __DIR__ . '/modules/image-dimension-rules.php';

// CMS Modules
require_once __DIR__ . '/modules/officials.php';
require_once __DIR__ . '/modules/banners.php';
require_once __DIR__ . '/modules/quick-links.php';
require_once __DIR__ . '/modules/news-events.php';
require_once __DIR__ . '/modules/faqs.php';
require_once __DIR__ . '/modules/important-links.php';
require_once __DIR__ . '/modules/galleries.php';
require_once __DIR__ . '/modules/homepage-messages.php';
require_once __DIR__ . '/modules/homepage-popup.php';
require_once __DIR__ . '/modules/organisation.php';
require_once __DIR__ . '/modules/rti.php';

// Migration & Activation
require_once __DIR__ . '/includes/migration.php';
require_once __DIR__ . '/includes/activation.php';
