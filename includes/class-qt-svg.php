<?php
/**
 * Inline SVG library for quote marks and layout wireframes.
 *
 * @package qaiyo-testimonials
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Qt_Svg {

	/**
	 * Quote mark SVG.
	 *
	 * @param int    $style 1 or 2.
	 * @param string $class CSS class for the wrapper.
	 */
	public static function quote_mark( $style = 1, $class = 'qt-quote-mark' ) {
		$style = (int) $style;
		/**
		 * Filter the list of valid quote-mark style numbers.
		 *
		 * Pro plugins can extend this list (5, 6, …) and provide the SVG
		 * via the `qt_quote_style_svg` filter below.
		 *
		 * @param int[] $styles
		 */
		$valid = apply_filters( 'qt_quote_styles', array( 1, 2, 3, 4 ) );
		if ( ! in_array( $style, $valid, true ) ) {
			$style = 1;
		}
		$method = 'quote_style_' . $style;
		$svg    = is_callable( array( __CLASS__, $method ) ) ? self::$method() : self::quote_style_1();
		/**
		 * Filter the inline SVG markup of a quote-mark style.
		 *
		 * Pro plugins can substitute the SVG (e.g. branded mark) or supply
		 * new styles (return SVG when $svg comes in empty for new style numbers).
		 *
		 * @param string $svg
		 * @param int    $style
		 */
		$svg = (string) apply_filters( 'qt_quote_style_svg', $svg, $style );
		return '<span class="' . esc_attr( $class ) . ' qt-quote-mark--style-' . $style . '" aria-hidden="true">' . $svg . '</span>';
	}

	/**
	 * Style 1 — teardrop "66" double quote (organic, flowing).
	 */
	public static function quote_style_1() {
		return '<svg viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg" focusable="false" role="presentation">'
			. '<path fill="currentColor" d="M119.472,66.59C53.489,66.59,0,120.094,0,186.1c0,65.983,53.489,119.487,119.472,119.487 c0,0-0.578,44.392-36.642,108.284c-4.006,12.802,3.135,26.435,15.945,30.418c9.089,2.859,18.653,0.08,24.829-6.389 c82.925-90.7,115.385-197.448,115.385-251.8C238.989,120.094,185.501,66.59,119.472,66.59z"/>'
			. '<path fill="currentColor" d="M392.482,66.59c-65.983,0-119.472,53.505-119.472,119.51c0,65.983,53.489,119.487,119.472,119.487 c0,0-0.578,44.392-36.642,108.284c-4.006,12.802,3.136,26.435,15.945,30.418c9.089,2.859,18.653,0.08,24.828-6.389 C479.539,347.2,512,240.452,512,186.1C512,120.094,458.511,66.59,392.482,66.59z"/>'
			. '</svg>';
	}

	/**
	 * Style 2 — bold filled block double quote (geometric, modern).
	 */
	public static function quote_style_2() {
		return '<svg viewBox="0 0 64 64" xmlns="http://www.w3.org/2000/svg" focusable="false" role="presentation">'
			. '<path fill="currentColor" fill-rule="evenodd" clip-rule="evenodd" d="M36.806,51.968l0,-8.026c2.537,-0.14 4.458,-0.604 5.761,-1.391c1.304,-0.786 2.22,-2.063 2.749,-3.829c0.528,-1.766 0.793,-4.293 0.793,-7.581l-9.303,0l0,-19.147l19.081,0l0,18.203c0,7.519 -1.612,13.028 -4.836,16.525c-3.225,3.497 -7.973,5.246 -14.245,5.246Zm-28.806,0l0,-8.026c2.537,-0.14 4.457,-0.586 5.761,-1.338c1.304,-0.752 2.247,-2.029 2.828,-3.83c0.581,-1.801 0.872,-4.345 0.872,-7.633l-9.461,0l0,-19.147l19.186,0l0,18.203c0,7.519 -1.603,13.028 -4.809,16.525c-3.207,3.497 -7.999,5.246 -14.377,5.246Z"/>'
			. '</svg>';
	}

	/**
	 * Style 3 — flowing comma "99" with closing tail (calligraphic).
	 */
	public static function quote_style_3() {
		return '<svg viewBox="0 0 350 217" xmlns="http://www.w3.org/2000/svg" focusable="false" role="presentation">'
			. '<path fill="currentColor" fill-rule="nonzero" d="M84.896,4.833c-43.637,0 -79.167,35.287 -79.167,79.167c0,32.894 20.703,62.713 51.529,74.171c2.832,1.042 4.443,4.232 3.092,6.934c-3.662,7.373 -9.245,14.567 -16.569,21.435c-5.078,4.752 -6.103,11.979 -2.572,17.969c3.581,6.071 10.514,8.773 17.301,6.559c45.963,-14.909 101.108,-55.142 105.275,-119.433c2.962,-45.654 -32.048,-86.8 -78.908,-86.8l0.018,-0.001Zm68.8,86.15c-3.841,59.229 -55.354,96.517 -98.292,110.45c-2.604,0.846 -4.525,-0.521 -5.436,-2.067c-0.423,-0.716 -1.595,-3.206 0.765,-5.42c8.22,-7.683 14.518,-15.869 18.718,-24.316c4.085,-8.203 -0.244,-17.838 -8.643,-20.963c-26.872,-9.993 -44.938,-35.986 -44.938,-64.667c0,-38.216 30.908,-69.025 69.025,-69.025c40.82,0 71.387,36.019 68.783,76.008l0.017,-0Zm111.508,-86.15c-43.638,0 -79.15,35.287 -79.15,79.167c0,32.894 20.703,62.713 51.529,74.171c2.848,1.058 4.427,4.248 3.092,6.934c-3.678,7.373 -9.245,14.583 -16.569,21.435c-5.078,4.752 -6.103,11.979 -2.572,17.969c3.564,6.038 10.465,8.773 17.301,6.559c45.963,-14.909 101.108,-55.142 105.275,-119.433c2.962,-45.542 -31.95,-86.8 -78.908,-86.8l0.001,-0.001Zm68.8,86.15c-3.841,59.229 -55.354,96.517 -98.292,110.45c-2.604,0.846 -4.525,-0.521 -5.436,-2.067c-0.423,-0.716 -1.595,-3.206 0.765,-5.42c8.203,-7.683 14.502,-15.869 18.718,-24.316c4.085,-8.203 -0.244,-17.838 -8.643,-20.963c-26.872,-9.993 -44.937,-35.986 -44.937,-64.667c0,-38.216 30.908,-69.025 69.025,-69.025c40.69,0 71.404,35.889 68.8,76.008l0,-0Z"/>'
			. '</svg>';
	}

	/**
	 * Style 4 — speech-bubble outlined double quote (modern, sharp).
	 */
	public static function quote_style_4() {
		return '<svg viewBox="0 0 2134 1175" xmlns="http://www.w3.org/2000/svg" focusable="false" role="presentation">'
			. '<g fill="currentColor" fill-rule="nonzero">'
			. '<path d="M1771.44,1062.064c-6.348,0 -11.656,-5.434 -11.656,-11.902c0,-1.219 0.242,-2.562 0.305,-2.867c7.996,-41.441 11.84,-82.031 11.84,-123.902c0,-39.184 -3.602,-78.734 -10.68,-117.676l-3.297,-18.312l-10.742,-58.41l-469.238,0c-12.328,0 -22.277,-10.07 -22.277,-22.398l-0.062,-581.238c0,-12.27 10.008,-22.34 22.219,-22.34l695.313,0c12.27,0 22.219,10.07 22.219,22.277l-0.121,480.348c-0.488,48.156 -18.25,136.109 -99.977,281.738c-45.41,80.934 -97.898,154.602 -109.742,166.32c-8.363,6.773 -11.109,8.055 -11.352,8.18c-0.004,-0 -0.734,0.184 -2.75,0.184Zm0,71.227c24.598,0 39.734,-8.547 59.145,-24.352c18.312,-14.891 78.98,-101.441 126.832,-186.645c71.531,-127.504 108.277,-233.766 109.07,-315.918l0.18,-481.02c0,-51.574 -41.93,-93.566 -93.508,-93.566l-695.312,0c-51.516,0 -93.508,41.992 -93.508,93.566l0.121,581.238c0,51.637 41.93,93.629 93.508,93.629l409.914,0l3.297,18.312c6.285,34.547 9.523,69.824 9.523,104.859c0,37.477 -3.539,73.609 -10.621,110.473c-1.219,7.629 -1.586,12.023 -1.586,16.359c0,45.832 37.23,83.063 82.945,83.063Z"/>'
			. '<path d="M653.702,1063.65c-6.348,0 -11.719,-5.434 -11.719,-11.719c0.121,-1.219 0.367,-2.688 0.367,-3.051c7.934,-41.258 11.84,-81.789 11.84,-123.902c0,-39.367 -3.539,-78.918 -10.621,-117.492l-3.297,-18.371l-10.621,-58.531l-469.418,0c-12.27,0 -22.277,-10.07 -22.277,-22.34l0,-581.363c0,-12.207 9.949,-22.219 22.219,-22.219l695.312,0c12.207,0 22.219,10.008 22.219,22.219l-0.242,480.469c-0.426,48.094 -18.129,135.984 -99.914,281.676c-45.41,80.871 -97.898,154.602 -109.68,166.32c-8.363,6.773 -11.047,7.996 -11.352,8.117c-0.008,0.004 -0.742,0.188 -2.816,0.188Zm0,71.227c24.656,0 39.797,-8.547 59.082,-24.293c18.312,-14.832 78.98,-101.379 126.891,-186.707c71.594,-127.562 108.277,-233.824 109.07,-315.855l0.242,-481.141c0,-51.516 -41.992,-93.445 -93.508,-93.445l-695.312,0c-51.57,0.004 -93.5,41.934 -93.5,93.445l0.062,581.359c0,51.516 41.93,93.566 93.508,93.566l409.914,0l3.355,18.371c6.227,34.484 9.398,69.762 9.398,104.797c0,37.535 -3.418,73.668 -10.5,110.473c-1.281,7.629 -1.648,12.086 -1.648,16.359c-0,45.777 37.23,83.07 82.945,83.07Z"/>'
			. '</g>'
			. '</svg>';
	}

	/**
	 * Version layout wireframes — used in Settings page + Display Builder picker.
	 *
	 * @param string $version v1..v5
	 * @param bool   $is_selected Whether to render with brand highlight.
	 */
	public static function wireframe( $version ) {
		$method = 'wireframe_' . $version;
		$svg    = '';
		if ( is_callable( array( __CLASS__, $method ) ) ) {
			$svg = self::$method();
		}
		/**
		 * Filter the wireframe SVG markup for a given layout version.
		 *
		 * Pro plugins can return their own SVG for premium versions (v9+)
		 * or override the Free wireframes for branding purposes.
		 *
		 * @param string $svg     Inline SVG markup, or empty string if no wireframe is registered.
		 * @param string $version Layout version slug.
		 */
		return (string) apply_filters( 'qt_wireframe_svg', $svg, $version );
	}

	/** V1 — 1-2 horizontal scrolling rows. */
	public static function wireframe_v1() {
		return '<svg class="qt-wf qt-wf-v1" viewBox="0 0 320 180" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="V1 horizontal rows layout">'
			. '<defs><linearGradient id="qtwf-fade-x" x1="0" x2="1" y1="0" y2="0">'
			. '<stop offset="0" stop-color="#fff"/><stop offset="0.1" stop-color="#fff" stop-opacity="0"/>'
			. '<stop offset="0.9" stop-color="#fff" stop-opacity="0"/><stop offset="1" stop-color="#fff"/>'
			. '</linearGradient></defs>'
			. '<rect width="320" height="180" fill="#f8f6ff"/>'
			// Row 1 - 3 cards
			. self::wf_card( 10, 20, 90, 70 ) . self::wf_card( 110, 20, 90, 70 ) . self::wf_card( 210, 20, 90, 70 )
			// Row 2 - 3 cards offset
			. self::wf_card( -10, 100, 90, 70 ) . self::wf_card( 90, 100, 90, 70 ) . self::wf_card( 190, 100, 90, 70 ) . self::wf_card( 290, 100, 90, 70 )
			// Direction arrows
			. '<path d="M15 55 l-6 -4 v8 z" fill="#6c5ce7"/>'
			. '<path d="M305 135 l6 -4 v8 z" fill="#6c5ce7"/>'
			// Fade overlay
			. '<rect width="320" height="180" fill="url(#qtwf-fade-x)"/>'
			. '</svg>';
	}

	/** V2 — 2-3 vertical scrolling columns. */
	public static function wireframe_v2() {
		return '<svg class="qt-wf qt-wf-v2" viewBox="0 0 320 180" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="V2 vertical columns layout">'
			. '<defs><linearGradient id="qtwf-fade-y" x1="0" x2="0" y1="0" y2="1">'
			. '<stop offset="0" stop-color="#fff"/><stop offset="0.15" stop-color="#fff" stop-opacity="0"/>'
			. '<stop offset="0.85" stop-color="#fff" stop-opacity="0"/><stop offset="1" stop-color="#fff"/>'
			. '</linearGradient></defs>'
			. '<rect width="320" height="180" fill="#f8f6ff"/>'
			// 3 columns of 2 cards
			. self::wf_card( 15, 10, 90, 55 ) . self::wf_card( 15, 75, 90, 55 ) . self::wf_card( 15, 140, 90, 55 )
			. self::wf_card( 115, -20, 90, 55 ) . self::wf_card( 115, 45, 90, 55 ) . self::wf_card( 115, 110, 90, 55 )
			. self::wf_card( 215, 25, 90, 55 ) . self::wf_card( 215, 90, 90, 55 ) . self::wf_card( 215, 155, 90, 55 )
			// Arrows
			. '<path d="M60 18 l-4 6 h8 z" fill="#6c5ce7"/>'
			. '<path d="M160 165 l-4 -6 h8 z" fill="#6c5ce7"/>'
			. '<path d="M260 18 l-4 6 h8 z" fill="#6c5ce7"/>'
			. '<rect width="320" height="180" fill="url(#qtwf-fade-y)"/>'
			. '</svg>';
	}

	/** V3 — single row, circular avatar top-left, left-aligned signature at bottom. */
	public static function wireframe_v3() {
		$card = function( $x ) {
			return '<rect x="' . $x . '" y="15" width="100" height="150" rx="8" fill="#fff" stroke="#c3c4c7"/>'
				// circular avatar top-left
				. '<circle cx="' . ( $x + 18 ) . '" cy="33" r="9" fill="#e2dcf7" stroke="#c3c4c7" stroke-width="0.5"/>'
				// body lines (left-aligned)
				. '<rect x="' . ( $x + 9 ) . '" y="54" width="84" height="3.5" rx="1.5" fill="#c3c4c7"/>'
				. '<rect x="' . ( $x + 9 ) . '" y="61" width="76" height="3.5" rx="1.5" fill="#c3c4c7"/>'
				. '<rect x="' . ( $x + 9 ) . '" y="68" width="82" height="3.5" rx="1.5" fill="#c3c4c7"/>'
				. '<rect x="' . ( $x + 9 ) . '" y="75" width="70" height="3.5" rx="1.5" fill="#c3c4c7"/>'
				. '<rect x="' . ( $x + 9 ) . '" y="82" width="80" height="3.5" rx="1.5" fill="#c3c4c7"/>'
				. '<rect x="' . ( $x + 9 ) . '" y="89" width="60" height="3.5" rx="1.5" fill="#c3c4c7"/>'
				// handwritten name + meta (left-aligned at bottom)
				. '<rect x="' . ( $x + 9 ) . '" y="138" width="46" height="6" rx="1.5" fill="#1d2327"/>'
				. '<rect x="' . ( $x + 9 ) . '" y="150" width="64" height="3" rx="1.5" fill="#787c82"/>';
		};
		return '<svg class="qt-wf qt-wf-v3" viewBox="0 0 320 180" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="V3 single row carousel layout">'
			. '<defs><linearGradient id="qtwf-fade-x3" x1="0" x2="1" y1="0" y2="0">'
			. '<stop offset="0" stop-color="#fff"/><stop offset="0.12" stop-color="#fff" stop-opacity="0"/>'
			. '<stop offset="0.88" stop-color="#fff" stop-opacity="0"/><stop offset="1" stop-color="#fff"/>'
			. '</linearGradient></defs>'
			. '<rect width="320" height="180" fill="#f8f6ff"/>'
			. $card( -10 ) . $card( 110 ) . $card( 230 ) . $card( 350 )
			. '<path d="M300 90 l6 -4 v8 z" fill="#6c5ce7"/>'
			. '<rect width="320" height="180" fill="url(#qtwf-fade-x3)"/>'
			. '</svg>';
	}

	/** V4 — bento grid. */
	public static function wireframe_v4() {
		return '<svg class="qt-wf qt-wf-v4" viewBox="0 0 320 180" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="V4 bento grid layout">'
			. '<rect width="320" height="180" fill="#f8f6ff"/>'
			// Big highlight box
			. '<rect x="10" y="10" width="155" height="160" rx="8" fill="#1a1a1a"/>'
			. '<rect x="22" y="22" width="14" height="10" rx="2" fill="#fff"/>'
			. '<rect x="22" y="42" width="120" height="6" rx="2" fill="#fff"/>'
			. '<rect x="22" y="56" width="130" height="4" rx="2" fill="#bbb"/>'
			. '<rect x="22" y="64" width="110" height="4" rx="2" fill="#bbb"/>'
			. '<rect x="22" y="72" width="125" height="4" rx="2" fill="#bbb"/>'
			. '<circle cx="32" cy="150" r="8" fill="#fff"/>'
			. '<rect x="48" y="146" width="50" height="4" rx="2" fill="#fff"/>'
			. '<rect x="48" y="154" width="80" height="3" rx="2" fill="#bbb"/>'
			// Top right
			. self::wf_card( 175, 10, 135, 75 )
			// Bottom right - 2 cards
			. self::wf_card( 175, 95, 65, 75 ) . self::wf_card( 245, 95, 65, 75 )
			. '</svg>';
	}

	/** V6 — photo hero on top, dark content section below. 3 cards in a row. */
	public static function wireframe_v6() {
		$card = function( $x ) {
			$y       = 14;
			$w       = 95;
			$h       = 152;
			$photo_h = 64;
			$r       = 8;
			// Photo as a top-rounded-only path.
			$tx_l = $x;
			$tx_r = $x + $w;
			$top  = $y;
			$bot  = $y + $photo_h;
			$photo_path = 'M' . ( $tx_l + $r ) . ' ' . $top
				. ' L' . ( $tx_r - $r ) . ' ' . $top
				. ' A' . $r . ' ' . $r . ' 0 0 1 ' . $tx_r . ' ' . ( $top + $r )
				. ' L' . $tx_r . ' ' . $bot
				. ' L' . $tx_l . ' ' . $bot
				. ' L' . $tx_l . ' ' . ( $top + $r )
				. ' A' . $r . ' ' . $r . ' 0 0 1 ' . ( $tx_l + $r ) . ' ' . $top . ' Z';
			$ty = $bot + 16;
			return ''
				// Dark card background
				. '<rect x="' . $x . '" y="' . $y . '" width="' . $w . '" height="' . $h . '" rx="' . $r . '" fill="#1f1f1f"/>'
				// Photo (top-rounded only)
				. '<path d="' . $photo_path . '" fill="#e2dcf7"/>'
				// Quote circle straddling the boundary
				. '<circle cx="' . ( $x + 16 ) . '" cy="' . $bot . '" r="8" fill="#fff"/>'
				. '<text x="' . ( $x + 16 ) . '" y="' . ( $bot + 3.5 ) . '" text-anchor="middle" font-size="10" font-weight="800" fill="#6c5ce7">,,</text>'
				// Text lines
				. '<rect x="' . ( $x + 8 ) . '" y="' . $ty . '" width="' . ( $w - 16 ) . '" height="3" rx="1" fill="#9d9d9d"/>'
				. '<rect x="' . ( $x + 8 ) . '" y="' . ( $ty + 7 ) . '" width="' . ( $w - 22 ) . '" height="3" rx="1" fill="#9d9d9d"/>'
				. '<rect x="' . ( $x + 8 ) . '" y="' . ( $ty + 14 ) . '" width="' . ( $w - 30 ) . '" height="3" rx="1" fill="#9d9d9d"/>'
				// Name + role at bottom-left
				. '<rect x="' . ( $x + 8 ) . '" y="' . ( $y + $h - 20 ) . '" width="42" height="4" rx="1" fill="#fff"/>'
				. '<rect x="' . ( $x + 8 ) . '" y="' . ( $y + $h - 11 ) . '" width="58" height="3" rx="1" fill="#787c82"/>';
		};
		return '<svg class="qt-wf qt-wf-v6" viewBox="0 0 320 180" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="V6 photo hero + dark card layout">'
			. '<rect width="320" height="180" fill="#f8f6ff"/>'
			. $card( 10 ) . $card( 113 ) . $card( 216 )
			. '</svg>';
	}

	/** V7 — pastel colored, slightly rotated stacked cards. */
	public static function wireframe_v7() {
		$colors  = array( '#fce4d9', '#fff3a6', '#c9b8ff', '#fce4d9' );
		$angles  = array( -3, 2, -1, 3 );
		$x_pos   = array( 10, 88, 168, 246 );
		$cards   = '';
		for ( $i = 0; $i < 4; $i++ ) {
			$x  = $x_pos[ $i ];
			$y  = 22;
			$w  = 70;
			$h  = 140;
			$cx = $x + $w / 2;
			$cy = $y + $h / 2;
			$cards .= '<g transform="rotate(' . $angles[ $i ] . ' ' . $cx . ' ' . $cy . ')">'
				. '<rect x="' . $x . '" y="' . $y . '" width="' . $w . '" height="' . $h . '" rx="6" fill="' . $colors[ $i ] . '"/>'
				// quote at top-left
				. '<rect x="' . ( $x + 7 ) . '" y="' . ( $y + 7 ) . '" width="8" height="6" rx="1" fill="#1d2327"/>'
				// text lines
				. '<rect x="' . ( $x + 7 ) . '" y="' . ( $y + 26 ) . '" width="' . ( $w - 14 ) . '" height="3" rx="1" fill="#1d2327"/>'
				. '<rect x="' . ( $x + 7 ) . '" y="' . ( $y + 33 ) . '" width="' . ( $w - 18 ) . '" height="3" rx="1" fill="#1d2327"/>'
				. '<rect x="' . ( $x + 7 ) . '" y="' . ( $y + 40 ) . '" width="' . ( $w - 12 ) . '" height="3" rx="1" fill="#1d2327"/>'
				. '<rect x="' . ( $x + 7 ) . '" y="' . ( $y + 47 ) . '" width="' . ( $w - 22 ) . '" height="3" rx="1" fill="#1d2327"/>'
				. '<rect x="' . ( $x + 7 ) . '" y="' . ( $y + 54 ) . '" width="' . ( $w - 16 ) . '" height="3" rx="1" fill="#1d2327"/>'
				. '<rect x="' . ( $x + 7 ) . '" y="' . ( $y + 61 ) . '" width="' . ( $w - 28 ) . '" height="3" rx="1" fill="#1d2327"/>'
				// avatar + name at bottom
				. '<circle cx="' . ( $x + 14 ) . '" cy="' . ( $y + 118 ) . '" r="6" fill="#fff" stroke="#1d2327" stroke-width="0.5"/>'
				. '<rect x="' . ( $x + 24 ) . '" y="' . ( $y + 115 ) . '" width="38" height="4" rx="1" fill="#1d2327"/>'
				. '<rect x="' . ( $x + 24 ) . '" y="' . ( $y + 123 ) . '" width="32" height="3" rx="1" fill="#1d2327" fill-opacity="0.6"/>'
				. '</g>';
		}
		return '<svg class="qt-wf qt-wf-v7" viewBox="0 0 320 180" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="V7 pastel tilted cards layout">'
			. '<rect width="320" height="180" fill="#f8f6ff"/>'
			. $cards
			. '</svg>';
	}

	/** V8 — split: dark content left (quote watermark + text), photo top-right, dark name bottom-right. */
	public static function wireframe_v8() {
		$x = 18;
		$y = 28;
		$w = 284;
		$h = 124;
		$left_w = 170;
		return '<svg class="qt-wf qt-wf-v8" viewBox="0 0 320 180" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="V8 split panel layout">'
			. '<rect width="320" height="180" fill="#f8f6ff"/>'
			// container outline
			// left section (dark)
			. '<rect x="' . $x . '" y="' . $y . '" width="' . $left_w . '" height="' . $h . '" rx="6" fill="#1d2327"/>'
			// quote watermark 50% opacity
			. '<text x="' . ( $x + 18 ) . '" y="' . ( $y + 40 ) . '" font-size="32" font-weight="800" fill="#fff" fill-opacity="0.18">"</text>'
			// description text
			. '<rect x="' . ( $x + 14 ) . '" y="' . ( $y + 52 ) . '" width="140" height="4" rx="1" fill="#fff"/>'
			. '<rect x="' . ( $x + 14 ) . '" y="' . ( $y + 62 ) . '" width="130" height="4" rx="1" fill="#fff"/>'
			. '<rect x="' . ( $x + 14 ) . '" y="' . ( $y + 72 ) . '" width="142" height="4" rx="1" fill="#fff"/>'
			. '<rect x="' . ( $x + 14 ) . '" y="' . ( $y + 82 ) . '" width="120" height="4" rx="1" fill="#fff"/>'
			. '<rect x="' . ( $x + 14 ) . '" y="' . ( $y + 92 ) . '" width="100" height="4" rx="1" fill="#fff"/>'
			// right top: photo (2/3 of right column height)
			. '<rect x="' . ( $x + $left_w + 6 ) . '" y="' . $y . '" width="' . ( $w - $left_w - 6 ) . '" height="' . ( $h * 0.66 - 3 ) . '" rx="6" fill="#e2dcf7"/>'
			// right bottom: author (1/3 of right column height)
			. '<rect x="' . ( $x + $left_w + 6 ) . '" y="' . ( $y + $h * 0.66 + 3 ) . '" width="' . ( $w - $left_w - 6 ) . '" height="' . ( $h * 0.34 - 3 ) . '" rx="6" fill="#1d2327"/>'
			. '<rect x="' . ( $x + $left_w + 22 ) . '" y="' . ( $y + $h * 0.66 + 14 ) . '" width="60" height="5" rx="1" fill="#fff"/>'
			. '<rect x="' . ( $x + $left_w + 22 ) . '" y="' . ( $y + $h * 0.66 + 25 ) . '" width="70" height="3" rx="1" fill="#787c82"/>'
			. '</svg>';
	}

	/** V5 — photo grid + tabbed quote below. */
	public static function wireframe_v5() {
		$avatars = '';
		// Row 1: 14 avatars
		for ( $i = 0; $i < 14; $i++ ) {
			$x = 18 + $i * 21;
			$active = ( 6 === $i ) ? '#6c5ce7' : '#c3c4c7';
			$fill   = ( 6 === $i ) ? '#e2dcf7' : '#f0f0f1';
			$avatars .= '<circle cx="' . $x . '" cy="25" r="9" fill="' . $fill . '" stroke="' . $active . '" stroke-width="' . ( 6 === $i ? 2 : 1 ) . '"/>';
		}
		// Row 2: 12 avatars centered
		for ( $i = 0; $i < 12; $i++ ) {
			$x = 40 + $i * 21;
			$avatars .= '<circle cx="' . $x . '" cy="48" r="9" fill="#f0f0f1" stroke="#c3c4c7"/>';
		}
		return '<svg class="qt-wf qt-wf-v5" viewBox="0 0 320 180" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="V5 avatar grid with active quote layout">'
			. '<rect width="320" height="180" fill="#f8f6ff"/>'
			. $avatars
			// Selected name
			. '<rect x="115" y="75" width="90" height="8" rx="2" fill="#1a1a1a"/>'
			. '<rect x="115" y="89" width="90" height="4" rx="2" fill="#787c82"/>'
			// Quote
			. '<rect x="40" y="110" width="240" height="4" rx="2" fill="#3c3c3c"/>'
			. '<rect x="50" y="120" width="220" height="4" rx="2" fill="#3c3c3c"/>'
			. '<rect x="60" y="130" width="200" height="4" rx="2" fill="#3c3c3c"/>'
			. '<rect x="80" y="140" width="160" height="4" rx="2" fill="#3c3c3c"/>'
			. '</svg>';
	}

	private static function wf_card( $x, $y, $w, $h ) {
		$pad = 6;
		return '<rect x="' . $x . '" y="' . $y . '" width="' . $w . '" height="' . $h . '" rx="6" fill="#fff" stroke="#c3c4c7"/>'
			. '<rect x="' . ( $x + $pad ) . '" y="' . ( $y + $pad ) . '" width="10" height="8" rx="1" fill="#6c5ce7"/>'
			. '<rect x="' . ( $x + $pad ) . '" y="' . ( $y + $pad + 14 ) . '" width="' . ( $w - 12 ) . '" height="3" rx="1" fill="#c3c4c7"/>'
			. '<rect x="' . ( $x + $pad ) . '" y="' . ( $y + $pad + 22 ) . '" width="' . ( $w - 20 ) . '" height="3" rx="1" fill="#c3c4c7"/>'
			. '<rect x="' . ( $x + $pad ) . '" y="' . ( $y + $pad + 30 ) . '" width="' . ( $w - 16 ) . '" height="3" rx="1" fill="#c3c4c7"/>'
			. '<circle cx="' . ( $x + $pad + 4 ) . '" cy="' . ( $y + $h - $pad - 5 ) . '" r="4" fill="#e2dcf7"/>'
			. '<rect x="' . ( $x + $pad + 12 ) . '" y="' . ( $y + $h - $pad - 8 ) . '" width="30" height="3" rx="1" fill="#1d2327"/>'
			. '<rect x="' . ( $x + $pad + 12 ) . '" y="' . ( $y + $h - $pad - 3 ) . '" width="40" height="2" rx="1" fill="#787c82"/>';
	}

	/* --- Pro layout wireframes (shown as locked teasers in the free admin) --- */

	/** V9 — Masonry wall (3 columns, varying heights). */
	public static function wireframe_v9() {
		return '<svg class="qt-wf qt-wf-v9" viewBox="0 0 320 180" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="V9 masonry wall layout">'
			. '<rect width="320" height="180" fill="#f8f6ff"/>'
			. self::wf_card( 12, 14, 92, 70 ) . self::wf_card( 12, 92, 92, 50 )
			. self::wf_card( 114, 14, 92, 95 ) . self::wf_card( 114, 117, 92, 48 )
			. self::wf_card( 216, 14, 92, 55 ) . self::wf_card( 216, 77, 92, 88 )
			. '</svg>';
	}

	/** V10 — One large featured card beside a grid of smaller ones. */
	public static function wireframe_v10() {
		return '<svg class="qt-wf qt-wf-v10" viewBox="0 0 320 180" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="V10 featured plus grid layout">'
			. '<rect width="320" height="180" fill="#f8f6ff"/>'
			. self::wf_card( 12, 14, 150, 152 )
			. self::wf_card( 172, 14, 136, 46 ) . self::wf_card( 172, 66, 136, 46 ) . self::wf_card( 172, 118, 136, 48 )
			. '</svg>';
	}

	/** V11 — Spotlight card + clickable side list. */
	public static function wireframe_v11() {
		$out = '<svg class="qt-wf qt-wf-v11" viewBox="0 0 320 180" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="V11 spotlight plus list layout">'
			. '<rect width="320" height="180" fill="#f8f6ff"/>'
			. self::wf_card( 12, 14, 180, 152 );
		for ( $i = 0; $i < 4; $i++ ) {
			$y  = 14 + $i * 39;
			$on = 0 === $i;
			$out .= '<rect x="200" y="' . $y . '" width="108" height="32" rx="5" fill="' . ( $on ? '#f8f6ff' : '#fff' ) . '" stroke="' . ( $on ? '#6c5ce7' : '#c3c4c7' ) . '"/>'
				. '<circle cx="216" cy="' . ( $y + 16 ) . '" r="9" fill="#e2dcf7"/>'
				. '<rect x="232" y="' . ( $y + 10 ) . '" width="50" height="4" rx="1" fill="#1d2327"/>'
				. '<rect x="232" y="' . ( $y + 18 ) . '" width="64" height="3" rx="1" fill="#787c82"/>';
		}
		return $out . '</svg>';
	}

	/** V12 — Editorial big quote with avatar strip. */
	public static function wireframe_v12() {
		$out = '<svg class="qt-wf qt-wf-v12" viewBox="0 0 320 180" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="V12 editorial big quote layout">'
			. '<rect width="320" height="180" fill="#f8f6ff"/>';
		for ( $i = 0; $i < 9; $i++ ) {
			$x  = 64 + $i * 22;
			$on = 4 === $i;
			$out .= '<circle cx="' . $x . '" cy="32" r="9" fill="' . ( $on ? '#e2dcf7' : '#f0f0f1' ) . '" stroke="' . ( $on ? '#6c5ce7' : '#c3c4c7' ) . '" stroke-width="' . ( $on ? 2 : 1 ) . '"/>';
		}
		$out .= '<rect x="60" y="66" width="14" height="11" rx="2" fill="#6c5ce7"/>'
			. '<rect x="56" y="92" width="208" height="6" rx="3" fill="#3c3c3c"/>'
			. '<rect x="76" y="106" width="168" height="6" rx="3" fill="#3c3c3c"/>'
			. '<rect x="120" y="134" width="80" height="5" rx="2" fill="#1d2327"/>'
			. '<rect x="130" y="146" width="60" height="3" rx="1" fill="#787c82"/>';
		return $out . '</svg>';
	}
}
