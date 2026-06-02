<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Qt_Settings {

	const OPTION = 'qt_settings';

	public static function defaults() {
		$defaults = array(
			'bg_color'         => '#ffffff',
			'text_color'       => '#3c3c3c',
			'meta_color'       => '#787c82',
			'padding'          => 24,
			'border_width'     => 1,
			'border_color'     => '#e0e0e0',
			'border_radius'    => 12,
			'quote_color'      => '#6c5ce7',
			'name_font_color'  => '#1a1a1a',
			'speed'            => 30,
			'fade_edges'       => 1,
			'gap'              => 20,
			'heading_size'     => 18,
			'text_size'        => 15,
			'remove_data_on_uninstall' => 0,
		);
		/**
		 * Filter the global Settings page defaults.
		 *
		 * Pro plugins can add their own option keys here, then register
		 * matching form fields via the qt_register_settings_fields action.
		 *
		 * @param array $defaults
		 */
		return apply_filters( 'qt_settings_defaults', $defaults );
	}

	public static function get( $key = null ) {
		$opts = get_option( self::OPTION, array() );
		$opts = wp_parse_args( $opts, self::defaults() );
		if ( null === $key ) {
			return $opts;
		}
		return isset( $opts[ $key ] ) ? $opts[ $key ] : null;
	}

	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'menu' ) );
		add_action( 'admin_init', array( __CLASS__, 'register' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue' ) );
	}

	public static function enqueue( $hook ) {
		if ( strpos( (string) $hook, 'qt-settings' ) === false ) {
			return;
		}
		wp_enqueue_style( 'qt-admin', QT_URL . 'assets/css/admin.css', array(), qt_asset_ver( 'assets/css/admin.css' ) );
	}

	public static function menu() {
		add_submenu_page(
			'edit.php?post_type=' . QT_POST_TYPE,
			__( 'Testimonials Settings', 'qaiyo-testimonials' ),
			__( 'Settings', 'qaiyo-testimonials' ),
			'manage_options',
			'qt-settings',
			array( __CLASS__, 'render_page' )
		);
	}

	public static function register() {
		register_setting(
			'qt_settings_group',
			self::OPTION,
			array(
				'type'              => 'array',
				'sanitize_callback' => array( __CLASS__, 'sanitize' ),
				'default'           => self::defaults(),
			)
		);
	}

	public static function sanitize( $input ) {
		$defaults = self::defaults();
		$out      = array();
		foreach ( $defaults as $key => $def ) {
			if ( ! isset( $input[ $key ] ) ) {
				$out[ $key ] = $def;
				continue;
			}
			$val = $input[ $key ];
			if ( in_array( $key, array( 'bg_color', 'text_color', 'meta_color', 'border_color', 'quote_color', 'name_font_color' ), true ) ) {
				$clean       = sanitize_hex_color( $val );
				$out[ $key ] = $clean ? $clean : $def;
			} elseif ( in_array( $key, array( 'padding', 'border_width', 'border_radius', 'speed', 'gap', 'heading_size', 'text_size' ), true ) ) {
				$out[ $key ] = max( 0, absint( $val ) );
			} elseif ( in_array( $key, array( 'fade_edges', 'remove_data_on_uninstall' ), true ) ) {
				$out[ $key ] = $val ? 1 : 0;
			}
		}
		return $out;
	}

	public static function render_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$opts = self::get();
		?>
		<div class="wrap qt-wrap">
			<h1><span class="qt-brand">Qaiyo</span> <?php esc_html_e( 'Testimonials', 'qaiyo-testimonials' ); ?>
				<span class="qt-version">v<?php echo esc_html( QT_VERSION ); ?></span>
			</h1>
			<p class="qt-desc"><?php esc_html_e( 'Default frontend appearance for all testimonial layouts. Shortcode attributes override these defaults per instance.', 'qaiyo-testimonials' ); ?></p>

			<form method="post" action="options.php">
				<?php settings_fields( 'qt_settings_group' ); ?>

				<div class="qt-card">
					<h3><?php esc_html_e( 'Default colors & spacing', 'qaiyo-testimonials' ); ?></h3>
					<table class="form-table">
						<tr>
							<th><label for="bg_color"><?php esc_html_e( 'Card background', 'qaiyo-testimonials' ); ?></label></th>
							<td><input type="color" id="bg_color" name="<?php echo esc_attr( self::OPTION ); ?>[bg_color]" value="<?php echo esc_attr( $opts['bg_color'] ); ?>" /></td>
						</tr>
						<tr>
							<th><label for="text_color"><?php esc_html_e( 'Quote / description text', 'qaiyo-testimonials' ); ?></label></th>
							<td><input type="color" id="text_color" name="<?php echo esc_attr( self::OPTION ); ?>[text_color]" value="<?php echo esc_attr( $opts['text_color'] ); ?>" /></td>
						</tr>
						<tr>
							<th><label for="meta_color"><?php esc_html_e( 'Position / company text', 'qaiyo-testimonials' ); ?></label></th>
							<td><input type="color" id="meta_color" name="<?php echo esc_attr( self::OPTION ); ?>[meta_color]" value="<?php echo esc_attr( $opts['meta_color'] ); ?>" /></td>
						</tr>
						<tr>
							<th><label for="name_font_color"><?php esc_html_e( 'Customer name (cursive)', 'qaiyo-testimonials' ); ?></label></th>
							<td><input type="color" id="name_font_color" name="<?php echo esc_attr( self::OPTION ); ?>[name_font_color]" value="<?php echo esc_attr( $opts['name_font_color'] ); ?>" /></td>
						</tr>
						<tr>
							<th><label for="quote_color"><?php esc_html_e( 'Quote mark color', 'qaiyo-testimonials' ); ?></label></th>
							<td><input type="color" id="quote_color" name="<?php echo esc_attr( self::OPTION ); ?>[quote_color]" value="<?php echo esc_attr( $opts['quote_color'] ); ?>" /></td>
						</tr>
						<tr>
							<th><label for="border_color"><?php esc_html_e( 'Border color', 'qaiyo-testimonials' ); ?></label></th>
							<td><input type="color" id="border_color" name="<?php echo esc_attr( self::OPTION ); ?>[border_color]" value="<?php echo esc_attr( $opts['border_color'] ); ?>" /></td>
						</tr>
						<tr>
							<th><label for="border_width"><?php esc_html_e( 'Border width (px)', 'qaiyo-testimonials' ); ?></label></th>
							<td><input type="number" min="0" max="10" id="border_width" name="<?php echo esc_attr( self::OPTION ); ?>[border_width]" value="<?php echo esc_attr( $opts['border_width'] ); ?>" /></td>
						</tr>
						<tr>
							<th><label for="border_radius"><?php esc_html_e( 'Border radius (px)', 'qaiyo-testimonials' ); ?></label></th>
							<td><input type="number" min="0" max="40" id="border_radius" name="<?php echo esc_attr( self::OPTION ); ?>[border_radius]" value="<?php echo esc_attr( $opts['border_radius'] ); ?>" /></td>
						</tr>
						<tr>
							<th><label for="padding"><?php esc_html_e( 'Inner padding (px)', 'qaiyo-testimonials' ); ?></label></th>
							<td><input type="number" min="0" max="80" id="padding" name="<?php echo esc_attr( self::OPTION ); ?>[padding]" value="<?php echo esc_attr( $opts['padding'] ); ?>" /></td>
						</tr>
						<tr>
							<th><label for="gap"><?php esc_html_e( 'Gap between cards (px)', 'qaiyo-testimonials' ); ?></label></th>
							<td><input type="number" min="0" max="80" id="gap" name="<?php echo esc_attr( self::OPTION ); ?>[gap]" value="<?php echo esc_attr( $opts['gap'] ); ?>" /></td>
						</tr>
						<tr>
							<th><label for="heading_size"><?php esc_html_e( 'Heading font size (px)', 'qaiyo-testimonials' ); ?></label></th>
							<td><input type="number" min="10" max="48" id="heading_size" name="<?php echo esc_attr( self::OPTION ); ?>[heading_size]" value="<?php echo esc_attr( $opts['heading_size'] ); ?>" /></td>
						</tr>
						<tr>
							<th><label for="text_size"><?php esc_html_e( 'Description font size (px)', 'qaiyo-testimonials' ); ?></label></th>
							<td><input type="number" min="10" max="32" id="text_size" name="<?php echo esc_attr( self::OPTION ); ?>[text_size]" value="<?php echo esc_attr( $opts['text_size'] ); ?>" /></td>
						</tr>
					</table>
				</div>

				<div class="qt-card">
					<h3><?php esc_html_e( 'Slider behavior', 'qaiyo-testimonials' ); ?></h3>
					<table class="form-table">
						<tr>
							<th><label for="speed"><?php esc_html_e( 'Default speed (sec for full loop)', 'qaiyo-testimonials' ); ?></label></th>
							<td><input type="number" min="5" max="600" id="speed" name="<?php echo esc_attr( self::OPTION ); ?>[speed]" value="<?php echo esc_attr( $opts['speed'] ); ?>" />
								<span class="qt-hint"><?php esc_html_e( 'Lower = faster. Used for V1, V2, V3, V5.', 'qaiyo-testimonials' ); ?></span>
							</td>
						</tr>
						<tr>
							<th><label for="fade_edges"><?php esc_html_e( 'Fade edges on sliders', 'qaiyo-testimonials' ); ?></label></th>
							<td><label><input type="checkbox" id="fade_edges" name="<?php echo esc_attr( self::OPTION ); ?>[fade_edges]" value="1" <?php checked( 1, (int) $opts['fade_edges'] ); ?> /> <?php esc_html_e( 'Enabled by default (V1, V2, V3)', 'qaiyo-testimonials' ); ?></label></td>
						</tr>
					</table>
				</div>

				<div class="qt-card">
					<h3><?php esc_html_e( 'Data & maintenance', 'qaiyo-testimonials' ); ?></h3>
					<table class="form-table">
						<tr>
							<th><label for="remove_data_on_uninstall"><?php esc_html_e( 'When deleting the plugin', 'qaiyo-testimonials' ); ?></label></th>
							<td>
								<label>
									<input type="checkbox" id="remove_data_on_uninstall" name="<?php echo esc_attr( self::OPTION ); ?>[remove_data_on_uninstall]" value="1" <?php checked( 1, (int) $opts['remove_data_on_uninstall'] ); ?> />
									<?php esc_html_e( 'Permanently delete all testimonials, displays, categories and settings on uninstall', 'qaiyo-testimonials' ); ?>
								</label>
								<span class="qt-hint" style="display:block;margin-top:6px;">
									<?php esc_html_e( 'Off (default): your data is kept in the database if you delete the plugin, so reinstalling restores everything. On: deleting the plugin from the Plugins page also erases all data — this cannot be undone.', 'qaiyo-testimonials' ); ?>
								</span>
							</td>
						</tr>
					</table>
				</div>

				<?php submit_button(); ?>
			</form>

			<div class="qt-card">
				<h3><?php esc_html_e( 'Layout versions overview', 'qaiyo-testimonials' ); ?></h3>
				<p class="qt-hint" style="margin:0 0 12px;"><?php esc_html_e( 'Twelve layouts in total. V1–V6 are included free; V7–V12 are unlocked by the Qaiyo Testimonials Pro add-on.', 'qaiyo-testimonials' ); ?></p>
				<div class="qt-wireframe-gallery">
					<?php
					$upgrade_url = Qt_Catalog::upgrade_url();
					foreach ( Qt_Catalog::all() as $v => $t ) :
						$unlocked = Qt_Catalog::is_unlocked( $v );
						?>
						<div class="qt-wf-item <?php echo $unlocked ? '' : 'qt-wf-locked'; ?>">
							<span class="qt-wf-wrap">
								<?php echo Qt_Svg::wireframe( $v ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
								<?php if ( ! $unlocked ) : ?>
									<span class="qt-pro-badge"><?php esc_html_e( 'PRO', 'qaiyo-testimonials' ); ?></span>
									<span class="qt-lock-overlay" aria-hidden="true">
										<svg viewBox="0 0 24 24" width="20" height="20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M6 10V8a6 6 0 1 1 12 0v2h1a1 1 0 0 1 1 1v9a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1v-9a1 1 0 0 1 1-1h1zm2 0h8V8a4 4 0 1 0-8 0v2z" fill="#fff"/></svg>
									</span>
								<?php endif; ?>
							</span>
							<h4><?php echo esc_html( $t[0] ); ?></h4>
							<p><?php echo esc_html( $t[1] ); ?></p>
							<?php if ( ! $unlocked ) : ?>
								<a class="qt-unlock-link" href="<?php echo esc_url( $upgrade_url ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Unlock in Pro →', 'qaiyo-testimonials' ); ?></a>
							<?php endif; ?>
						</div>
					<?php endforeach; ?>
				</div>
				<?php if ( Qt_Catalog::has_locked() ) : ?>
					<p style="margin:14px 0 0;padding:12px 16px;background:#f8f6ff;border:1px solid #e2dcf7;border-radius:4px;">
						<strong style="color:#6c5ce7;"><?php esc_html_e( 'Want layouts V7–V12, star ratings, a submission form, video testimonials and more?', 'qaiyo-testimonials' ); ?></strong><br />
						<a class="button button-primary" style="margin-top:8px;background:#6c5ce7;border-color:#6c5ce7;" href="<?php echo esc_url( $upgrade_url ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Get Qaiyo Testimonials Pro', 'qaiyo-testimonials' ); ?></a>
					</p>
				<?php endif; ?>
				<p style="margin-top:14px;">
					<a class="button button-primary" href="<?php echo esc_url( admin_url( 'post-new.php?post_type=' . Qt_Display::POST_TYPE ) ); ?>"><?php esc_html_e( 'Create a new Display', 'qaiyo-testimonials' ); ?></a>
					<a class="button" href="<?php echo esc_url( admin_url( 'edit.php?post_type=' . Qt_Display::POST_TYPE ) ); ?>"><?php esc_html_e( 'Manage Displays', 'qaiyo-testimonials' ); ?></a>
				</p>
			</div>

			<div class="qt-card">
				<h3><?php esc_html_e( 'Shortcode reference', 'qaiyo-testimonials' ); ?></h3>
				<p class="qt-hint" style="margin:0 0 10px;"><?php esc_html_e( 'Recommended: create a Display and use', 'qaiyo-testimonials' ); ?> <code style="background:#e8e8e8;padding:1px 6px;border-radius:3px;">[qaiyo_testimonials display="ID"]</code>. <?php esc_html_e( 'Or use raw attributes:', 'qaiyo-testimonials' ); ?></p>
				<table class="qt-shortcode-table">
					<tr><th><?php esc_html_e( 'Attribute', 'qaiyo-testimonials' ); ?></th><th><?php esc_html_e( 'Values', 'qaiyo-testimonials' ); ?></th><th><?php esc_html_e( 'Used by', 'qaiyo-testimonials' ); ?></th></tr>
					<tr><td><code>display</code></td><td><?php esc_html_e( 'Display post ID', 'qaiyo-testimonials' ); ?></td><td><?php esc_html_e( 'All (overrides every other attribute except those you also pass explicitly)', 'qaiyo-testimonials' ); ?></td></tr>
					<tr><td><code>version</code></td><td>v1–v6 (<?php esc_html_e( 'free', 'qaiyo-testimonials' ); ?>), v7–v12 (<?php esc_html_e( 'Pro', 'qaiyo-testimonials' ); ?>)</td><td><?php esc_html_e( 'All', 'qaiyo-testimonials' ); ?></td></tr>
					<tr><td><code>content_bg</code> / <code>content_color</code></td><td>#hex</td><td>V8 (<?php esc_html_e( 'left description box', 'qaiyo-testimonials' ); ?>)</td></tr>
					<tr><td><code>author_bg</code> / <code>author_color</code></td><td>#hex</td><td>V8 (<?php esc_html_e( 'right author box', 'qaiyo-testimonials' ); ?>)</td></tr>
					<tr><td><code>quote_style</code></td><td>1, 2, 3, 4</td><td><?php esc_html_e( 'All except V5', 'qaiyo-testimonials' ); ?></td></tr>
					<tr><td><code>category</code></td><td><?php esc_html_e( 'category slug', 'qaiyo-testimonials' ); ?></td><td><?php esc_html_e( 'All', 'qaiyo-testimonials' ); ?></td></tr>
					<tr><td><code>ids</code></td><td>1,2,3</td><td><?php esc_html_e( 'All', 'qaiyo-testimonials' ); ?></td></tr>
					<tr><td><code>limit</code></td><td>-1, 10, 20…</td><td><?php esc_html_e( 'All', 'qaiyo-testimonials' ); ?></td></tr>
					<tr><td><code>rows</code></td><td>1, 2</td><td>V1</td></tr>
					<tr><td><code>columns</code></td><td>2, 3</td><td>V2</td></tr>
					<tr><td><code>direction</code></td><td>left, right, up, down</td><td>V1, V2, V3</td></tr>
					<tr><td><code>alt_direction</code></td><td>0, 1</td><td>V1, V2 (<?php esc_html_e( 'alternate per row/column', 'qaiyo-testimonials' ); ?>)</td></tr>
					<tr><td><code>speed</code></td><td>5–600</td><td>V1, V2, V3, V5</td></tr>
					<tr><td><code>fade_edges</code></td><td>0, 1</td><td>V1, V2, V3</td></tr>
					<tr><td><code>bg_color</code> / <code>text_color</code> / <code>border_color</code> / <code>quote_color</code></td><td>#hex</td><td><?php esc_html_e( 'All except V5', 'qaiyo-testimonials' ); ?></td></tr>
					<tr><td><code>padding</code> / <code>border_width</code> / <code>border_radius</code></td><td>px</td><td><?php esc_html_e( 'All except V5', 'qaiyo-testimonials' ); ?></td></tr>
					<tr><td><code>heading_size</code> / <code>text_size</code></td><td>px</td><td><?php esc_html_e( 'All', 'qaiyo-testimonials' ); ?></td></tr>
				</table>
			</div>

			<p class="qt-footer">Qaiyo Testimonials v<?php echo esc_html( QT_VERSION ); ?> &mdash; <?php esc_html_e( 'Made by', 'qaiyo-testimonials' ); ?>: <a href="https://qaiyo-plugins.com" target="_blank" rel="noopener noreferrer">Qaiyo by PixelDesigns</a></p>
		</div>
		<?php
	}
}
