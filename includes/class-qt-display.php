<?php
/**
 * Display Builder — qt_display CPT with version-first wizard UI.
 *
 * @package qaiyo-testimonials
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Qt_Display {

	const POST_TYPE = 'qt_display';

	public static function init() {
		add_action( 'init', array( __CLASS__, 'register_post_type' ) );
		add_action( 'add_meta_boxes', array( __CLASS__, 'meta_boxes' ) );
		add_action( 'save_post_' . self::POST_TYPE, array( __CLASS__, 'save' ), 10, 2 );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue' ) );
		add_filter( 'manage_' . self::POST_TYPE . '_posts_columns', array( __CLASS__, 'columns' ) );
		add_action( 'manage_' . self::POST_TYPE . '_posts_custom_column', array( __CLASS__, 'column_content' ), 10, 2 );
		// Hide the default content editor & permalink area visually via screen options.
		add_action( 'edit_form_after_title', array( __CLASS__, 'after_title_hint' ) );
	}

	public static function register_post_type() {
		$labels = array(
			'name'               => __( 'Displays', 'qaiyo-testimonials' ),
			'singular_name'      => __( 'Display', 'qaiyo-testimonials' ),
			'menu_name'          => __( 'Displays', 'qaiyo-testimonials' ),
			'add_new'            => __( 'Add New Display', 'qaiyo-testimonials' ),
			'add_new_item'       => __( 'Add New Display', 'qaiyo-testimonials' ),
			'edit_item'          => __( 'Edit Display', 'qaiyo-testimonials' ),
			'new_item'           => __( 'New Display', 'qaiyo-testimonials' ),
			'all_items'          => __( 'Displays', 'qaiyo-testimonials' ),
			'search_items'       => __( 'Search Displays', 'qaiyo-testimonials' ),
			'not_found'          => __( 'No displays found', 'qaiyo-testimonials' ),
			'not_found_in_trash' => __( 'No displays found in Trash', 'qaiyo-testimonials' ),
		);
		register_post_type(
			self::POST_TYPE,
			array(
				'labels'              => $labels,
				'public'              => false,
				'show_ui'             => true,
				'show_in_menu'        => 'edit.php?post_type=' . QT_POST_TYPE,
				'supports'            => array( 'title' ),
				'capability_type'     => 'post',
				'has_archive'         => false,
				'rewrite'             => false,
				'exclude_from_search' => true,
				'show_in_rest'        => false,
			)
		);
	}

	public static function enqueue( $hook ) {
		global $post;
		if ( ! in_array( $hook, array( 'post.php', 'post-new.php', 'edit.php' ), true ) ) {
			return;
		}
		if ( ! $post || self::POST_TYPE !== $post->post_type ) {
			$screen = get_current_screen();
			if ( ! $screen || self::POST_TYPE !== $screen->post_type ) {
				return;
			}
		}
		wp_enqueue_style( 'qt-admin', QT_URL . 'assets/css/admin.css', array(), qt_asset_ver( 'assets/css/admin.css' ) );
		wp_enqueue_script( 'qt-display', QT_URL . 'assets/js/display-builder.js', array( 'jquery' ), QT_VERSION, true );
	}

	public static function after_title_hint( $post ) {
		if ( self::POST_TYPE !== $post->post_type ) {
			return;
		}
		echo '<p class="qt-hint" style="margin:6px 2px 0;font-size:12px;color:#787c82;">';
		esc_html_e( 'Give this display a name (only visible to admins).', 'qaiyo-testimonials' );
		echo '</p>';
	}

	public static function columns( $cols ) {
		$new = array(
			'cb'         => $cols['cb'],
			'title'      => $cols['title'],
			'qt_version' => __( 'Version', 'qaiyo-testimonials' ),
			'qt_cat'     => __( 'Category', 'qaiyo-testimonials' ),
			'qt_sc'      => __( 'Shortcode', 'qaiyo-testimonials' ),
			'date'       => $cols['date'],
		);
		return $new;
	}

	public static function column_content( $col, $post_id ) {
		if ( 'qt_version' === $col ) {
			$v = get_post_meta( $post_id, '_qtd_version', true );
			echo '<span class="qt-badge qt-badge-version">' . esc_html( strtoupper( $v ? $v : 'v1' ) ) . '</span>';
		} elseif ( 'qt_cat' === $col ) {
			$slug = get_post_meta( $post_id, '_qtd_category', true );
			if ( $slug ) {
				$term = get_term_by( 'slug', $slug, QT_TAXONOMY );
				echo $term ? esc_html( $term->name ) : '<em style="color:#787c82;">' . esc_html( $slug ) . '</em>';
			} else {
				echo '<span style="color:#787c82;">' . esc_html__( 'All testimonials', 'qaiyo-testimonials' ) . '</span>';
			}
		} elseif ( 'qt_sc' === $col ) {
			echo '<code style="background:#e8e8e8;padding:2px 6px;border-radius:3px;font-size:12px;">[qaiyo_testimonials display="' . (int) $post_id . '"]</code>';
		}
	}

	public static function meta_boxes() {
		remove_meta_box( 'submitdiv', self::POST_TYPE, 'side' );
		add_meta_box( 'qt_display_save', __( 'Save', 'qaiyo-testimonials' ), array( __CLASS__, 'box_save' ), self::POST_TYPE, 'side', 'high' );
		add_meta_box( 'qt_display_shortcode', __( 'Shortcode', 'qaiyo-testimonials' ), array( __CLASS__, 'box_shortcode' ), self::POST_TYPE, 'side', 'high' );
		add_meta_box( 'qt_display_version', __( '1. Choose layout version', 'qaiyo-testimonials' ), array( __CLASS__, 'box_version' ), self::POST_TYPE, 'normal', 'high' );
		add_meta_box( 'qt_display_quote', __( '2. Quote mark style', 'qaiyo-testimonials' ), array( __CLASS__, 'box_quote' ), self::POST_TYPE, 'normal', 'high' );
		add_meta_box( 'qt_display_source', __( '3. Content source', 'qaiyo-testimonials' ), array( __CLASS__, 'box_source' ), self::POST_TYPE, 'normal', 'high' );
		add_meta_box( 'qt_display_behavior', __( '4. Layout-specific options', 'qaiyo-testimonials' ), array( __CLASS__, 'box_behavior' ), self::POST_TYPE, 'normal', 'default' );
		add_meta_box( 'qt_display_design', __( '5. Design overrides (optional)', 'qaiyo-testimonials' ), array( __CLASS__, 'box_design' ), self::POST_TYPE, 'normal', 'low' );
	}

	private static function get_meta( $post_id, $key, $default = '' ) {
		$val = get_post_meta( $post_id, $key, true );
		return ( '' === $val || null === $val ) ? $default : $val;
	}

	public static function box_save( $post ) {
		wp_nonce_field( 'qt_display_save', 'qt_display_nonce' );
		?>
		<p><?php submit_button( __( 'Save display', 'qaiyo-testimonials' ), 'primary large', 'publish', false, array( 'style' => 'width:100%;text-align:center;' ) ); ?></p>
		<?php if ( 'publish' === $post->post_status ) : ?>
			<p class="qt-hint"><?php esc_html_e( 'Display is saved and ready to use.', 'qaiyo-testimonials' ); ?></p>
		<?php endif; ?>
		<?php
	}

	public static function box_shortcode( $post ) {
		if ( 'auto-draft' === $post->post_status ) {
			echo '<p class="qt-hint">' . esc_html__( 'Save the display first to get a shortcode.', 'qaiyo-testimonials' ) . '</p>';
			return;
		}
		$sc = '[qaiyo_testimonials display="' . (int) $post->ID . '"]';
		?>
		<p><?php esc_html_e( 'Paste this shortcode anywhere on your site:', 'qaiyo-testimonials' ); ?></p>
		<input type="text" readonly value="<?php echo esc_attr( $sc ); ?>" class="qt-shortcode-input" onfocus="this.select();" style="width:100%;font-family:monospace;background:#f0f0f1;padding:8px;border:1px solid #c3c4c7;border-radius:3px;" />
		<p class="qt-hint" style="margin-top:8px;"><?php esc_html_e( 'Click to select, then copy.', 'qaiyo-testimonials' ); ?></p>
		<?php
	}

	public static function box_version( $post ) {
		$current     = self::get_meta( $post->ID, '_qtd_version', 'v1' );
		$catalog     = Qt_Catalog::all();
		$upgrade_url = Qt_Catalog::upgrade_url();

		// Allow filtering legacy entries too (back-compat for the old filter name).
		$catalog = apply_filters( 'qt_display_version_labels', $catalog );
		?>
		<div class="qt-version-picker">
			<?php
			foreach ( $catalog as $v => $lbl ) :
				$unlocked = Qt_Catalog::is_unlocked( $v );
				$wf       = Qt_Svg::wireframe( $v );

				if ( $unlocked ) :
					?>
					<label class="qt-version-card <?php echo $current === $v ? 'is-active' : ''; ?>" data-version="<?php echo esc_attr( $v ); ?>">
						<input type="radio" name="qtd_version" value="<?php echo esc_attr( $v ); ?>" <?php checked( $current, $v ); ?> />
						<span class="qt-wf-wrap"><?php echo $wf; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
						<span class="qt-version-title"><?php echo esc_html( $lbl[0] ); ?></span>
						<span class="qt-version-desc"><?php echo esc_html( $lbl[1] ); ?></span>
					</label>
					<?php
				else :
					?>
					<a class="qt-version-card qt-version-locked" href="<?php echo esc_url( $upgrade_url ); ?>" target="_blank" rel="noopener noreferrer" title="<?php esc_attr_e( 'Available in Qaiyo Testimonials Pro', 'qaiyo-testimonials' ); ?>">
						<span class="qt-pro-badge"><?php esc_html_e( 'PRO', 'qaiyo-testimonials' ); ?></span>
						<span class="qt-wf-wrap"><?php echo $wf; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							<span class="qt-lock-overlay" aria-hidden="true">
								<svg viewBox="0 0 24 24" width="22" height="22" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M6 10V8a6 6 0 1 1 12 0v2h1a1 1 0 0 1 1 1v9a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1v-9a1 1 0 0 1 1-1h1zm2 0h8V8a4 4 0 1 0-8 0v2z" fill="#fff"/></svg>
							</span>
						</span>
						<span class="qt-version-title"><?php echo esc_html( $lbl[0] ); ?></span>
						<span class="qt-version-desc"><?php echo esc_html( $lbl[1] ); ?></span>
						<span class="qt-unlock-link"><?php esc_html_e( 'Unlock in Pro →', 'qaiyo-testimonials' ); ?></span>
					</a>
					<?php
				endif;
			endforeach;
			?>
		</div>
		<?php
	}

	public static function box_quote( $post ) {
		$current = (int) self::get_meta( $post->ID, '_qtd_quote_style', 1 );
		$styles = array(
			1 => __( 'Style 1 — Teardrop "66"', 'qaiyo-testimonials' ),
			2 => __( 'Style 2 — Bold block', 'qaiyo-testimonials' ),
			3 => __( 'Style 3 — Calligraphic comma', 'qaiyo-testimonials' ),
			4 => __( 'Style 4 — Speech-bubble outline', 'qaiyo-testimonials' ),
		);
		?>
		<div class="qt-quote-picker">
			<?php foreach ( $styles as $s => $label ) : ?>
				<label class="qt-quote-card <?php echo $current === $s ? 'is-active' : ''; ?>" data-quote="<?php echo esc_attr( $s ); ?>">
					<input type="radio" name="qtd_quote_style" value="<?php echo esc_attr( $s ); ?>" <?php checked( $current, $s ); ?> />
					<span class="qt-quote-preview qt-quote-preview-<?php echo esc_attr( $s ); ?>"><?php echo call_user_func( array( 'Qt_Svg', 'quote_style_' . $s ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
					<span class="qt-quote-label"><?php echo esc_html( $label ); ?></span>
				</label>
			<?php endforeach; ?>
		</div>
		<?php
	}

	public static function box_source( $post ) {
		$cat   = self::get_meta( $post->ID, '_qtd_category', '' );
		$limit = self::get_meta( $post->ID, '_qtd_limit', -1 );
		$terms = get_terms(
			array(
				'taxonomy'   => QT_TAXONOMY,
				'hide_empty' => false,
			)
		);
		?>
		<p>
			<label for="qtd_category"><strong><?php esc_html_e( 'Category', 'qaiyo-testimonials' ); ?></strong></label><br />
			<select id="qtd_category" name="qtd_category" style="min-width:280px;">
				<option value=""><?php esc_html_e( 'All testimonials', 'qaiyo-testimonials' ); ?></option>
				<?php if ( ! is_wp_error( $terms ) ) : foreach ( $terms as $term ) : ?>
					<option value="<?php echo esc_attr( $term->slug ); ?>" <?php selected( $cat, $term->slug ); ?>><?php echo esc_html( $term->name ); ?></option>
				<?php endforeach; endif; ?>
			</select>
			<span class="qt-hint"><?php esc_html_e( 'New testimonials added to this category will appear automatically.', 'qaiyo-testimonials' ); ?></span>
		</p>
		<p>
			<label for="qtd_limit"><strong><?php esc_html_e( 'Limit', 'qaiyo-testimonials' ); ?></strong></label><br />
			<input type="number" id="qtd_limit" name="qtd_limit" value="<?php echo esc_attr( $limit ); ?>" min="-1" max="100" />
			<span class="qt-hint"><?php esc_html_e( '-1 = no limit. V5 always caps at 29.', 'qaiyo-testimonials' ); ?></span>
		</p>
		<?php
	}

	public static function box_behavior( $post ) {
		$d = array(
			'rows'          => (int) self::get_meta( $post->ID, '_qtd_rows', 1 ),
			'columns'       => (int) self::get_meta( $post->ID, '_qtd_columns', 3 ),
			'direction'     => self::get_meta( $post->ID, '_qtd_direction', 'left' ),
			'alt_direction' => (int) self::get_meta( $post->ID, '_qtd_alt_direction', 0 ),
			'speed'         => (int) self::get_meta( $post->ID, '_qtd_speed', Qt_Settings::get( 'speed' ) ),
			'fade_edges'    => (int) self::get_meta( $post->ID, '_qtd_fade_edges', 1 ),
			'author_bg'     => self::get_meta( $post->ID, '_qtd_author_bg', '#1a1a1a' ),
			'author_color'  => self::get_meta( $post->ID, '_qtd_author_color', '#ffffff' ),
			'content_bg'    => self::get_meta( $post->ID, '_qtd_content_bg', '#ffffff' ),
			'content_color' => self::get_meta( $post->ID, '_qtd_content_color', '#1a1a1a' ),
			'v8_testimonial' => (int) self::get_meta( $post->ID, '_qtd_v8_testimonial', 0 ),
		);
		?>
		<div class="qt-behavior" data-version="<?php echo esc_attr( self::get_meta( $post->ID, '_qtd_version', 'v1' ) ); ?>">

			<div class="qt-only-v1" style="display:none;">
				<p>
					<strong><?php esc_html_e( 'Rows', 'qaiyo-testimonials' ); ?></strong>
					<label style="margin-left:14px;"><input type="radio" name="qtd_rows" value="1" <?php checked( $d['rows'], 1 ); ?> /> 1</label>
					<label style="margin-left:10px;"><input type="radio" name="qtd_rows" value="2" <?php checked( $d['rows'], 2 ); ?> /> 2</label>
				</p>
			</div>

			<div class="qt-only-v2" style="display:none;">
				<p>
					<strong><?php esc_html_e( 'Columns', 'qaiyo-testimonials' ); ?></strong>
					<label style="margin-left:14px;"><input type="radio" name="qtd_columns" value="2" <?php checked( $d['columns'], 2 ); ?> /> 2</label>
					<label style="margin-left:10px;"><input type="radio" name="qtd_columns" value="3" <?php checked( $d['columns'], 3 ); ?> /> 3</label>
				</p>
			</div>

			<div class="qt-only-v1 qt-only-v2 qt-only-v3" style="display:none;">
				<p>
					<strong><?php esc_html_e( 'Direction', 'qaiyo-testimonials' ); ?></strong>
					<span class="qt-dir-x">
						<label style="margin-left:14px;"><input type="radio" name="qtd_direction" value="left" <?php checked( $d['direction'], 'left' ); ?> /> <?php esc_html_e( 'Left', 'qaiyo-testimonials' ); ?></label>
						<label style="margin-left:10px;"><input type="radio" name="qtd_direction" value="right" <?php checked( $d['direction'], 'right' ); ?> /> <?php esc_html_e( 'Right', 'qaiyo-testimonials' ); ?></label>
					</span>
					<span class="qt-dir-y" style="display:none;">
						<label style="margin-left:14px;"><input type="radio" name="qtd_direction" value="up" <?php checked( $d['direction'], 'up' ); ?> /> <?php esc_html_e( 'Up', 'qaiyo-testimonials' ); ?></label>
						<label style="margin-left:10px;"><input type="radio" name="qtd_direction" value="down" <?php checked( $d['direction'], 'down' ); ?> /> <?php esc_html_e( 'Down', 'qaiyo-testimonials' ); ?></label>
					</span>
				</p>
			</div>

			<div class="qt-only-v1 qt-only-v2" style="display:none;">
				<p>
					<label><input type="checkbox" name="qtd_alt_direction" value="1" <?php checked( $d['alt_direction'], 1 ); ?> /> <?php esc_html_e( 'Alternate direction per row/column', 'qaiyo-testimonials' ); ?></label>
				</p>
			</div>

			<div class="qt-only-v1 qt-only-v2 qt-only-v3 qt-only-v5" style="display:none;">
				<p>
					<label for="qtd_speed"><strong><?php esc_html_e( 'Speed (sec for full loop)', 'qaiyo-testimonials' ); ?></strong></label><br />
					<input type="number" id="qtd_speed" name="qtd_speed" min="5" max="600" value="<?php echo esc_attr( $d['speed'] ); ?>" />
					<span class="qt-hint"><?php esc_html_e( 'Lower = faster.', 'qaiyo-testimonials' ); ?></span>
				</p>
			</div>

			<div class="qt-only-v1 qt-only-v2 qt-only-v3" style="display:none;">
				<p>
					<label><input type="checkbox" name="qtd_fade_edges" value="1" <?php checked( $d['fade_edges'], 1 ); ?> /> <?php esc_html_e( 'Fade edges', 'qaiyo-testimonials' ); ?></label>
				</p>
			</div>

			<p class="qt-only-v4" style="display:none;color:#787c82;font-size:13px;"><?php esc_html_e( 'V4 has no slider — boxes display in a static bento grid. Use the per-testimonial highlight toggle to mark one box.', 'qaiyo-testimonials' ); ?></p>

			<div class="qt-only-v6" style="display:none;">
				<p>
					<strong><?php esc_html_e( 'Columns', 'qaiyo-testimonials' ); ?></strong>
					<?php foreach ( array( 1, 2, 3, 4 ) as $c ) : ?>
						<label style="margin-left:10px;"><input type="radio" name="qtd_columns" value="<?php echo esc_attr( $c ); ?>" <?php checked( $d['columns'], $c ); ?> /> <?php echo esc_html( $c ); ?></label>
					<?php endforeach; ?>
				</p>
			</div>

			<p class="qt-only-v7" style="display:none;color:#787c82;font-size:13px;"><?php esc_html_e( 'V7 uses the per-testimonial background and text color from the "V4 Highlight" meta box on each Testimonial — set them there to give each card its own pastel color.', 'qaiyo-testimonials' ); ?></p>

			<div class="qt-only-v8" style="display:none;">
				<p>
					<strong><?php esc_html_e( 'Which testimonial to display', 'qaiyo-testimonials' ); ?></strong>
				</p>
				<p>
					<?php
					$all_testimonials = get_posts(
						array(
							'post_type'      => QT_POST_TYPE,
							'post_status'    => 'publish',
							'posts_per_page' => -1,
							'orderby'        => 'title',
							'order'          => 'ASC',
						)
					);
					?>
					<select name="qtd_v8_testimonial" style="min-width:320px;max-width:100%;">
						<option value="0"><?php esc_html_e( '— First in selected category —', 'qaiyo-testimonials' ); ?></option>
						<?php foreach ( $all_testimonials as $t ) :
							$pos = get_post_meta( $t->ID, '_qt_position', true );
							$co  = get_post_meta( $t->ID, '_qt_company', true );
							$meta_label = trim( implode( ', ', array_filter( array( $pos, $co ) ) ) );
							$label = get_the_title( $t ) . ( $meta_label ? ' — ' . $meta_label : '' );
							?>
							<option value="<?php echo esc_attr( $t->ID ); ?>" <?php selected( $d['v8_testimonial'], $t->ID ); ?>><?php echo esc_html( $label ); ?></option>
						<?php endforeach; ?>
					</select>
					<span class="qt-hint" style="display:block;"><?php esc_html_e( 'V8 is designed to feature a single testimonial. Pick which one — leave empty to use the first from the selected category.', 'qaiyo-testimonials' ); ?></span>
				</p>

				<p style="margin-top:18px;">
					<strong><?php esc_html_e( 'Description box colors (V8 left panel)', 'qaiyo-testimonials' ); ?></strong>
				</p>
				<p>
					<label for="qtd_content_bg"><?php esc_html_e( 'Description box background', 'qaiyo-testimonials' ); ?></label><br />
					<input type="color" id="qtd_content_bg" name="qtd_content_bg" value="<?php echo esc_attr( $d['content_bg'] ); ?>" />
				</p>
				<p>
					<label for="qtd_content_color"><?php esc_html_e( 'Description box text color', 'qaiyo-testimonials' ); ?></label><br />
					<input type="color" id="qtd_content_color" name="qtd_content_color" value="<?php echo esc_attr( $d['content_color'] ); ?>" />
				</p>
				<p style="margin-top:18px;">
					<strong><?php esc_html_e( 'Author box colors (V8 right panel)', 'qaiyo-testimonials' ); ?></strong>
				</p>
				<p>
					<label for="qtd_author_bg"><?php esc_html_e( 'Author box background', 'qaiyo-testimonials' ); ?></label><br />
					<input type="color" id="qtd_author_bg" name="qtd_author_bg" value="<?php echo esc_attr( $d['author_bg'] ); ?>" />
				</p>
				<p>
					<label for="qtd_author_color"><?php esc_html_e( 'Author box text color', 'qaiyo-testimonials' ); ?></label><br />
					<input type="color" id="qtd_author_color" name="qtd_author_color" value="<?php echo esc_attr( $d['author_color'] ); ?>" />
				</p>
				<p class="qt-hint"><?php esc_html_e( 'Right column ratio: photo gets 2/3, author panel gets 1/3 vertically.', 'qaiyo-testimonials' ); ?></p>
			</div>
		</div>
		<?php
	}

	public static function box_design( $post ) {
		$globals = Qt_Settings::get();
		$f = function( $key ) use ( $post, $globals ) {
			$val = get_post_meta( $post->ID, '_qtd_' . $key, true );
			return ( '' === $val || null === $val ) ? $globals[ $key ] : $val;
		};
		?>
		<p class="qt-hint"><?php esc_html_e( 'Leave a field empty to inherit the global Settings page value.', 'qaiyo-testimonials' ); ?></p>
		<table class="form-table">
			<tr>
				<th><label><?php esc_html_e( 'Card background', 'qaiyo-testimonials' ); ?></label></th>
				<td><input type="color" name="qtd_bg_color" value="<?php echo esc_attr( $f( 'bg_color' ) ); ?>" /></td>
				<th><label><?php esc_html_e( 'Quote mark color', 'qaiyo-testimonials' ); ?></label></th>
				<td><input type="color" name="qtd_quote_color" value="<?php echo esc_attr( $f( 'quote_color' ) ); ?>" /></td>
			</tr>
			<tr>
				<th><label><?php esc_html_e( 'Text color', 'qaiyo-testimonials' ); ?></label></th>
				<td><input type="color" name="qtd_text_color" value="<?php echo esc_attr( $f( 'text_color' ) ); ?>" /></td>
				<th><label><?php esc_html_e( 'Meta color', 'qaiyo-testimonials' ); ?></label></th>
				<td><input type="color" name="qtd_meta_color" value="<?php echo esc_attr( $f( 'meta_color' ) ); ?>" /></td>
			</tr>
			<tr>
				<th><label><?php esc_html_e( 'Name color', 'qaiyo-testimonials' ); ?></label></th>
				<td><input type="color" name="qtd_name_color" value="<?php echo esc_attr( $f( 'name_font_color' ) ); ?>" /></td>
				<th><label><?php esc_html_e( 'Border color', 'qaiyo-testimonials' ); ?></label></th>
				<td><input type="color" name="qtd_border_color" value="<?php echo esc_attr( $f( 'border_color' ) ); ?>" /></td>
			</tr>
			<tr>
				<th><label><?php esc_html_e( 'Border width (px)', 'qaiyo-testimonials' ); ?></label></th>
				<td><input type="number" min="0" max="10" name="qtd_border_width" value="<?php echo esc_attr( $f( 'border_width' ) ); ?>" /></td>
				<th><label><?php esc_html_e( 'Border radius (px)', 'qaiyo-testimonials' ); ?></label></th>
				<td><input type="number" min="0" max="40" name="qtd_border_radius" value="<?php echo esc_attr( $f( 'border_radius' ) ); ?>" /></td>
			</tr>
			<tr>
				<th><label><?php esc_html_e( 'Padding (px)', 'qaiyo-testimonials' ); ?></label></th>
				<td><input type="number" min="0" max="80" name="qtd_padding" value="<?php echo esc_attr( $f( 'padding' ) ); ?>" /></td>
				<th><label><?php esc_html_e( 'Gap (px)', 'qaiyo-testimonials' ); ?></label></th>
				<td><input type="number" min="0" max="80" name="qtd_gap" value="<?php echo esc_attr( $f( 'gap' ) ); ?>" /></td>
			</tr>
			<tr>
				<th><label><?php esc_html_e( 'Heading font size (px)', 'qaiyo-testimonials' ); ?></label></th>
				<td><input type="number" min="10" max="48" name="qtd_heading_size" value="<?php echo esc_attr( $f( 'heading_size' ) ); ?>" /></td>
				<th><label><?php esc_html_e( 'Description font size (px)', 'qaiyo-testimonials' ); ?></label></th>
				<td><input type="number" min="10" max="32" name="qtd_text_size" value="<?php echo esc_attr( $f( 'text_size' ) ); ?>" /></td>
			</tr>
		</table>
		<?php
	}

	public static function save( $post_id, $post ) {
		if ( ! isset( $_POST['qt_display_nonce'] ) ) {
			return;
		}
		$nonce = sanitize_text_field( wp_unslash( $_POST['qt_display_nonce'] ) );
		if ( ! wp_verify_nonce( $nonce, 'qt_display_save' ) ) {
			return;
		}
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		$text_fields = array(
			'version'   => array( 'enum', array( 'v1', 'v2', 'v3', 'v4', 'v5', 'v6', 'v7', 'v8' ), 'v1' ),
			'direction' => array( 'enum', array( 'left', 'right', 'up', 'down' ), 'left' ),
			'category'  => array( 'slug', null, '' ),
		);
		foreach ( $text_fields as $key => $rule ) {
			$raw = isset( $_POST[ 'qtd_' . $key ] ) ? sanitize_text_field( wp_unslash( $_POST[ 'qtd_' . $key ] ) ) : '';
			if ( 'enum' === $rule[0] ) {
				$val = in_array( $raw, $rule[1], true ) ? $raw : $rule[2];
			} elseif ( 'slug' === $rule[0] ) {
				$val = sanitize_title( $raw );
			} else {
				$val = $rule[2];
			}
			update_post_meta( $post_id, '_qtd_' . $key, $val );
		}

		$int_fields = array( 'quote_style' => 1, 'rows' => 1, 'columns' => 3, 'limit' => -1, 'speed' => 30, 'border_width' => 1, 'border_radius' => 12, 'padding' => 24, 'gap' => 20, 'heading_size' => 18, 'text_size' => 15, 'v8_testimonial' => 0 );
		foreach ( $int_fields as $key => $def ) {
			$raw = isset( $_POST[ 'qtd_' . $key ] ) ? intval( wp_unslash( $_POST[ 'qtd_' . $key ] ) ) : $def;
			if ( 'limit' === $key ) {
				$val = ( -1 === $raw ) ? -1 : max( -1, $raw );
			} elseif ( 'quote_style' === $key ) {
				$val = max( 1, min( 4, $raw ) );
			} else {
				$val = max( 0, $raw );
			}
			update_post_meta( $post_id, '_qtd_' . $key, $val );
		}

		$bool_fields = array( 'alt_direction', 'fade_edges' );
		foreach ( $bool_fields as $key ) {
			update_post_meta( $post_id, '_qtd_' . $key, isset( $_POST[ 'qtd_' . $key ] ) ? 1 : 0 );
		}

		$color_fields = array( 'bg_color', 'text_color', 'meta_color', 'quote_color', 'name_color', 'border_color', 'author_bg', 'author_color', 'content_bg', 'content_color' );
		foreach ( $color_fields as $key ) {
			$raw = isset( $_POST[ 'qtd_' . $key ] ) ? sanitize_hex_color( wp_unslash( $_POST[ 'qtd_' . $key ] ) ) : '';
			update_post_meta( $post_id, '_qtd_' . $key, $raw ? $raw : '' );
		}

		/**
		 * Action fired after a Display configuration is saved.
		 *
		 * Pro plugins can persist premium fields here (Pro-only version meta,
		 * advanced animation settings, conditional display rules, etc.) — the
		 * nonce + capability check have already passed.
		 *
		 * @param int     $post_id
		 * @param WP_Post $post
		 */
		do_action( 'qt_after_save_display', $post_id, $post );
	}

	/**
	 * Build a shortcode-atts array from a Display post.
	 *
	 * @param int $post_id Display post ID.
	 * @return array|false Returns false if invalid.
	 */
	/**
	 * Return all published Display configurations as id => title pairs.
	 * Used by the Pro add-on's Gutenberg / Elementor / Bricks widgets to
	 * populate a display picker dropdown.
	 *
	 * @return array<int,string>
	 */
	public static function get_all() {
		$posts = get_posts(
			array(
				'post_type'      => self::POST_TYPE,
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'orderby'        => 'title',
				'order'          => 'ASC',
			)
		);
		$out = array();
		foreach ( $posts as $p ) {
			$out[ $p->ID ] = get_the_title( $p );
		}
		return $out;
	}

	public static function to_atts( $post_id ) {
		$post = get_post( $post_id );
		if ( ! $post || self::POST_TYPE !== $post->post_type || 'publish' !== $post->post_status ) {
			return false;
		}
		$g    = Qt_Settings::get();
		$pick = function( $key, $def ) use ( $post_id ) {
			$v = get_post_meta( $post_id, '_qtd_' . $key, true );
			return ( '' === $v || null === $v ) ? $def : $v;
		};
		// V8 features a single testimonial — pass the selected ID through `ids` when set.
		$v8_id   = (int) $pick( 'v8_testimonial', 0 );
		$version = $pick( 'version', 'v1' );
		$ids_val = ( 'v8' === $version && $v8_id > 0 ) ? (string) $v8_id : '';
		return array(
			'version'       => $version,
			'quote_style'   => (int) $pick( 'quote_style', 1 ),
			'category'      => $pick( 'category', '' ),
			'ids'           => $ids_val,
			'limit'         => (int) $pick( 'limit', -1 ),
			'rows'          => (int) $pick( 'rows', 1 ),
			'columns'       => (int) $pick( 'columns', 3 ),
			'direction'     => $pick( 'direction', 'left' ),
			'alt_direction' => (int) $pick( 'alt_direction', 0 ),
			'speed'         => (int) $pick( 'speed', $g['speed'] ),
			'fade_edges'    => (int) $pick( 'fade_edges', $g['fade_edges'] ),
			'bg_color'      => $pick( 'bg_color', $g['bg_color'] ),
			'text_color'    => $pick( 'text_color', $g['text_color'] ),
			'meta_color'    => $pick( 'meta_color', $g['meta_color'] ),
			'quote_color'   => $pick( 'quote_color', $g['quote_color'] ),
			'name_color'    => $pick( 'name_color', $g['name_font_color'] ),
			'border_color'  => $pick( 'border_color', $g['border_color'] ),
			'border_width'  => (int) $pick( 'border_width', $g['border_width'] ),
			'border_radius' => (int) $pick( 'border_radius', $g['border_radius'] ),
			'padding'       => (int) $pick( 'padding', $g['padding'] ),
			'gap'           => (int) $pick( 'gap', $g['gap'] ),
			'heading_size'  => (int) $pick( 'heading_size', $g['heading_size'] ),
			'text_size'     => (int) $pick( 'text_size', $g['text_size'] ),
			'author_bg'     => $pick( 'author_bg', '#1a1a1a' ),
			'author_color'  => $pick( 'author_color', '#ffffff' ),
			'content_bg'    => $pick( 'content_bg', '' ),
			'content_color' => $pick( 'content_color', '' ),
		);
	}
}
