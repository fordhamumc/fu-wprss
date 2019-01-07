<?php
/**
 * @package           FU_WPRSS
 * @since             0.0.1
 *
 * @wordpress-plugin
 * Plugin Name:       Fordham WP RSS Aggregator Edits
 * Plugin URI:        http://news.fordham.edu
 * Description:       Edits to the WP RSS Aggregator Plugin.
 * Version:           0.1.1
 * Author:            Michael Foley
 * Author URI:        https://michaeldfoley.com
 * License:           GPLv3
 * License URI:       https://www.gnu.org/licenses/gpl-3.0.txt
 * Text Domain:       fu-wprss
 * Domain Path:       /languages
 */

namespace FU_WPRSS;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Define Constants
 */

$min_php = '5.6.0';

define( __NAMESPACE__ . '\NS', __NAMESPACE__ . '\\' );

define( NS . 'PLUGIN_NAME', 'fu_wprss' );

define( NS . 'PLUGIN_TITLE', 'Fordham WP RSS Aggregator Edits' );

define( NS . 'PLUGIN_VERSION', '0.1.1' );

define( NS . 'WPRSS_MIN_VERSION', '4.0.0' );

define( NS . 'PLUGIN_NAME_DIR', plugin_dir_path( __FILE__ ) );

define( NS . 'PLUGIN_NAME_URL', plugin_dir_url( __FILE__ ) );

define( NS . 'PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

define( NS . 'PLUGIN_TEXT_DOMAIN', 'fu-wprss' );

if( !defined( 'WPRSS_FTP_USER_AJAX_COUNT_THRESHOLD' ) )
    define( 'WPRSS_FTP_USER_AJAX_COUNT_THRESHOLD', 40 );


/**
 * Autoload Classes
 */
require_once( PLUGIN_NAME_DIR . 'vendor/autoload.php');

/**
 * Register Activation and Deactivation Hooks
 * This action is documented in inc/core/class-activator.php
 */

register_activation_hook( __FILE__, array( NS . 'Src\Core\Activator', 'activate' ) );

/**
 * The code that runs during plugin deactivation.
 * This action is documented inc/core/class-deactivator.php
 */

register_deactivation_hook( __FILE__, array( NS . 'Src\Core\Deactivator', 'deactivate' ) );


/**
 * Plugin Singleton Container
 *
 * Maintains a single copy of the plugin app object
 *
 * @since    0.0.1
 */
class FU_WPRSS {

	/**
	 * The instance of the plugin.
	 *
	 * @since    0.0.1
	 */
	private static $init;
	/**
	 * Loads the plugin
	 *
	 * @access    public
	 */
	public static function init() {

		if ( null === self::$init ) {
			self::$init = new core\Init();
			self::$init->run();
		}

		return self::$init;
	}

}

/**
 * Begins execution of the plugin
 **/
function fu_wprss_init() {
  return FU_WPRSS::init();
}


if ( version_compare( PHP_VERSION, $min_php, '>=' ) ) {
  fu_wprss_init();
}


