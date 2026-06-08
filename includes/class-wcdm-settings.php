<?php

/**
 * Admin Settings Class.
 *
 * Registers WooCommerce Coupon Display settings tab and submenu page.
 *
 * @package WooCouponDisplayManager
 */
if (!defined('ABSPATH')) {
	exit;
}

class WCDM_Settings
{
	/** @var WCDM_Settings */
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
		add_filter('woocommerce_settings_tabs_array', array($this, 'add_settings_tab'), 50);
		add_action('woocommerce_settings_tabs_wcdm_settings', array($this, 'settings_tab_content'));
		add_action('woocommerce_update_options_wcdm_settings', array($this, 'update_settings'));
		add_action('admin_menu', array($this, 'add_admin_menu'));
		add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));

		// Custom coupon selection field hooks
		add_action('woocommerce_admin_field_wcdm_coupon_select_grid', array($this, 'render_coupon_select_grid'));
		add_action('woocommerce_update_option_wcdm_coupon_select_grid', array($this, 'save_coupon_select_grid'));
	}

	// =========================================================================
	// Menu
	// =========================================================================

	public function add_admin_menu()
	{
		add_submenu_page(
			'woocommerce',
			__('Coupon Display Settings', 'woo-coupon-display-manager'),
			__('Coupon Display', 'woo-coupon-display-manager'),
			'manage_woocommerce',
			'wcdm-settings',
			array($this, 'render_submenu_page')
		);
	}

	public function render_submenu_page()
	{
		if (isset($_POST['wcdm_save_settings']) && check_admin_referer('wcdm_save_settings_action', 'wcdm_nonce')) {
			woocommerce_update_options($this->get_settings());
			echo '<div class="updated"><p>' . esc_html__('Settings saved.', 'woo-coupon-display-manager') . '</p></div>';
		}
		?>
		<div class="wrap woocommerce wcdm-settings-wrap">
			<h2 class="screen-reader-text"><?php esc_html_e('Coupon Display Settings', 'woo-coupon-display-manager'); ?></h2>
			<div class="wcdm-settings-header">
				<div class="wcdm-settings-title-section">
					<span class="wcdm-logo-icon">
						<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><line x1="9" y1="9" x2="15" y2="15"></line><line x1="15" y1="9" x2="9" y2="15"></line></svg>
					</span>
					<span class="wcdm-settings-title-text"><?php esc_html_e('Coupon Display Settings', 'woo-coupon-display-manager'); ?></span>
				</div>
				<div class="wcdm-settings-header-actions">
					<a href="https://docs.eraai.id" target="_blank" class="button wcdm-doc-button"><?php esc_html_e('Documentation', 'woo-coupon-display-manager'); ?></a>
				</div>
			</div>
			
			<div class="wcdm-settings-card">
				<form method="post" action="">
					<?php wp_nonce_field('wcdm_save_settings_action', 'wcdm_nonce'); ?>
					<?php woocommerce_admin_fields($this->get_settings()); ?>
					<p class="submit wcdm-settings-submit-row">
						<button name="wcdm_save_settings" class="button-primary wcdm-save-btn" type="submit">
							<?php esc_html_e('Save changes', 'woo-coupon-display-manager'); ?>
						</button>
					</p>
				</form>
			</div>
		</div>
		<?php
	}

	// =========================================================================
	// WC Settings tab
	// =========================================================================

	public function add_settings_tab($tabs)
	{
		$tabs['wcdm_settings'] = __('Coupon Display', 'woo-coupon-display-manager');
		return $tabs;
	}

	public function settings_tab_content()
	{
		woocommerce_admin_fields($this->get_settings());
	}

	public function update_settings()
	{
		woocommerce_update_options($this->get_settings());
	}

	// =========================================================================
	// Admin assets (color picker)
	// =========================================================================

	public function enqueue_admin_assets()
	{
		$on_page = (
			(isset($_GET['page']) && 'wc-settings' === $_GET['page'] && isset($_GET['tab']) && 'wcdm_settings' === $_GET['tab']) ||
			(isset($_GET['page']) && 'wcdm-settings' === $_GET['page'])
		);

		if (!$on_page) {
			return;
		}

		wp_enqueue_style('wp-color-picker');
		wp_enqueue_script('wp-color-picker');

		wp_enqueue_style('wcdm-admin-css', WCDM_PLUGIN_URL . 'assets/css/admin.css', array('wp-color-picker'), WCDM_VERSION);
		wp_enqueue_script('wcdm-admin-js', WCDM_PLUGIN_URL . 'assets/js/admin.js', array('jquery', 'wp-color-picker'), WCDM_VERSION, true);
	}

	private function get_all_coupons()
	{
		if (!class_exists('WooCommerce')) {
			return array();
		}
		$posts = get_posts(array(
			'post_type' => 'shop_coupon',
			'post_status' => 'publish',
			'posts_per_page' => -1,
			'orderby' => 'title',
			'order' => 'ASC',
		));

		$options = array();
		foreach ($posts as $post) {
			$options[$post->post_title] = $post->post_title;
		}
		return $options;
	}

	public function get_settings()
	{
		$settings = array(
			// ---- General ----
			array(
				'title' => __('WooCommerce Coupon Display Manager', 'woo-coupon-display-manager'),
				'type' => 'title',
				'desc' => __('Moves the coupon field to above the Place Order button so customers never confuse it with the payment button. Works with CartFlows, FunnelKit, Block Checkout, and the WooCommerce default.', 'woo-coupon-display-manager'),
				'id' => 'wcdm_settings_title',
			),
			array(
				'title' => __('Activated', 'woo-coupon-display-manager'),
				'desc' => __('Enable customized coupon display on checkout.', 'woo-coupon-display-manager'),
				'id' => 'wcdm_activated',
				'default' => 'no',
				'type' => 'checkbox',
			),
			array(
				'title' => __('Layout Position', 'woo-coupon-display-manager'),
				'desc' => __('Choose where to display the coupon field on checkout. "Above Payment Button" places it right above the Place Order button. "WooCommerce Default / Sidebar" places it in the Order Summary sidebar (for block checkout) or above the Order Review sidebar (for classic checkout).', 'woo-coupon-display-manager'),
				'id' => 'wcdm_layout_position',
				'default' => 'above_payment',
				'type' => 'select',
				'class' => 'wc-enhanced-select',
				'options' => array(
					'above_payment' => __('Above Payment Button', 'woo-coupon-display-manager'),
					'sidebar' => __('WooCommerce Default / Sidebar', 'woo-coupon-display-manager'),
				),
				'desc_tip' => true,
			),
			// ---- Apply button ----
			array(
				'title' => __('Apply Button Text', 'woo-coupon-display-manager'),
				'desc' => __('Label shown on the Apply button.', 'woo-coupon-display-manager'),
				'id' => 'wcdm_button_text',
				'default' => __('Apply Coupon', 'woo-coupon-display-manager'),
				'type' => 'text',
				'desc_tip' => true,
			),
			array(
				'title' => __('Button Style', 'woo-coupon-display-manager'),
				'desc' => __('Visual style for the Apply button. "Secondary" uses a neutral outline; "Link" renders a text-only link; "Custom" lets you set exact colors.', 'woo-coupon-display-manager'),
				'id' => 'wcdm_button_style',
				'default' => 'secondary',
				'type' => 'select',
				'class' => 'wc-enhanced-select',
				'options' => array(
					'secondary' => __('Secondary Outline (Gray / Neutral)', 'woo-coupon-display-manager'),
					'link' => __('Minimal Text Link', 'woo-coupon-display-manager'),
					'custom' => __('Custom Color Scheme', 'woo-coupon-display-manager'),
				),
				'desc_tip' => true,
			),
			array(
				'title' => __('Button Background Color', 'woo-coupon-display-manager'),
				'id' => 'wcdm_custom_bg_color',
				'default' => '#6c757d',
				'type' => 'text',
				'class' => 'wcdm-color-field wcdm-color-row',
			),
			array(
				'title' => __('Button Text Color', 'woo-coupon-display-manager'),
				'id' => 'wcdm_custom_text_color',
				'default' => '#ffffff',
				'type' => 'text',
				'class' => 'wcdm-color-field wcdm-color-row',
			),
			array(
				'title' => __('Button Hover Background', 'woo-coupon-display-manager'),
				'id' => 'wcdm_custom_hover_bg_color',
				'default' => '#5a6268',
				'type' => 'text',
				'class' => 'wcdm-color-field wcdm-color-row',
			),
			array(
				'title' => __('Button Hover Text Color', 'woo-coupon-display-manager'),
				'id' => 'wcdm_custom_hover_text_color',
				'default' => '#ffffff',
				'type' => 'text',
				'class' => 'wcdm-color-field wcdm-color-row',
			),
			// ---- UX extras ----
			array(
				'title' => __('Show Optional Hint', 'woo-coupon-display-manager'),
				'desc' => __('Show "Optional — only if you have a coupon" hint below the input.', 'woo-coupon-display-manager'),
				'id' => 'wcdm_show_hint',
				'default' => 'yes',
				'type' => 'checkbox',
			),
			array(
				'type' => 'sectionend',
				'id' => 'wcdm_general_sectionend',
			),
			// ---- Coupon restriction ----
			array(
				'title' => __('Coupon Restriction', 'woo-coupon-display-manager'),
				'type' => 'title',
				'desc' => __('Control how many coupons a customer can apply at once.', 'woo-coupon-display-manager'),
				'id' => 'wcdm_restriction_title',
			),
			array(
				'title' => __('Single Coupon Mode', 'woo-coupon-display-manager'),
				'desc' => __('Allow only one coupon per order. Applying a new code automatically replaces the existing one. <br/><strong style="color: #ea580c; display: block; margin-top: 5px;">WARNING: Do not uncheck this option unless you explicitly want to allow customers to combine multiple coupons/discounts on a single order. If unchecked, switching coupons on the checkout page may fail due to WooCommerce\'s "Individual use only" restrictions.</strong>', 'woo-coupon-display-manager'),
				'id' => 'wcdm_single_coupon',
				'default' => 'yes',
				'type' => 'checkbox',
			),
			array(
				'type' => 'sectionend',
				'id' => 'wcdm_restriction_sectionend',
			),
			// ---- Available coupons list ----
			array(
				'title' => __('Available Coupons List', 'woo-coupon-display-manager'),
				'type' => 'title',
				'desc' => __("Show a list of valid coupons so customers can apply one with a single click. Prefix a coupon's description with [private] to hide it from the list.", 'woo-coupon-display-manager'),
				'id' => 'wcdm_list_section_title',
			),
			array(
				'title' => __('Coupon Display Mode', 'woo-coupon-display-manager'),
				'desc' => __('Choose the type of display for the checkout coupon. "Manual Input Only" shows only the text box. "Clickable Coupon List Only" shows only the clickable badges. "Both" shows both badges and the manual text input field.', 'woo-coupon-display-manager'),
				'id' => 'wcdm_coupon_display_mode',
				'default' => 'input',
				'type' => 'select',
				'class' => 'wc-enhanced-select',
				'options' => array(
					'input' => __('Manual Input Only', 'woo-coupon-display-manager'),
					'list' => __('Clickable Coupon List Only', 'woo-coupon-display-manager'),
					'both' => __('Both (Manual Input & Clickable List)', 'woo-coupon-display-manager'),
				),
				'desc_tip' => true,
			),
			array(
				'title' => __('Select Coupons to Display', 'woo-coupon-display-manager'),
				'desc' => __('Choose which coupons to display in the list. If none are selected, all valid public coupons will be shown.', 'woo-coupon-display-manager'),
				'id' => 'wcdm_list_selected_coupons',
				'type' => 'wcdm_coupon_select_grid',
				'desc_tip' => true,
			),
			array(
				'title' => __('List Heading', 'woo-coupon-display-manager'),
				'desc' => __('Heading text shown above the coupon badges.', 'woo-coupon-display-manager'),
				'id' => 'wcdm_list_title',
				'default' => __('Available Coupons', 'woo-coupon-display-manager'),
				'type' => 'text',
				'desc_tip' => true,
			),
			array(
				'title' => __('Show Coupon Description', 'woo-coupon-display-manager'),
				'desc' => __('Show discount info below each badge (e.g. "Discount 10%").', 'woo-coupon-display-manager'),
				'id' => 'wcdm_list_show_desc',
				'default' => 'yes',
				'type' => 'checkbox',
			),
			array(
				'title' => __('Hide Private Coupons', 'woo-coupon-display-manager'),
				'desc' => __('Hide coupons whose description starts with [private].', 'woo-coupon-display-manager'),
				'id' => 'wcdm_list_hide_private',
				'default' => 'no',
				'type' => 'checkbox',
			),
			array(
				'type' => 'sectionend',
				'id' => 'wcdm_settings_sectionend',
			),
		);

		return apply_filters('woocommerce_coupon_display_manager_settings', $settings);
	}

	private function get_all_coupons_detailed()
	{
		if (!class_exists('WooCommerce')) {
			return array();
		}
		$posts = get_posts(array(
			'post_type' => 'shop_coupon',
			'post_status' => 'publish',
			'posts_per_page' => -1,
			'orderby' => 'title',
			'order' => 'ASC',
		));

		$coupons = array();
		foreach ($posts as $post) {
			$code = $post->post_title;
			$coupon = new WC_Coupon($code);
			$type = $coupon->get_discount_type();
			$amount = $coupon->get_amount();

			switch ($type) {
				case 'percent':
					$amount_label = $amount . '%';
					break;
				case 'fixed_cart':
					$amount_label = wc_price($amount);
					break;
				case 'fixed_product':
					$amount_label = wc_price($amount) . ' ' . __('per Product', 'woo-coupon-display-manager');
					break;
				default:
					$amount_label = $amount;
					break;
			}

			$expiry_date = $coupon->get_date_expires();
			$expiry_label = '';
			if ($expiry_date) {
				$expiry_label = $expiry_date->date_i18n(get_option('date_format'));
			}

			$usage_limit = $coupon->get_usage_limit();
			$usage_count = $coupon->get_usage_count();
			$limit_label = '';
			if ($usage_limit > 0) {
				$limit_label = sprintf(__('%d/%d Used', 'woo-coupon-display-manager'), $usage_count, $usage_limit);
			} else {
				$limit_label = sprintf(__('%d Used', 'woo-coupon-display-manager'), $usage_count);
			}

			$coupons[] = array(
				'code' => $code,
				'amount_label' => strip_tags($amount_label),
				'description' => $coupon->get_description(),
				'discount_type' => $type,
				'expiry_label' => $expiry_label,
				'limit_label' => $limit_label,
			);
		}
		return $coupons;
	}

	public function render_coupon_select_grid($value)
	{
		$option_value = get_option($value['id'], array());
		if (!is_array($option_value)) {
			$option_value = array();
		}

		$coupons = $this->get_all_coupons_detailed();
		?>
		<tr valign="top" id="wcdm_list_selected_coupons_row">
			<th scope="row" class="titledesc">
				<label><?php echo esc_html($value['title']); ?></label>
				<?php echo $value['desc_tip'] ? wc_help_tip($value['desc']) : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</th>
			<td class="forminp">
				<div class="wcdm-admin-coupon-grid-wrapper">
					<!-- Search and Actions Bar -->
					<div class="wcdm-admin-coupon-bar">
						<div class="wcdm-admin-coupon-search-wrapper">
							<svg class="wcdm-search-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
							<input type="text" id="wcdm-admin-coupon-search" placeholder="<?php esc_attr_e('Search coupons...', 'woo-coupon-display-manager'); ?>" />
						</div>
						
						<div class="wcdm-admin-coupon-actions">
							<span class="wcdm-admin-selected-counter">0 selected</span>
							<button type="button" class="button wcdm-admin-select-all"><?php esc_html_e('Select All', 'woo-coupon-display-manager'); ?></button>
							<button type="button" class="button wcdm-admin-deselect-all"><?php esc_html_e('Clear Selection', 'woo-coupon-display-manager'); ?></button>
						</div>
					</div>

					<!-- Coupon Grid -->
					<div class="wcdm-admin-coupon-grid">
						<?php if (empty($coupons)): ?>
							<div class="wcdm-admin-no-coupons-card">
								<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="wcdm-empty-icon"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><line x1="9" y1="9" x2="15" y2="15"></line><line x1="15" y1="9" x2="9" y2="15"></line></svg>
								<p class="wcdm-admin-no-coupons"><?php esc_html_e('No coupons found.', 'woo-coupon-display-manager'); ?></p>
								<a href="<?php echo esc_url(admin_url('post-new.php?post_type=shop_coupon')); ?>" class="button button-primary wcdm-create-coupon-btn"><?php esc_html_e('Create Coupon', 'woo-coupon-display-manager'); ?></a>
							</div>
						<?php else: ?>
							<?php
							foreach ($coupons as $coupon):
								$checked = in_array($coupon['code'], $option_value, true);
								$active_class = $checked ? ' wcdm-admin-ticket-active' : '';

								$type_label = '';
								$type_class = '';
								switch ($coupon['discount_type']) {
									case 'percent':
										$type_label = __('Percentage', 'woo-coupon-display-manager');
										$type_class = 'wcdm-type-percent';
										break;
									case 'fixed_cart':
										$type_label = __('Fixed Cart', 'woo-coupon-display-manager');
										$type_class = 'wcdm-type-fixed-cart';
										break;
									case 'fixed_product':
										$type_label = __('Fixed Product', 'woo-coupon-display-manager');
										$type_class = 'wcdm-type-fixed-product';
										break;
									default:
										$type_label = ucfirst(str_replace('_', ' ', $coupon['discount_type']));
										$type_class = 'wcdm-type-other';
										break;
								}
								?>
								<div class="wcdm-admin-ticket<?php echo esc_attr($active_class); ?>" data-code="<?php echo esc_attr($coupon['code']); ?>">
									<div class="wcdm-admin-ticket-header">
										<span class="wcdm-admin-ticket-code"><?php echo esc_html($coupon['code']); ?></span>
										<input type="checkbox" name="<?php echo esc_attr($value['id']); ?>[]" value="<?php echo esc_attr($coupon['code']); ?>" <?php checked($checked, true); ?> style="display:none;" />
										<span class="wcdm-admin-ticket-checkbox"></span>
									</div>
									<div class="wcdm-admin-ticket-body">
										<div class="wcdm-admin-ticket-amount-row">
											<span class="wcdm-admin-ticket-amount"><?php echo esc_html($coupon['amount_label']); ?></span>
											<span class="wcdm-admin-ticket-badge <?php echo esc_attr($type_class); ?>"><?php echo esc_html($type_label); ?></span>
										</div>
										<?php if (!empty($coupon['description'])): ?>
											<span class="wcdm-admin-ticket-desc"><?php echo esc_html($coupon['description']); ?></span>
										<?php endif; ?>
									</div>
									<?php if (!empty($coupon['expiry_label']) || !empty($coupon['limit_label'])): ?>
										<div class="wcdm-admin-ticket-footer">
											<?php if (!empty($coupon['expiry_label'])): ?>
												<span class="wcdm-admin-ticket-meta wcdm-expiry" title="<?php esc_attr_e('Expiry Date', 'woo-coupon-display-manager'); ?>">
													<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="wcdm-meta-icon"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
													<?php echo esc_html($coupon['expiry_label']); ?>
												</span>
											<?php endif; ?>
											<?php if (!empty($coupon['limit_label'])): ?>
												<span class="wcdm-admin-ticket-meta wcdm-usage" title="<?php esc_attr_e('Usage', 'woo-coupon-display-manager'); ?>">
													<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="wcdm-meta-icon"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
													<?php echo esc_html($coupon['limit_label']); ?>
												</span>
											<?php endif; ?>
										</div>
									<?php endif; ?>
								</div>
							<?php endforeach; ?>
						<?php endif; ?>
					</div>
					<p class="description"><?php echo esc_html($value['desc']); ?></p>
				</div>
			</td>
		</tr>
		<?php
	}

	public function save_coupon_select_grid($field)
	{
		$id = $field['id'];
		$value = isset($_POST[$id]) ? array_map('sanitize_text_field', $_POST[$id]) : array();
		update_option($id, $value);
	}
}
