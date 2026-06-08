<?php

/**
 * Plugin Name:       WooCommerce Coupon Display Manager
 * Plugin URI:        https://eraai.id
 * Description:       Control the position, style, and behavior of the WooCommerce coupon field on any checkout page — including CartFlows, FunnelKit, Block Checkout, and the WooCommerce default.
 * Version:           1.0.0
 * Author:            ERA AI
 * Author URI:        https://eraai.id
 * Text Domain:       woo-coupon-display-manager
 * Domain Path:       /languages
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * WC requires at least: 7.0
 * WC tested up to:   9.9
 *
 * @package WooCouponDisplayManager
 */
if (!defined('ABSPATH')) {
	exit;
}

// Plugin constants.
define('WCDM_VERSION', '1.0.0');
define('WCDM_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WCDM_PLUGIN_URL', plugin_dir_url(__FILE__));
define('WCDM_PLUGIN_BASENAME', plugin_basename(__FILE__));

/** Declare HPOS compatibility. */
add_action('before_woocommerce_init', function () {
	if (class_exists('\Automattic\WooCommerce\Utilities\FeaturesUtil')) {
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility(
			'custom_order_tables',
			__FILE__,
			true
		);
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility(
			'cart_checkout_blocks',
			__FILE__,
			true
		);
	}
});

/**
 * Main Plugin Class — singleton bootstrap.
 */
class Woo_Coupon_Display_Manager
{
	/** @var Woo_Coupon_Display_Manager */
	private static $instance = null;

	public static function get_instance()
	{
		if (null === self::$instance) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct()
	{
		add_action('plugins_loaded', array($this, 'init'));
	}

	public function init()
	{
		if (!class_exists('WooCommerce')) {
			add_action('admin_notices', array($this, 'woocommerce_missing_notice'));
			return;
		}

		// Load text domain.
		load_plugin_textdomain(
			'woo-coupon-display-manager',
			false,
			dirname(WCDM_PLUGIN_BASENAME) . '/languages'
		);

		// Load all includes.
		$this->includes();

		// Boot classes.
		if (is_admin()) {
			WCDM_Settings::get_instance();
		}

		WCDM_Frontend::get_instance();
		WCDM_Blocks::get_instance();
	}

	private function includes()
	{
		require_once WCDM_PLUGIN_DIR . 'includes/class-wcdm-compat.php';
		require_once WCDM_PLUGIN_DIR . 'includes/class-wcdm-settings.php';
		require_once WCDM_PLUGIN_DIR . 'includes/class-wcdm-frontend.php';
		require_once WCDM_PLUGIN_DIR . 'includes/class-wcdm-blocks.php';
	}

	public function woocommerce_missing_notice()
	{
		?>
		<div class="notice notice-error">
			<p>
				<?php
				esc_html_e(
					'WooCommerce Coupon Display Manager requires WooCommerce to be installed and active.',
					'woo-coupon-display-manager'
				);
				?>
			</p>
		</div>
		<?php
	}
}

// Boot.
Woo_Coupon_Display_Manager::get_instance();
