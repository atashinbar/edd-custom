<?php
/**
 * Plugin Name:       Edd Customization
 * Plugin URI:        https://example.com
 * Description:       Edd Customization.
 * Version:           1.0.0
 * Requires at least: 5.2
 * Requires PHP:      5.6
 * Author:            Author
 * Author URI:        https://example.com
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       edd-custom
 * Domain Path:       /edd-custom
 */

defined( 'ABSPATH' ) || exit;

define( 'EDD_CUSTOM_PATH', plugin_dir_path( __FILE__ ) );
define( 'EDD_CUSTOM_URL', plugin_dir_url( __FILE__ ) );

include EDD_CUSTOM_PATH . 'includes/checkout/signup-form.php';

