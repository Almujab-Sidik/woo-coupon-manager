<?php
/**
 * Uninstall — remove all plugin options from the database.
 *
 * Runs only when the user clicks "Delete" from the WordPress Plugins screen.
 *
 * @package WooCouponDisplayManager
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

$options = array(
	'wcdm_enabled',
	'wcdm_activated',
	'wcdm_mode',
	'wcdm_toggle_text',
	'wcdm_button_text',
	'wcdm_button_style',
	'wcdm_custom_bg_color',
	'wcdm_custom_text_color',
	'wcdm_custom_hover_bg_color',
	'wcdm_custom_hover_text_color',
	'wcdm_show_hint',
	'wcdm_show_input',
	'wcdm_enable_list',
	'wcdm_coupon_display_mode',
	'wcdm_layout_position',
	'wcdm_single_coupon',
	'wcdm_list_selected_coupons',
	'wcdm_list_title',
	'wcdm_list_show_desc',
	'wcdm_list_hide_private',
);

foreach ( $options as $option ) {
	delete_option( $option );
}

// Multisite cleanup.
if ( is_multisite() ) {
	$sites = get_sites( array( 'fields' => 'ids' ) );
	foreach ( $sites as $site_id ) {
		switch_to_blog( $site_id );
		foreach ( $options as $option ) {
			delete_option( $option );
		}
		restore_current_blog();
	}
}
