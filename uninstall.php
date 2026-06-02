<?php
/**
 * Uninstall handler for Qaiyo Testimonials.
 *
 * WordPress runs this file when the user deletes the plugin from the Plugins
 * page. The plugin cannot show an interactive prompt at delete-time, so the
 * behaviour is controlled by the "Data & maintenance" setting:
 *
 *   - OFF (default): keep all data — reinstalling restores everything.
 *   - ON: permanently delete all testimonials, displays, categories, post
 *         meta and the settings option.
 *
 * @package qaiyo-testimonials
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

$qt_settings = get_option( 'qt_settings', array() );
$qt_remove   = is_array( $qt_settings ) && ! empty( $qt_settings['remove_data_on_uninstall'] );

if ( ! $qt_remove ) {
	// User chose to keep data — do nothing.
	return;
}

$qt_post_type = 'qaiyo_testimonial';
$qt_display   = 'qt_display';
$qt_taxonomy  = 'qt_category';

// Delete all testimonial + display posts (and their meta).
$qt_ids = get_posts(
	array(
		'post_type'      => array( $qt_post_type, $qt_display ),
		'post_status'    => 'any',
		'numberposts'    => -1,
		'fields'         => 'ids',
		'suppress_filters' => true,
	)
);
foreach ( $qt_ids as $qt_id ) {
	wp_delete_post( $qt_id, true );
}

// Delete all terms in the testimonial category taxonomy.
$qt_terms = get_terms(
	array(
		'taxonomy'   => $qt_taxonomy,
		'hide_empty' => false,
		'fields'     => 'ids',
	)
);
if ( ! is_wp_error( $qt_terms ) ) {
	foreach ( $qt_terms as $qt_term_id ) {
		wp_delete_term( $qt_term_id, $qt_taxonomy );
	}
}

// Delete the settings option.
delete_option( 'qt_settings' );

// Clear any leftover brand-menu transients (none persisted, but safe).
delete_option( 'qt_version' );

/**
 * Let the Pro add-on (if installed) clean up its own data in response.
 * The Pro uninstall.php also fires independently; this is a courtesy hook
 * for the case where Pro is still active when Free is removed.
 */
do_action( 'qt_uninstall_cleanup' );
