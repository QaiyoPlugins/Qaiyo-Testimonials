<?php
/**
 * Layout catalog — the single source of truth for all 12 layout versions.
 *
 * The free plugin renders V1–V6 and ships the wireframes + labels for ALL
 * twelve. V7–V12 are shown in the admin as locked "Pro" teasers (lock badge +
 * upgrade link) until the Qaiyo Testimonials Pro add-on unlocks them via the
 * `qt_unlocked_versions` filter.
 *
 * @package qaiyo-testimonials
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Qt_Catalog {

	/** Versions the free plugin renders out of the box. */
	const FREE_VERSIONS = array( 'v1', 'v2', 'v3', 'v4', 'v5', 'v6' );

	/** Default upgrade landing page. */
	const UPGRADE_URL = 'https://qaiyo-plugins.com/qaiyo-testimonials-pro/';

	/**
	 * Full catalog: version => [ label, short description ].
	 *
	 * @return array<string, array{0:string,1:string}>
	 */
	public static function all() {
		$catalog = array(
			'v1'  => array( __( 'V1 — Horizontal rows', 'qaiyo-testimonials' ),   __( '1 or 2 rows scrolling left/right with fading edges.', 'qaiyo-testimonials' ) ),
			'v2'  => array( __( 'V2 — Vertical columns', 'qaiyo-testimonials' ),   __( '2 or 3 columns scrolling up/down with fading edges.', 'qaiyo-testimonials' ) ),
			'v3'  => array( __( 'V3 — Photo carousel', 'qaiyo-testimonials' ),     __( 'Single row, circular avatar on top of each card.', 'qaiyo-testimonials' ) ),
			'v4'  => array( __( 'V4 — Bento grid', 'qaiyo-testimonials' ),         __( 'Asymmetric grid with one highlighted box.', 'qaiyo-testimonials' ) ),
			'v5'  => array( __( 'V5 — Avatar switcher', 'qaiyo-testimonials' ),    __( 'Click an avatar to reveal the matching quote (max 29).', 'qaiyo-testimonials' ) ),
			'v6'  => array( __( 'V6 — Photo hero card', 'qaiyo-testimonials' ),    __( 'Large photo on top, dark content section below.', 'qaiyo-testimonials' ) ),
			'v7'  => array( __( 'V7 — Pastel tilted cards', 'qaiyo-testimonials' ), __( 'Slightly rotated cards in pastel colors (set per testimonial).', 'qaiyo-testimonials' ) ),
			'v8'  => array( __( 'V8 — Split panel hero', 'qaiyo-testimonials' ),   __( 'Wide hero card: quote on the left, photo + name on the right.', 'qaiyo-testimonials' ) ),
			'v9'  => array( __( 'V9 — Masonry wall', 'qaiyo-testimonials' ),       __( 'Pinterest-style wall of cards with varying heights.', 'qaiyo-testimonials' ) ),
			'v10' => array( __( 'V10 — Featured + grid', 'qaiyo-testimonials' ),   __( 'One large featured testimonial beside a grid of smaller ones.', 'qaiyo-testimonials' ) ),
			'v11' => array( __( 'V11 — Spotlight + list', 'qaiyo-testimonials' ),  __( 'Large spotlight card with a clickable side list.', 'qaiyo-testimonials' ) ),
			'v12' => array( __( 'V12 — Editorial big quote', 'qaiyo-testimonials' ), __( 'Centered large quote with an avatar strip; auto-rotates.', 'qaiyo-testimonials' ) ),
		);
		/**
		 * Filter the full layout catalog (labels + descriptions for all versions).
		 *
		 * @param array $catalog
		 */
		return apply_filters( 'qt_layouts_catalog', $catalog );
	}

	/**
	 * Versions that are currently unlocked (selectable / renderable).
	 * Pro adds v7–v12 here when its license is active.
	 *
	 * @return string[]
	 */
	public static function unlocked() {
		/**
		 * Filter the unlocked (non-Pro-gated) versions.
		 *
		 * @param string[] $versions
		 */
		$versions = apply_filters( 'qt_unlocked_versions', self::FREE_VERSIONS );
		return is_array( $versions ) ? $versions : self::FREE_VERSIONS;
	}

	public static function is_unlocked( $version ) {
		return in_array( $version, self::unlocked(), true );
	}

	/** Whether any locked (Pro) version exists — i.e. should we show teasers? */
	public static function has_locked() {
		return (bool) array_diff( array_keys( self::all() ), self::unlocked() );
	}

	public static function upgrade_url() {
		/**
		 * Filter the "upgrade to Pro" URL used by the locked teasers.
		 *
		 * @param string $url
		 */
		return apply_filters( 'qt_upgrade_url', self::UPGRADE_URL );
	}
}
