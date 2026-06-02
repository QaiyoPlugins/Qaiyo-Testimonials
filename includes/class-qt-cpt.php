<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Qt_Cpt {

	public static function init() {
		add_action( 'init', array( __CLASS__, 'register_post_type' ) );
		add_action( 'init', array( __CLASS__, 'register_taxonomy' ) );
		add_filter( 'manage_' . QT_POST_TYPE . '_posts_columns', array( __CLASS__, 'columns' ) );
		add_action( 'manage_' . QT_POST_TYPE . '_posts_custom_column', array( __CLASS__, 'column_content' ), 10, 2 );
	}

	public static function activate() {
		self::register_post_type();
		self::register_taxonomy();
		flush_rewrite_rules();
	}

	public static function register_post_type() {
		// Register our top-level menu slug with the brand menu, so the separator
		// can locate this plugin's group regardless of float-position quirks.
		if ( class_exists( 'Qt_Brand_Menu' ) ) {
			Qt_Brand_Menu::register_plugin_slug( 'edit.php?post_type=' . QT_POST_TYPE );
		}

		$labels = array(
			'name'               => __( 'Testimonials', 'qaiyo-testimonials' ),
			'singular_name'      => __( 'Testimonial', 'qaiyo-testimonials' ),
			'menu_name'          => __( 'Testimonials', 'qaiyo-testimonials' ),
			'add_new'            => __( 'Add New', 'qaiyo-testimonials' ),
			'add_new_item'       => __( 'Add New Testimonial', 'qaiyo-testimonials' ),
			'edit_item'          => __( 'Edit Testimonial', 'qaiyo-testimonials' ),
			'new_item'           => __( 'New Testimonial', 'qaiyo-testimonials' ),
			'view_item'          => __( 'View Testimonial', 'qaiyo-testimonials' ),
			'search_items'       => __( 'Search Testimonials', 'qaiyo-testimonials' ),
			'not_found'          => __( 'No testimonials found', 'qaiyo-testimonials' ),
			'not_found_in_trash' => __( 'No testimonials found in Trash', 'qaiyo-testimonials' ),
			'all_items'          => __( 'All Testimonials', 'qaiyo-testimonials' ),
		);

		register_post_type(
			QT_POST_TYPE,
			array(
				'labels'              => $labels,
				'public'              => false,
				'show_ui'             => true,
				'show_in_menu'        => true,
				'show_in_rest'        => true,
				'menu_icon'           => 'dashicons-format-quote',
				'menu_position'       => class_exists( 'Qt_Brand_Menu' ) ? Qt_Brand_Menu::plugin_position() : 25,
				'supports'            => array( 'title' ),
				'has_archive'         => false,
				'rewrite'             => false,
				'capability_type'     => 'post',
				'hierarchical'        => false,
				'exclude_from_search' => true,
			)
		);
	}

	public static function register_taxonomy() {
		$labels = array(
			'name'          => __( 'Categories', 'qaiyo-testimonials' ),
			'singular_name' => __( 'Category', 'qaiyo-testimonials' ),
			'search_items'  => __( 'Search Categories', 'qaiyo-testimonials' ),
			'all_items'     => __( 'All Categories', 'qaiyo-testimonials' ),
			'edit_item'     => __( 'Edit Category', 'qaiyo-testimonials' ),
			'update_item'   => __( 'Update Category', 'qaiyo-testimonials' ),
			'add_new_item'  => __( 'Add New Category', 'qaiyo-testimonials' ),
			'new_item_name' => __( 'New Category Name', 'qaiyo-testimonials' ),
			'menu_name'     => __( 'Categories', 'qaiyo-testimonials' ),
		);

		register_taxonomy(
			QT_TAXONOMY,
			QT_POST_TYPE,
			array(
				'labels'            => $labels,
				'public'            => false,
				'show_ui'           => true,
				'show_admin_column' => true,
				'show_in_rest'      => true,
				'hierarchical'      => true,
				'rewrite'           => false,
			)
		);
	}

	public static function columns( $cols ) {
		$new = array();
		foreach ( $cols as $key => $label ) {
			if ( 'title' === $key ) {
				$new['qt_photo'] = __( 'Photo', 'qaiyo-testimonials' );
			}
			$new[ $key ] = $label;
			if ( 'title' === $key ) {
				$new['qt_company'] = __( 'Company', 'qaiyo-testimonials' );
			}
		}
		return $new;
	}

	public static function column_content( $col, $post_id ) {
		if ( 'qt_photo' === $col ) {
			$photo_id = (int) get_post_meta( $post_id, '_qt_photo_id', true );
			if ( $photo_id ) {
				echo wp_get_attachment_image( $photo_id, array( 48, 48 ), false, array( 'style' => 'border-radius:50%;object-fit:cover;width:48px;height:48px;' ) );
			} else {
				echo '<span style="color:#787c82;">—</span>';
			}
		} elseif ( 'qt_company' === $col ) {
			$position = get_post_meta( $post_id, '_qt_position', true );
			$company  = get_post_meta( $post_id, '_qt_company', true );
			$parts    = array_filter( array( $position, $company ) );
			echo esc_html( implode( ', ', $parts ) );
		}
	}

	/**
	 * Programmatically create a testimonial. Used by the Pro add-on for the
	 * frontend submission form and for review imports (Google / Trustpilot / …).
	 *
	 * @param array $data {
	 *     @type string $name       Customer name (post title). Required.
	 *     @type string $quote      Testimonial body.
	 *     @type string $heading    Optional heading.
	 *     @type string $position   Job title.
	 *     @type string $company    Company name.
	 *     @type int    $photo_id   Attachment ID.
	 *     @type string $status     Post status (default 'draft').
	 *     @type string $category   Category slug to assign (optional).
	 *     @type array  $meta       Extra meta key/value pairs to persist (Pro fields).
	 * }
	 * @return int|WP_Error New post ID, or WP_Error on failure.
	 */
	public static function insert_testimonial( array $data ) {
		$name = isset( $data['name'] ) ? sanitize_text_field( $data['name'] ) : '';
		if ( '' === $name ) {
			return new WP_Error( 'qt_missing_name', __( 'A testimonial name is required.', 'qaiyo-testimonials' ) );
		}

		$status   = isset( $data['status'] ) ? sanitize_key( $data['status'] ) : 'draft';
		$allowed  = array( 'draft', 'pending', 'publish' );
		$status   = in_array( $status, $allowed, true ) ? $status : 'draft';

		$post_id = wp_insert_post(
			array(
				'post_type'   => QT_POST_TYPE,
				'post_status' => $status,
				'post_title'  => $name,
			),
			true
		);
		if ( is_wp_error( $post_id ) ) {
			return $post_id;
		}

		$map = array(
			'quote'    => '_qt_quote',
			'heading'  => '_qt_heading',
			'position' => '_qt_position',
			'company'  => '_qt_company',
		);
		foreach ( $map as $key => $meta_key ) {
			if ( isset( $data[ $key ] ) ) {
				$value = ( 'quote' === $key )
					? sanitize_textarea_field( $data[ $key ] )
					: sanitize_text_field( $data[ $key ] );
				update_post_meta( $post_id, $meta_key, $value );
			}
		}
		if ( ! empty( $data['photo_id'] ) ) {
			update_post_meta( $post_id, '_qt_photo_id', absint( $data['photo_id'] ) );
		}
		if ( ! empty( $data['category'] ) ) {
			wp_set_object_terms( $post_id, sanitize_title( $data['category'] ), QT_TAXONOMY );
		}
		if ( ! empty( $data['meta'] ) && is_array( $data['meta'] ) ) {
			foreach ( $data['meta'] as $mk => $mv ) {
				update_post_meta( $post_id, sanitize_key( $mk ), $mv );
			}
		}

		/**
		 * Fires after a testimonial is created programmatically (form / import).
		 *
		 * @param int   $post_id
		 * @param array $data
		 */
		do_action( 'qt_testimonial_inserted', $post_id, $data );

		return $post_id;
	}
}
