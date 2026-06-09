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

$wcdm_options = array(
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
	'wcdm_hint_text',
	'wcdm_show_input',
	'wcdm_enable_list',
	'wcdm_coupon_display_mode',
	'wcdm_layout_position',
	'wcdm_dropdown_hide',
	'wcdm_dropdown_text',
	'wcdm_single_coupon',
	'wcdm_list_selected_coupons',
	'wcdm_list_title',
	'wcdm_list_show_desc',
	'wcdm_list_hide_private',
);

foreach ( $wcdm_options as $wcdm_option ) {
	delete_option( $wcdm_option );
}

// Multisite cleanup.
if ( is_multisite() ) {
	$wcdm_sites = get_sites( array( 'fields' => 'ids' ) );
	foreach ( $wcdm_sites as $wcdm_site_id ) {
		switch_to_blog( $wcdm_site_id );
		foreach ( $wcdm_options as $wcdm_option ) {
			delete_option( $wcdm_option );
		}
		restore_current_blog();
	}
}
