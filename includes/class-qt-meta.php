<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Qt_Meta {

	const META_KEYS = array(
		'_qt_position',
		'_qt_company',
		'_qt_heading',
		'_qt_quote',
		'_qt_photo_id',
		'_qt_highlight',
		'_qt_highlight_bg',
		'_qt_highlight_text',
	);

	public static function init() {
		add_action( 'add_meta_boxes', array( __CLASS__, 'add_meta_boxes' ) );
		add_action( 'save_post_' . QT_POST_TYPE, array( __CLASS__, 'save' ), 10, 2 );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue' ) );
	}

	public static function enqueue( $hook ) {
		global $post;
		if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) {
			return;
		}
		if ( ! $post || QT_POST_TYPE !== $post->post_type ) {
			return;
		}
		wp_enqueue_media();
		wp_enqueue_style( 'qt-admin', QT_URL . 'assets/css/admin.css', array(), qt_asset_ver( 'assets/css/admin.css' ) );
		wp_enqueue_script( 'qt-admin', QT_URL . 'assets/js/admin.js', array( 'jquery' ), qt_asset_ver( 'assets/js/admin.js' ), true );
		wp_localize_script(
			'qt-admin',
			'qtAdmin',
			array(
				'maxBytes' => QT_MAX_UPLOAD_BYTES,
				'i18n'     => array(
					'choose'      => esc_html__( 'Choose photo', 'qaiyo-testimonials' ),
					'use'         => esc_html__( 'Use this photo', 'qaiyo-testimonials' ),
					'remove'      => esc_html__( 'Remove photo', 'qaiyo-testimonials' ),
					'tooLarge'    => esc_html__( 'The selected image is larger than 1 MB. Please choose a smaller file.', 'qaiyo-testimonials' ),
					'invalidType' => esc_html__( 'Only JPG, PNG and WebP images are allowed.', 'qaiyo-testimonials' ),
				),
			)
		);
	}

	public static function add_meta_boxes() {
		add_meta_box( 'qt_details', __( 'Testimonial Details', 'qaiyo-testimonials' ), array( __CLASS__, 'render_details' ), QT_POST_TYPE, 'normal', 'high' );
		add_meta_box( 'qt_highlight', __( 'Card colors (V4 highlight, V7 background)', 'qaiyo-testimonials' ), array( __CLASS__, 'render_highlight' ), QT_POST_TYPE, 'side', 'default' );
		add_meta_box( 'qt_shortcode_help', __( 'Shortcode', 'qaiyo-testimonials' ), array( __CLASS__, 'render_shortcode_help' ), QT_POST_TYPE, 'side', 'low' );

		/**
		 * Action fired after Free meta boxes are registered.
		 *
		 * Pro plugins use this to add their own meta boxes (rating, video URL,
		 * Google Reviews source, conditional display rules, etc.).
		 */
		do_action( 'qt_register_meta_boxes', QT_POST_TYPE );
	}

	public static function render_details( $post ) {
		wp_nonce_field( 'qt_save_meta', 'qt_meta_nonce' );
		$position = get_post_meta( $post->ID, '_qt_position', true );
		$company  = get_post_meta( $post->ID, '_qt_company', true );
		$heading  = get_post_meta( $post->ID, '_qt_heading', true );
		$quote    = get_post_meta( $post->ID, '_qt_quote', true );
		$photo_id = (int) get_post_meta( $post->ID, '_qt_photo_id', true );
		$photo_src = $photo_id ? wp_get_attachment_image_url( $photo_id, 'thumbnail' ) : '';
		?>
		<div class="qt-meta-wrap">
			<p class="qt-hint"><?php esc_html_e( 'Customer name is taken from the title above.', 'qaiyo-testimonials' ); ?></p>

			<div class="qt-row">
				<label for="qt_heading"><?php esc_html_e( 'Heading (optional)', 'qaiyo-testimonials' ); ?></label>
				<input type="text" id="qt_heading" name="qt_heading" value="<?php echo esc_attr( $heading ); ?>" class="widefat" />
				<span class="qt-hint"><?php esc_html_e( 'Short headline shown above the quote (V1, V2, V3, V4). Leave empty to hide.', 'qaiyo-testimonials' ); ?></span>
			</div>

			<div class="qt-row">
				<label for="qt_quote"><?php esc_html_e( 'Quote / Description', 'qaiyo-testimonials' ); ?></label>
				<textarea id="qt_quote" name="qt_quote" rows="5" class="widefat"><?php echo esc_textarea( $quote ); ?></textarea>
			</div>

			<div class="qt-grid-2">
				<div class="qt-row">
					<label for="qt_position"><?php esc_html_e( 'Position', 'qaiyo-testimonials' ); ?></label>
					<input type="text" id="qt_position" name="qt_position" value="<?php echo esc_attr( $position ); ?>" class="widefat" />
				</div>
				<div class="qt-row">
					<label for="qt_company"><?php esc_html_e( 'Company', 'qaiyo-testimonials' ); ?></label>
					<input type="text" id="qt_company" name="qt_company" value="<?php echo esc_attr( $company ); ?>" class="widefat" />
				</div>
			</div>

			<div class="qt-row">
				<label><?php esc_html_e( 'Customer photo (JPG/PNG/WebP, max 1 MB)', 'qaiyo-testimonials' ); ?></label>
				<div class="qt-photo-picker">
					<div class="qt-photo-preview<?php echo $photo_src ? '' : ' qt-hidden'; ?>">
						<?php if ( $photo_src ) : ?>
							<img src="<?php echo esc_url( $photo_src ); ?>" alt="" />
						<?php endif; ?>
					</div>
					<input type="hidden" id="qt_photo_id" name="qt_photo_id" value="<?php echo esc_attr( $photo_id ); ?>" />
					<button type="button" class="button qt-photo-select"><?php esc_html_e( 'Select / Upload', 'qaiyo-testimonials' ); ?></button>
					<button type="button" class="button qt-photo-remove<?php echo $photo_id ? '' : ' qt-hidden'; ?>"><?php esc_html_e( 'Remove', 'qaiyo-testimonials' ); ?></button>
				</div>
			</div>
		</div>
		<?php
	}

	public static function render_highlight( $post ) {
		$highlight      = get_post_meta( $post->ID, '_qt_highlight', true );
		$highlight_bg   = get_post_meta( $post->ID, '_qt_highlight_bg', true );
		$highlight_text = get_post_meta( $post->ID, '_qt_highlight_text', true );
		if ( '' === $highlight_bg ) {
			$highlight_bg = '#111111';
		}
		if ( '' === $highlight_text ) {
			$highlight_text = '#ffffff';
		}
		?>
		<p>
			<label>
				<input type="checkbox" name="qt_highlight" value="1" <?php checked( $highlight, '1' ); ?> />
				<?php esc_html_e( 'Highlight this box in V4 (Bento) layouts', 'qaiyo-testimonials' ); ?>
			</label>
		</p>
		<p>
			<label for="qt_highlight_bg"><?php esc_html_e( 'Highlight background', 'qaiyo-testimonials' ); ?></label><br />
			<input type="color" id="qt_highlight_bg" name="qt_highlight_bg" value="<?php echo esc_attr( $highlight_bg ); ?>" />
		</p>
		<p>
			<label for="qt_highlight_text"><?php esc_html_e( 'Highlight text color', 'qaiyo-testimonials' ); ?></label><br />
			<input type="color" id="qt_highlight_text" name="qt_highlight_text" value="<?php echo esc_attr( $highlight_text ); ?>" />
		</p>
		<p class="qt-hint"><?php esc_html_e( 'Used by V4 (only when highlighted above) and V7 (always — each card uses its own background and text color).', 'qaiyo-testimonials' ); ?></p>
		<?php
	}

	public static function render_shortcode_help( $post ) {
		?>
		<p><?php esc_html_e( 'Display testimonials with:', 'qaiyo-testimonials' ); ?></p>
		<code style="display:block;background:#e8e8e8;padding:6px 8px;border-radius:3px;font-size:12px;">[qaiyo_testimonials version="v1"]</code>
		<p class="qt-hint" style="margin-top:8px;"><?php esc_html_e( 'See the Settings page for all attributes.', 'qaiyo-testimonials' ); ?></p>
		<?php
	}

	public static function save( $post_id, $post ) {
		if ( ! isset( $_POST['qt_meta_nonce'] ) ) {
			return;
		}
		$nonce = sanitize_text_field( wp_unslash( $_POST['qt_meta_nonce'] ) );
		if ( ! wp_verify_nonce( $nonce, 'qt_save_meta' ) ) {
			return;
		}
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		$heading  = isset( $_POST['qt_heading'] ) ? sanitize_text_field( wp_unslash( $_POST['qt_heading'] ) ) : '';
		$quote    = isset( $_POST['qt_quote'] ) ? sanitize_textarea_field( wp_unslash( $_POST['qt_quote'] ) ) : '';
		$position = isset( $_POST['qt_position'] ) ? sanitize_text_field( wp_unslash( $_POST['qt_position'] ) ) : '';
		$company  = isset( $_POST['qt_company'] ) ? sanitize_text_field( wp_unslash( $_POST['qt_company'] ) ) : '';
		$photo_id = isset( $_POST['qt_photo_id'] ) ? absint( wp_unslash( $_POST['qt_photo_id'] ) ) : 0;

		update_post_meta( $post_id, '_qt_heading', $heading );
		update_post_meta( $post_id, '_qt_quote', $quote );
		update_post_meta( $post_id, '_qt_position', $position );
		update_post_meta( $post_id, '_qt_company', $company );
		update_post_meta( $post_id, '_qt_photo_id', $photo_id );

		$highlight = isset( $_POST['qt_highlight'] ) ? '1' : '0';
		update_post_meta( $post_id, '_qt_highlight', $highlight );

		$hl_bg   = isset( $_POST['qt_highlight_bg'] ) ? sanitize_hex_color( wp_unslash( $_POST['qt_highlight_bg'] ) ) : '';
		$hl_text = isset( $_POST['qt_highlight_text'] ) ? sanitize_hex_color( wp_unslash( $_POST['qt_highlight_text'] ) ) : '';
		update_post_meta( $post_id, '_qt_highlight_bg', $hl_bg ? $hl_bg : '#111111' );
		update_post_meta( $post_id, '_qt_highlight_text', $hl_text ? $hl_text : '#ffffff' );

		/**
		 * Action fired after a testimonial's core meta is saved.
		 *
		 * Pro plugins can persist their own meta (rating, video URL, source, etc.)
		 * by hooking in here — the nonce + capability check have already passed.
		 *
		 * @param int     $post_id
		 * @param WP_Post $post
		 */
		do_action( 'qt_after_save_testimonial', $post_id, $post );
	}
}
