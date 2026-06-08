<?php
/**
 * Frontend Checkout Customization — Reposition Mode.
 *
 * Removes the native WooCommerce coupon field from its default location
 * and renders it below the Place Order button across all checkout builders:
 *  - WooCommerce Classic (shortcode)
 *  - CartFlows (free & pro)
 *  - FunnelKit / WooFunnels
 *  - Block Checkout (handled by class-wcdm-blocks.php)
 *  - Any page embedding [woocommerce_checkout]
 *
 * @package WooCouponDisplayManager
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WCDM_Frontend {

	/** @var WCDM_Frontend */
	private static $instance = null;

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		$activated = get_option( 'wcdm_activated', get_option( 'wcdm_enabled', 'no' ) );
		if ( 'yes' !== $activated ) {
			return;
		}

		// Assets.
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
		add_action( 'wp_head',            array( $this, 'output_custom_styles' ) );

		// Remove native coupon form from its default position.
		remove_action( 'woocommerce_before_checkout_form', 'woocommerce_checkout_coupon_form', 10 );

		// Render repositioned coupon field (fires in Classic + CartFlows + FunnelKit).
		$layout_position = get_option( 'wcdm_layout_position', 'above_payment' );

		if ( 'sidebar' === $layout_position ) {
			add_action( 'woocommerce_checkout_before_order_review', array( $this, 'render_repositioned_coupon_field' ) );
		} else {
			add_action( 'woocommerce_review_order_before_submit', array( $this, 'render_repositioned_coupon_field' ) );

			// CartFlows-specific hook (before CF submit button).
			if ( class_exists( 'Cartflows_Loader' ) || defined( 'CARTFLOWS_VER' ) ) {
				add_action( 'wcf_before_checkout_btn', array( $this, 'render_repositioned_coupon_field' ) );
			}

			// FunnelKit-specific hook.
			if ( class_exists( 'WFFN_Core' ) || function_exists( 'wffn_is_checkout' ) ) {
				add_action( 'wffn_checkout_before_place_order', array( $this, 'render_repositioned_coupon_field' ) );
			}
		}

		// Hidden native form in footer — JS uses this to proxy coupon submission.
		add_action( 'wp_footer', array( $this, 'render_hidden_native_coupon_form' ) );

		// Hidden template copy — JS clones this for Block / CartFlows / FunnelKit.
		add_action( 'wp_footer', array( $this, 'render_hidden_repositioned_coupon_template' ) );

		// Hidden coupons list for JS injection.
		add_action( 'wp_footer', array( $this, 'render_hidden_available_coupons' ) );
	}

	// =========================================================================
	// Reposition rendering
	// =========================================================================

	/**
	 * Render the coupon widget at its new position.
	 * Shows a success pill when a coupon is already applied; otherwise the input.
	 */
	public function render_repositioned_coupon_field() {
		// Render only once per page, even if multiple hooks fire.
		static $rendered = false;
		if ( $rendered ) {
			return;
		}
		$rendered = true;

		if ( ! WC()->cart ) {
			return;
		}

		$applied = WC()->cart->get_applied_coupons();
		$has_coupon = ! empty( $applied );
		$applied_code = $has_coupon ? strtoupper( $applied[0] ) : '';

		$button_text = get_option( 'wcdm_button_text', __( 'Apply Coupon', 'woo-coupon-display-manager' ) );
		$show_hint   = 'yes' === get_option( 'wcdm_show_hint', 'yes' );
		
		$display_mode = get_option( 'wcdm_coupon_display_mode', 'input' );
		$show_input   = ( 'input' === $display_mode || 'both' === $display_mode );
		$enable_list  = ( 'list' === $display_mode || 'both' === $display_mode );

		$single_coupon = 'yes' === get_option( 'wcdm_single_coupon', 'yes' );
		$button_style = get_option( 'wcdm_button_style', 'secondary' );
		$btn_class = 'button wcdm-btn-' . esc_attr( $button_style );

		$wrapper_class = 'wcdm-checkout-coupon-repositioned';
		if ( $has_coupon ) {
			$wrapper_class .= ' wcdm-coupon-applied-state wcdm-has-coupon';
		}
		?>
		<div class="<?php echo esc_attr( $wrapper_class ); ?>">
			<?php
			if ( $enable_list ) {
				echo $this->render_available_coupons_markup(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}
			?>
			<div class="wcdm-coupon-interactive-area">
				<?php if ( $has_coupon ) : ?>
					<div class="wcdm-coupon-applied-pills-container">
						<?php foreach ( $applied as $coupon_code ) : ?>
							<div class="wcdm-coupon-applied-pill" style="margin-bottom: 6px;">
								<span class="wcdm-pill-check">&#10003;</span>
								<span class="wcdm-pill-label">
									<?php esc_html_e( 'Coupon', 'woo-coupon-display-manager' ); ?>
									<strong><?php echo esc_html( strtoupper( $coupon_code ) ); ?></strong>
									<?php esc_html_e( 'Applied!', 'woo-coupon-display-manager' ); ?>
								</span>
								<a href="#" class="wcdm-remove-coupon" data-coupon="<?php echo esc_attr( strtolower( $coupon_code ) ); ?>">
									<?php esc_html_e( '(Remove)', 'woo-coupon-display-manager' ); ?>
								</a>
							</div>
						<?php endforeach; ?>
					</div>
					<?php if ( $show_input && ! $single_coupon ) : ?>
						<div class="wcdm-coupon-input-wrapper-spacer" style="margin-top: 12px;"></div>
						<div class="wcdm-coupon-input-wrapper">
							<input
								type="text"
								id="wcdm_coupon_code_mock"
								class="input-text"
								placeholder="<?php esc_attr_e( 'Coupon Code', 'woo-coupon-display-manager' ); ?>"
								value=""
								autocomplete="off"
							/>
							<button type="button" id="wcdm_apply_coupon_mock" class="<?php echo esc_attr( $btn_class ); ?>">
								<?php echo esc_html( $button_text ); ?>
							</button>
						</div>
					<?php endif; ?>
				<?php else : ?>
					<?php if ( $show_input ) : ?>
					<div class="wcdm-coupon-input-wrapper">
						<input
							type="text"
							id="wcdm_coupon_code_mock"
							class="input-text"
							placeholder="<?php esc_attr_e( 'Coupon Code', 'woo-coupon-display-manager' ); ?>"
							value=""
							autocomplete="off"
						/>
						<button type="button" id="wcdm_apply_coupon_mock" class="<?php echo esc_attr( $btn_class ); ?>">
							<?php echo esc_html( $button_text ); ?>
						</button>
					</div>
					<?php endif; ?>
					<?php if ( $show_hint ) : ?>
					<span class="wcdm-coupon-hint">
						<?php esc_html_e( 'Optional — only if you have a coupon', 'woo-coupon-display-manager' ); ?>
					</span>
					<?php endif; ?>
				<?php endif; ?>
			</div>
		</div>
		<?php
	}

	/**
	 * The input + Apply button markup (used directly and as a hidden template clone).
	 */
	public function render_repositioned_coupon_html() {
		$button_text = get_option( 'wcdm_button_text', __( 'Apply Coupon', 'woo-coupon-display-manager' ) );
		$show_hint   = 'yes' === get_option( 'wcdm_show_hint', 'yes' );
		
		$display_mode = get_option( 'wcdm_coupon_display_mode', 'input' );
		$show_input   = ( 'input' === $display_mode || 'both' === $display_mode );
		$enable_list  = ( 'list' === $display_mode || 'both' === $display_mode );

		$button_style = get_option( 'wcdm_button_style', 'secondary' );

		$btn_class = 'button wcdm-btn-' . esc_attr( $button_style );
		?>
		<div class="wcdm-checkout-coupon-repositioned">
			<?php
			if ( $enable_list ) {
				echo $this->render_available_coupons_markup(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}
			?>
			<div class="wcdm-coupon-interactive-area">
				<?php if ( $show_input ) : ?>
				<div class="wcdm-coupon-input-wrapper">
					<input
						type="text"
						id="wcdm_coupon_code_mock"
						class="input-text"
						placeholder="<?php esc_attr_e( 'Coupon Code', 'woo-coupon-display-manager' ); ?>"
						value=""
						autocomplete="off"
					/>
					<button type="button" id="wcdm_apply_coupon_mock" class="<?php echo esc_attr( $btn_class ); ?>">
						<?php echo esc_html( $button_text ); ?>
					</button>
				</div>
				<?php endif; ?>
				<?php if ( $show_hint ) : ?>
				<span class="wcdm-coupon-hint">
					<?php esc_html_e( 'Optional — only if you have a coupon', 'woo-coupon-display-manager' ); ?>
				</span>
				<?php endif; ?>
			</div>
		</div>
		<?php
	}

	/**
	 * Hidden clone in footer — JS reads this to inject into Block / CartFlows pages.
	 */
	public function render_hidden_repositioned_coupon_template() {
		echo '<div id="wcdm-repositioned-coupon-html-hidden" style="display:none !important;">';
		$this->render_repositioned_coupon_html();
		echo '</div>';
	}

	/**
	 * Hidden native WooCommerce coupon form in footer — JS proxies submission through it.
	 */
	public function render_hidden_native_coupon_form() {
		echo '<div id="wcdm-hidden-coupon-form-wrapper" style="display:none !important;">';
		if ( function_exists( 'woocommerce_checkout_coupon_form' ) ) {
			woocommerce_checkout_coupon_form();
		}
		echo '</div>';
	}

	// =========================================================================
	// Available coupons list
	// =========================================================================

	public function render_hidden_available_coupons() {
		$display_mode = get_option( 'wcdm_coupon_display_mode', 'input' );
		$enable_list  = ( 'list' === $display_mode || 'both' === $display_mode );
		if ( ! $enable_list ) {
			return;
		}
		if ( ! WCDM_Compat::is_checkout_page() ) {
			return;
		}
		echo '<div id="wcdm-available-coupons-hidden" style="display:none !important;">';
		echo $this->render_available_coupons_markup(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo '</div>';
	}

	public function get_available_coupons() {
		if ( ! class_exists( 'WC_Coupon' ) || ! WC()->cart ) {
			return array();
		}

		$hide_private = 'yes' === get_option( 'wcdm_list_hide_private', 'no' );
		$selected     = get_option( 'wcdm_list_selected_coupons', array() );
		if ( ! is_array( $selected ) ) {
			$selected = array();
		}
		$applied      = WC()->cart->get_applied_coupons();

		// Prevent single coupon enforcement from modifying cart during this lookup.
		remove_filter( 'woocommerce_coupon_is_valid', array( 'WCDM_Compat', 'remove_existing_before_apply' ), 10 );

		$posts = get_posts( array(
			'post_type'      => 'shop_coupon',
			'post_status'    => 'publish',
			'posts_per_page' => 50,
			'orderby'        => 'date',
			'order'          => 'DESC',
		) );

		$applicable = array();

		foreach ( $posts as $post ) {
			$code   = $post->post_title;

			if ( ! empty( $selected ) && ! in_array( $code, $selected, true ) ) {
				continue;
			}

			$coupon = new WC_Coupon( $code );

			if ( ! $coupon->is_valid() ) {
				continue;
			}

			$type   = $coupon->get_discount_type();
			$amount = $coupon->get_amount();
			$desc   = $coupon->get_description();

			if ( $hide_private && 0 === strpos( trim( strtolower( $desc ) ), '[private]' ) ) {
				continue;
			}

			if ( empty( $desc ) ) {
				switch ( $type ) {
					case 'percent':
						$desc = sprintf( __( 'Discount %s%%', 'woo-coupon-display-manager' ), $amount );
						break;
					case 'fixed_cart':
						$desc = sprintf( __( '%s Off Total', 'woo-coupon-display-manager' ), wc_price( $amount ) );
						break;
					case 'fixed_product':
						$desc = sprintf( __( '%s Off per Product', 'woo-coupon-display-manager' ), wc_price( $amount ) );
						break;
					default:
						$desc = sprintf( __( '%s Off', 'woo-coupon-display-manager' ), $amount );
						break;
				}
			}

			$applicable[] = array(
				'code'        => $code,
				'description' => $desc,
				'amount'      => $amount,
				'type'        => $type,
				'applied'     => in_array( strtolower( $code ), array_map( 'strtolower', $applied ), true ),
			);
		}

		// Restore single coupon enforcement filter if active.
		if ( 'yes' === get_option( 'wcdm_single_coupon', 'yes' ) ) {
			add_filter( 'woocommerce_coupon_is_valid', array( 'WCDM_Compat', 'remove_existing_before_apply' ), 10, 2 );
		}

		return $applicable;
	}

	public function render_available_coupons_markup() {
		$coupons = $this->get_available_coupons();
		if ( empty( $coupons ) ) {
			return '';
		}

		$title     = get_option( 'wcdm_list_title', __( 'Available Coupons', 'woo-coupon-display-manager' ) );
		$show_desc = get_option( 'wcdm_list_show_desc', 'yes' );

		ob_start();
		?>
		<div class="wcdm-available-coupons-container">
			<h4 class="wcdm-coupon-list-title"><?php echo esc_html( $title ); ?></h4>
			<div class="wcdm-coupon-list-badges">
				<?php foreach ( $coupons as $coupon ) : 
					$applied_class = $coupon['applied'] ? ' wcdm-badge-applied' : '';
					?>
					<div class="wcdm-coupon-badge-item<?php echo esc_attr( $applied_class ); ?>"
						 data-code="<?php echo esc_attr( $coupon['code'] ); ?>"
						 role="button"
						 tabindex="0"
						 aria-label="<?php echo esc_attr( $coupon['code'] ); ?>">
						<span class="wcdm-badge-code">
							<?php echo esc_html( $coupon['code'] ); ?>
							<?php if ( $coupon['applied'] ) : ?>
								<span class="wcdm-badge-check">&#10003;</span>
							<?php endif; ?>
						</span>
						<?php if ( 'yes' === $show_desc && ! empty( $coupon['description'] ) ) : ?>
							<span class="wcdm-badge-desc"><?php echo esc_html( $coupon['description'] ); ?></span>
						<?php endif; ?>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}

	// =========================================================================
	// Assets
	// =========================================================================

	public function enqueue_assets() {
		if ( ! WCDM_Compat::is_checkout_page() ) {
			return;
		}

		wp_enqueue_style(
			'wcdm-frontend-css',
			WCDM_PLUGIN_URL . 'assets/css/frontend.css',
			array(),
			WCDM_VERSION . '.' . filemtime( WCDM_PLUGIN_DIR . 'assets/css/frontend.css' )
		);

		wp_enqueue_script(
			'wcdm-frontend-js',
			WCDM_PLUGIN_URL . 'assets/js/frontend.js',
			array( 'jquery' ),
			WCDM_VERSION . '.' . filemtime( WCDM_PLUGIN_DIR . 'assets/js/frontend.js' ),
			true
		);

		$display_mode = get_option( 'wcdm_coupon_display_mode', 'input' );
		$show_input   = ( 'input' === $display_mode || 'both' === $display_mode ) ? 'yes' : 'no';
		$enable_list  = ( 'list' === $display_mode || 'both' === $display_mode ) ? 'yes' : 'no';

		wp_localize_script(
			'wcdm-frontend-js',
			'wcdm_params',
			array(
				'button_text'   => get_option( 'wcdm_button_text', __( 'Apply Coupon', 'woo-coupon-display-manager' ) ),
				'button_style'  => get_option( 'wcdm_button_style', 'secondary' ),
				'show_hint'     => get_option( 'wcdm_show_hint', 'yes' ),
				'hint_text'     => __( 'Optional — only if you have a coupon', 'woo-coupon-display-manager' ),
				'show_input'    => $show_input,
				'enable_list'   => $enable_list,
				'checkout_type' => WCDM_Compat::get_checkout_type(),
				'remove_text'   => __( '(Remove)', 'woo-coupon-display-manager' ),
				'applied_text'  => __( 'Applied!', 'woo-coupon-display-manager' ),
				'removing_text' => __( 'Removing...', 'woo-coupon-display-manager' ),
				'single_coupon' => get_option( 'wcdm_single_coupon', 'yes' ),
				'layout_position' => get_option( 'wcdm_layout_position', 'above_payment' ),
			)
		);
	}

	/**
	 * Inline <style> — custom button colors + hide all native coupon elements.
	 */
	public function output_custom_styles() {
		if ( ! WCDM_Compat::is_checkout_page() ) {
			return;
		}

		echo '<style id="wcdm-custom-styles">';

		// Custom color CSS variables.
		if ( 'custom' === get_option( 'wcdm_button_style', 'secondary' ) ) {
			printf(
				':root{--wcdm-btn-bg:%s;--wcdm-btn-color:%s;--wcdm-btn-hover-bg:%s;--wcdm-btn-hover-color:%s;}',
				esc_attr( get_option( 'wcdm_custom_bg_color',         '#6c757d' ) ),
				esc_attr( get_option( 'wcdm_custom_text_color',       '#ffffff' ) ),
				esc_attr( get_option( 'wcdm_custom_hover_bg_color',   '#5a6268' ) ),
				esc_attr( get_option( 'wcdm_custom_hover_text_color', '#ffffff' ) )
			);
		}

		// Hide native / third-party coupon fields — we render our own.
		echo '
		/* WCDM: hide original coupon elements */
		.checkout_coupon.woocommerce-form-coupon,
		.woocommerce-info.woocommerce-coupon-form-notice,
		.wc-block-components-totals-coupon,
		.wc-block-checkout__coupon-code,
		.wp-block-woocommerce-checkout-order-summary-coupon-block,
		.wc-block-components-totals-coupon-link,
		.wcf-custom-coupon-field,
		#wcf_custom_coupon_field,
		#wcf_custom_coupon_field_order_review,
		.wcf_custom_coupon_field_wrap,
		.wffn-checkout .wffn-coupon-section,
		.fkwcs-coupon-section,
		.fkwcs-coupon-wrap,
		.wfacp-coupon-section,
		.wfacp_coupon_field_wrap {
			display: none !important;
		}';

		echo '</style>';
	}
}
