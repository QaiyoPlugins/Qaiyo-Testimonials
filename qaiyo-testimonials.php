<?php
/**
 * Plugin Name: Qaiyo Testimonials
 * Plugin URI: https://qaiyo-plugins.com/qaiyo-testimonials/
 * Description: Egyedi ügyfél visszajelzés / testimonial plugin 5 különböző design verzióval (sor slider, oszlop slider, kártya carousel, bento grid, fotó-galéria váltó). Kategorizálható, többnyelvű, teljesen testreszabható megjelenítéssel.
 * Version: 1.0.0
 * Requires at least: 5.8
 * Requires PHP: 7.4
 * Author: Qaiyo by PixelDesigns
 * Author URI: https://qaiyo-plugins.com
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: qaiyo-testimonials
 * Domain Path: /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'QT_VERSION', '1.0.0' );
define( 'QT_FILE', __FILE__ );
define( 'QT_DIR', plugin_dir_path( __FILE__ ) );
define( 'QT_URL', plugin_dir_url( __FILE__ ) );
define( 'QT_TEXT_DOMAIN', 'qaiyo-testimonials' );
define( 'QT_POST_TYPE', 'qaiyo_testimonial' );
define( 'QT_TAXONOMY', 'qt_category' );
define( 'QT_MAX_UPLOAD_BYTES', 1048576 ); // 1 MB

/**
 * Asset cache-busting version: the file's modification time when readable,
 * otherwise the plugin version. Guarantees CSS/JS changes are picked up
 * without a manual version bump (and stays unique per release).
 *
 * @param string $relative_path Path relative to the plugin root, e.g. 'assets/css/admin.css'.
 * @return string
 */
function qt_asset_ver( $relative_path ) {
	$file = QT_DIR . ltrim( $relative_path, '/' );
	$mtime = is_readable( $file ) ? filemtime( $file ) : 0;
	return $mtime ? QT_VERSION . '.' . $mtime : QT_VERSION;
}

require_once QT_DIR . 'includes/class-qt-i18n.php';
require_once QT_DIR . 'includes/class-qt-svg.php';
require_once QT_DIR . 'includes/class-qt-catalog.php';
require_once QT_DIR . 'includes/class-qt-brand-menu.php';
require_once QT_DIR . 'includes/class-qt-cpt.php';
require_once QT_DIR . 'includes/class-qt-meta.php';
require_once QT_DIR . 'includes/class-qt-settings.php';
require_once QT_DIR . 'includes/class-qt-display.php';
require_once QT_DIR . 'includes/class-qt-assets.php';
require_once QT_DIR . 'includes/class-qt-shortcode.php';

function qt_init_plugin() {
	Qt_I18n::init();
	Qt_Brand_Menu::init();
	Qt_Cpt::init();
	Qt_Meta::init();
	Qt_Settings::init();
	Qt_Display::init();
	Qt_Assets::init();
	Qt_Shortcode::init();
}
add_action( 'plugins_loaded', 'qt_init_plugin' );

register_activation_hook( __FILE__, array( 'Qt_Cpt', 'activate' ) );
register_deactivation_hook( __FILE__, 'flush_rewrite_rules' );
