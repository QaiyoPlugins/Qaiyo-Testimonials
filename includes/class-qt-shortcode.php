<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Qt_Shortcode {

	public static function init() {
		add_shortcode( 'qaiyo_testimonials', array( __CLASS__, 'render' ) );
	}

	public static function render( $atts ) {
		$defaults = Qt_Settings::get();

		$base = array(
			'display'       => 0,
			'version'       => 'v1',
			'quote_style'   => 1,
			'category'      => '',
			'ids'           => '',
			'limit'         => -1,
			'rows'          => 1,
			'columns'       => 3,
			'direction'     => 'left',
			'alt_direction' => 0,
			'speed'         => $defaults['speed'],
			'fade_edges'    => $defaults['fade_edges'],
			'bg_color'      => $defaults['bg_color'],
			'text_color'    => $defaults['text_color'],
			'meta_color'    => $defaults['meta_color'],
			'quote_color'   => $defaults['quote_color'],
			'name_color'    => $defaults['name_font_color'],
			'border_color'  => $defaults['border_color'],
			'border_width'  => $defaults['border_width'],
			'border_radius' => $defaults['border_radius'],
			'padding'       => $defaults['padding'],
			'gap'           => $defaults['gap'],
			'heading_size'  => $defaults['heading_size'],
			'text_size'     => $defaults['text_size'],
			// V8-specific: separate colors for the LEFT description box and the RIGHT author box.
			'content_bg'    => '',  // empty → falls back to bg_color
			'content_color' => '',  // empty → falls back to text_color
			'author_bg'     => '#1a1a1a',
			'author_color'  => '#ffffff',
		);

		/**
		 * Filter the default shortcode attributes before user overrides are merged.
		 *
		 * Pro plugins can use this to inject new defaults for premium features.
		 *
		 * @param array $base Default attribute values.
		 */
		$base = apply_filters( 'qt_shortcode_defaults', $base );

		// Resolve display="ID" first — it provides the base config for that instance.
		$user_atts  = is_array( $atts ) ? $atts : array();
		$display_id = isset( $user_atts['display'] ) ? absint( $user_atts['display'] ) : 0;
		if ( $display_id && class_exists( 'Qt_Display' ) ) {
			$display_atts = Qt_Display::to_atts( $display_id );
			if ( false !== $display_atts ) {
				$base = array_merge( $base, $display_atts );
			}
		}

		$atts = shortcode_atts( $base, $user_atts, 'qaiyo_testimonials' );

		/**
		 * Filter the list of supported layout version slugs.
		 *
		 * Pro plugins can append v9, v10, v11... here and provide rendering
		 * through the `qt_pre_render_html` filter.
		 *
		 * @param string[] $versions
		 */
		$supported_versions  = apply_filters( 'qt_supported_versions', array( 'v1', 'v2', 'v3', 'v4', 'v5', 'v6' ) );
		$version             = in_array( $atts['version'], $supported_versions, true ) ? $atts['version'] : 'v1';
		$atts['version']     = $version;
		$qs                  = (int) $atts['quote_style'];
		/**
		 * Filter the list of valid quote-mark style numbers.
		 *
		 * @param int[] $styles
		 */
		$valid_quote_styles  = apply_filters( 'qt_quote_styles', array( 1, 2, 3, 4 ) );
		$atts['quote_style'] = in_array( $qs, $valid_quote_styles, true ) ? $qs : 1;

		/**
		 * Filter the final resolved shortcode attributes before rendering.
		 *
		 * @param array $atts      Final attribute set.
		 * @param array $user_atts Attributes the user explicitly passed.
		 */
		$atts = apply_filters( 'qt_shortcode_atts', $atts, $user_atts );

		$items = self::query_items( $atts );
		/**
		 * Filter the items collection (after the DB query, before render).
		 *
		 * @param array $items
		 * @param array $atts
		 */
		$items = apply_filters( 'qt_query_items', $items, $atts );

		if ( empty( $items ) ) {
			return '';
		}

		Qt_Assets::enqueue();

		/**
		 * Short-circuit the renderer. If a filter returns a non-null string,
		 * that string is used as the final HTML — the Free renderer is skipped.
		 *
		 * Pro plugins implementing premium layouts (v9, v10, …) use this hook.
		 *
		 * @param string|null $html    Pre-rendered HTML, or null to fall through.
		 * @param string      $version Layout version slug.
		 * @param array       $items   Testimonial items.
		 * @param array       $atts    Shortcode attributes.
		 */
		$pre_html = apply_filters( 'qt_pre_render_html', null, $version, $items, $atts );
		if ( null !== $pre_html && is_string( $pre_html ) ) {
			$json_ld = self::schema_jsonld( $items );
			$html    = $json_ld . $pre_html;
			$html    = (string) apply_filters( 'qt_render_html', $html, $version, $atts, $items );
			do_action( 'qt_after_render', $version, $atts, $items );
			return $html;
		}

		$method = 'render_' . $version;
		ob_start();
		echo self::schema_jsonld( $items ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		if ( is_callable( array( __CLASS__, $method ) ) ) {
			self::$method( $items, $atts );
		}
		$html = ob_get_clean();

		/**
		 * Filter the final rendered HTML (including the JSON-LD).
		 *
		 * @param string $html
		 * @param string $version
		 * @param array  $atts
		 * @param array  $items
		 */
		$html = (string) apply_filters( 'qt_render_html', $html, $version, $atts, $items );

		/**
		 * Action fired after every shortcode render — useful for analytics/tracking.
		 *
		 * @param string $version
		 * @param array  $atts
		 * @param array  $items
		 */
		do_action( 'qt_after_render', $version, $atts, $items );

		return $html;
	}

	/**
	 * Build Schema.org ItemList of Reviews as JSON-LD for SEO / AI search engines.
	 */
	private static function schema_jsonld( $items ) {
		$list = array();
		$position = 1;
		foreach ( $items as $it ) {
			$body = trim( (string) $it['quote'] );
			if ( '' === $body ) {
				continue;
			}
			$review = array(
				'@type'         => 'Review',
				'position'      => $position++,
				'reviewBody'    => $body,
				'author'        => array(
					'@type' => 'Person',
					'name'  => (string) $it['name'],
				),
			);
			if ( ! empty( $it['heading'] ) ) {
				$review['name'] = (string) $it['heading'];
			}
			if ( ! empty( $it['position'] ) ) {
				$review['author']['jobTitle'] = (string) $it['position'];
			}
			if ( ! empty( $it['company'] ) ) {
				$review['author']['worksFor'] = array(
					'@type' => 'Organization',
					'name'  => (string) $it['company'],
				);
			}
			$list[] = $review;
		}
		if ( empty( $list ) ) {
			return '';
		}
		$ld = array(
			'@context'        => 'https://schema.org',
			'@type'           => 'ItemList',
			'itemListElement' => $list,
		);
		/**
		 * Filter the Schema.org JSON-LD payload.
		 *
		 * Pro plugins can attach an AggregateRating, additional fields,
		 * or convert to a different schema type (e.g. Product with reviews).
		 *
		 * @param array $ld
		 * @param array $items
		 */
		$ld = apply_filters( 'qt_schema_jsonld', $ld, $items );
		return '<script type="application/ld+json">' . wp_json_encode( $ld, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>';
	}

	private static function query_items( $atts ) {
		$args = array(
			'post_type'      => QT_POST_TYPE,
			'post_status'    => 'publish',
			'posts_per_page' => (int) $atts['limit'] !== 0 ? (int) $atts['limit'] : -1,
			'orderby'        => 'menu_order date',
			'order'          => 'ASC',
		);

		if ( ! empty( $atts['ids'] ) ) {
			$ids = array_filter( array_map( 'absint', explode( ',', $atts['ids'] ) ) );
			if ( ! empty( $ids ) ) {
				$args['post__in'] = $ids;
				$args['orderby']  = 'post__in';
			}
		}

		if ( ! empty( $atts['category'] ) ) {
			$args['tax_query'] = array(
				array(
					'taxonomy' => QT_TAXONOMY,
					'field'    => 'slug',
					'terms'    => array_map( 'sanitize_title', explode( ',', $atts['category'] ) ),
				),
			);
		}

		/**
		 * Filter the WP_Query args used to fetch testimonials.
		 *
		 * Pro plugins can add custom orderby (e.g. by rating), meta_query
		 * (e.g. only items with stars), or other filtering here.
		 *
		 * @param array $args
		 * @param array $atts
		 */
		$args  = apply_filters( 'qt_query_args', $args, $atts );
		$posts = get_posts( $args );
		$items = array();
		foreach ( $posts as $p ) {
			$photo_id = (int) get_post_meta( $p->ID, '_qt_photo_id', true );
			$item = array(
				'id'             => $p->ID,
				'name'           => get_the_title( $p ),
				'heading'        => get_post_meta( $p->ID, '_qt_heading', true ),
				'quote'          => get_post_meta( $p->ID, '_qt_quote', true ),
				'position'       => get_post_meta( $p->ID, '_qt_position', true ),
				'company'        => get_post_meta( $p->ID, '_qt_company', true ),
				'photo_id'       => $photo_id,
				'photo_url'      => $photo_id ? wp_get_attachment_image_url( $photo_id, 'medium' ) : '',
				'photo_url_large' => $photo_id ? ( wp_get_attachment_image_url( $photo_id, 'large' ) ?: wp_get_attachment_image_url( $photo_id, 'full' ) ) : '',
				'photo_thumb'    => $photo_id ? wp_get_attachment_image_url( $photo_id, 'thumbnail' ) : '',
				'highlight'      => '1' === get_post_meta( $p->ID, '_qt_highlight', true ),
				'highlight_bg'   => get_post_meta( $p->ID, '_qt_highlight_bg', true ),
				'highlight_text' => get_post_meta( $p->ID, '_qt_highlight_text', true ),
			);
			/**
			 * Filter a single testimonial item after its meta is loaded.
			 *
			 * Pro plugins can attach rating, video_url, source (google/trustpilot),
			 * custom fields, etc.
			 *
			 * @param array   $item Item data.
			 * @param WP_Post $p    Post object.
			 */
			$items[] = apply_filters( 'qt_item_data', $item, $p );
		}
		return $items;
	}

	/**
	 * Build the CSS custom-property string for a layout root element.
	 * Public so Pro layouts (v9+) can reuse the same design tokens.
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string
	 */
	public static function style_vars( $atts ) {
		$content_bg    = ( ! empty( $atts['content_bg'] ) )    ? $atts['content_bg']    : $atts['bg_color'];
		$content_color = ( ! empty( $atts['content_color'] ) ) ? $atts['content_color'] : $atts['text_color'];
		return sprintf(
			'--qt-bg:%s;--qt-text:%s;--qt-meta:%s;--qt-quote:%s;--qt-name:%s;--qt-border:%s;--qt-border-w:%dpx;--qt-radius:%dpx;--qt-pad:%dpx;--qt-gap:%dpx;--qt-heading-size:%dpx;--qt-text-size:%dpx;--qt-content-bg:%s;--qt-content-color:%s;--qt-author-bg:%s;--qt-author-color:%s;',
			esc_attr( $atts['bg_color'] ),
			esc_attr( $atts['text_color'] ),
			esc_attr( $atts['meta_color'] ),
			esc_attr( $atts['quote_color'] ),
			esc_attr( $atts['name_color'] ),
			esc_attr( $atts['border_color'] ),
			(int) $atts['border_width'],
			(int) $atts['border_radius'],
			(int) $atts['padding'],
			(int) $atts['gap'],
			(int) $atts['heading_size'],
			(int) $atts['text_size'],
			esc_attr( $content_bg ),
			esc_attr( $content_color ),
			esc_attr( isset( $atts['author_bg'] ) ? $atts['author_bg'] : '#1a1a1a' ),
			esc_attr( isset( $atts['author_color'] ) ? $atts['author_color'] : '#ffffff' )
		);
	}

	/** Public unique-ID generator so Pro layouts can produce collision-free root IDs. */
	public static function uid() {
		static $n = 0;
		return 'qt-' . ++$n . '-' . wp_rand( 1000, 9999 );
	}

	private static function quote_mark( $style = 1 ) {
		return Qt_Svg::quote_mark( $style );
	}

	/**
	 * Render the customer figcaption (photo + name + position/company) with
	 * full Schema.org Person microdata. Public so Pro layouts reuse it.
	 *
	 * @param array  $item   Item data.
	 * @param string $layout Layout modifier class suffix.
	 */
	public static function customer_block( $item, $layout = 'horizontal' ) {
		$position = $item['position'];
		$company  = $item['company'];
		?>
		<figcaption class="qt-customer qt-customer-<?php echo esc_attr( $layout ); ?>" itemprop="author" itemscope itemtype="https://schema.org/Person">
			<?php if ( $item['photo_url'] ) : ?>
				<div class="qt-photo"><img src="<?php echo esc_url( $item['photo_url'] ); ?>" alt="<?php echo esc_attr( $item['name'] ); ?>" loading="lazy" itemprop="image" /></div>
			<?php endif; ?>
			<div class="qt-customer-text">
				<div class="qt-name" itemprop="name"><?php echo esc_html( $item['name'] ); ?></div>
				<?php if ( $position || $company ) : ?>
					<div class="qt-meta">
						<?php if ( $position ) : ?>
							<span itemprop="jobTitle"><?php echo esc_html( $position ); ?></span><?php endif; ?>
						<?php if ( $position && $company ) : ?>, <?php endif; ?>
						<?php if ( $company ) : ?>
							<span itemprop="worksFor" itemscope itemtype="https://schema.org/Organization"><span itemprop="name"><?php echo esc_html( $company ); ?></span></span>
						<?php endif; ?>
					</div>
				<?php endif; ?>
			</div>
		</figcaption>
		<?php
	}

	/**
	 * Render a single standard card (quote mark, heading, quote, customer).
	 * Public so Pro layouts can reuse the canonical card markup.
	 *
	 * The `qt_card_html` filter lets extensions inject per-card markup into
	 * EVERY layout (e.g. star rating, video play badge, review source icon)
	 * without having to re-implement each layout.
	 *
	 * @param array $item Item data.
	 * @param array $atts Shortcode attributes.
	 */
	public static function render_card( $item, $atts ) {
		$quote_style = isset( $atts['quote_style'] ) ? (int) $atts['quote_style'] : 1;
		ob_start();
		?>
		<figure class="qt-card-inner" itemscope itemtype="https://schema.org/Review">
			<?php echo self::quote_mark( $quote_style ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			<?php if ( ! empty( $item['heading'] ) ) : ?>
				<h3 class="qt-heading" itemprop="name"><?php echo esc_html( $item['heading'] ); ?></h3>
			<?php endif; ?>
			<?php if ( ! empty( $item['quote'] ) ) : ?>
				<blockquote class="qt-quote-text" itemprop="reviewBody"><?php echo esc_html( $item['quote'] ); ?></blockquote>
			<?php endif; ?>
			<?php self::customer_block( $item, 'horizontal' ); ?>
		</figure>
		<?php
		$html = ob_get_clean();
		/**
		 * Filter the inner HTML of a single testimonial card.
		 *
		 * @param string $html The card-inner markup.
		 * @param array  $item Item data (id, name, quote, rating, video_url, …).
		 * @param array  $atts Shortcode attributes.
		 */
		echo apply_filters( 'qt_card_html', $html, $item, $atts ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- composed of escaped parts + trusted filter output.
	}

	public static function render_v1( $items, $atts ) {
		$uid       = self::uid();
		$rows      = (int) $atts['rows'] === 2 ? 2 : 1;
		$direction = in_array( $atts['direction'], array( 'left', 'right' ), true ) ? $atts['direction'] : 'left';
		$alt       = (int) $atts['alt_direction'] ? 1 : 0;
		$speed     = max( 5, (int) $atts['speed'] );
		$fade      = (int) $atts['fade_edges'] ? 'on' : 'off';
		$style     = self::style_vars( $atts );

		$rows_items = array();
		if ( 1 === $rows ) {
			$rows_items[] = $items;
		} else {
			$rows_items[0] = array();
			$rows_items[1] = array();
			foreach ( $items as $i => $it ) {
				$rows_items[ $i % 2 ][] = $it;
			}
		}
		?>
		<div class="qt qt-v1" id="<?php echo esc_attr( $uid ); ?>" data-fade="<?php echo esc_attr( $fade ); ?>" style="<?php echo esc_attr( $style ); ?>">
			<?php foreach ( $rows_items as $rindex => $ritems ) :
				$dir = $direction;
				if ( $alt && $rindex % 2 === 1 ) {
					$dir = ( 'left' === $direction ) ? 'right' : 'left';
				}
				?>
				<div class="qt-marquee" data-direction="<?php echo esc_attr( $dir ); ?>" data-speed="<?php echo esc_attr( $speed ); ?>" data-axis="x">
					<div class="qt-track">
						<?php
						// Duplicate items for seamless loop.
						foreach ( array( 0, 1 ) as $loop ) :
							foreach ( $ritems as $item ) :
								?>
								<div class="qt-card" aria-hidden="<?php echo 0 === $loop ? 'false' : 'true'; ?>">
									<?php self::render_card( $item, $atts ); ?>
								</div>
								<?php
							endforeach;
						endforeach;
						?>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
		<?php
	}

	public static function render_v2( $items, $atts ) {
		$uid       = self::uid();
		$columns   = (int) $atts['columns'] === 2 ? 2 : 3;
		$direction = in_array( $atts['direction'], array( 'up', 'down' ), true ) ? $atts['direction'] : 'up';
		$alt       = (int) $atts['alt_direction'] ? 1 : 0;
		$speed     = max( 5, (int) $atts['speed'] );
		$fade      = (int) $atts['fade_edges'] ? 'on' : 'off';
		$style     = self::style_vars( $atts );

		$cols = array_fill( 0, $columns, array() );
		foreach ( $items as $i => $it ) {
			$cols[ $i % $columns ][] = $it;
		}
		?>
		<div class="qt qt-v2" id="<?php echo esc_attr( $uid ); ?>" data-fade="<?php echo esc_attr( $fade ); ?>" style="<?php echo esc_attr( $style ); ?>">
			<div class="qt-cols qt-cols-<?php echo esc_attr( $columns ); ?>">
				<?php foreach ( $cols as $cindex => $citems ) :
					$dir = $direction;
					if ( $alt && $cindex % 2 === 1 ) {
						$dir = ( 'up' === $direction ) ? 'down' : 'up';
					}
					?>
					<div class="qt-marquee" data-direction="<?php echo esc_attr( $dir ); ?>" data-speed="<?php echo esc_attr( $speed ); ?>" data-axis="y">
						<div class="qt-track">
							<?php
							foreach ( array( 0, 1 ) as $loop ) :
								foreach ( $citems as $item ) :
									?>
									<div class="qt-card" aria-hidden="<?php echo 0 === $loop ? 'false' : 'true'; ?>">
										<?php self::render_card( $item, $atts ); ?>
									</div>
									<?php
								endforeach;
							endforeach;
							?>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
		<?php
	}

	public static function render_v3( $items, $atts ) {
		$uid   = self::uid();
		$speed = max( 5, (int) $atts['speed'] );
		$fade  = (int) $atts['fade_edges'] ? 'on' : 'off';
		$style = self::style_vars( $atts );
		?>
		<div class="qt qt-v3" id="<?php echo esc_attr( $uid ); ?>" data-fade="<?php echo esc_attr( $fade ); ?>" style="<?php echo esc_attr( $style ); ?>">
			<div class="qt-marquee" data-direction="left" data-speed="<?php echo esc_attr( $speed ); ?>" data-axis="x">
				<div class="qt-track">
					<?php
					foreach ( array( 0, 1 ) as $loop ) :
						foreach ( $items as $item ) :
							?>
							<div class="qt-card qt-card-v3" aria-hidden="<?php echo 0 === $loop ? 'false' : 'true'; ?>">
								<figure class="qt-card-inner" itemscope itemtype="https://schema.org/Review">
									<?php if ( $item['photo_url'] ) : ?>
										<div class="qt-photo"><img src="<?php echo esc_url( $item['photo_url'] ); ?>" alt="<?php echo esc_attr( $item['name'] ); ?>" loading="lazy" /></div>
									<?php endif; ?>
									<?php if ( ! empty( $item['heading'] ) ) : ?>
										<h3 class="qt-heading" itemprop="name"><?php echo esc_html( $item['heading'] ); ?></h3>
									<?php endif; ?>
									<?php if ( ! empty( $item['quote'] ) ) : ?>
										<blockquote class="qt-quote-text" itemprop="reviewBody"><?php echo esc_html( $item['quote'] ); ?></blockquote>
									<?php endif; ?>
									<figcaption class="qt-customer-text qt-customer-text-block" itemprop="author" itemscope itemtype="https://schema.org/Person">
										<div class="qt-name" itemprop="name"><?php echo esc_html( $item['name'] ); ?></div>
										<?php
										$pos = $item['position'];
										$co  = $item['company'];
										if ( $pos || $co ) :
											?>
											<div class="qt-meta">
												<?php if ( $pos ) : ?><span itemprop="jobTitle"><?php echo esc_html( $pos ); ?></span><?php endif; ?>
												<?php if ( $pos && $co ) : ?>, <?php endif; ?>
												<?php if ( $co ) : ?><span itemprop="worksFor" itemscope itemtype="https://schema.org/Organization"><span itemprop="name"><?php echo esc_html( $co ); ?></span></span><?php endif; ?>
											</div>
										<?php endif; ?>
									</figcaption>
								</figure>
							</div>
							<?php
						endforeach;
					endforeach;
					?>
				</div>
			</div>
		</div>
		<?php
	}

	public static function render_v4( $items, $atts ) {
		$uid   = self::uid();
		$style = self::style_vars( $atts );
		$count = count( $items );
		?>
		<div class="qt qt-v4" id="<?php echo esc_attr( $uid ); ?>" data-count="<?php echo esc_attr( $count ); ?>" style="<?php echo esc_attr( $style ); ?>">
			<div class="qt-bento qt-bento-<?php echo esc_attr( min( $count, 8 ) ); ?>">
				<?php
				foreach ( $items as $i => $item ) :
					$is_hl = ! empty( $item['highlight'] );
					$inline = '';
					if ( $is_hl ) {
						$inline = sprintf(
							'background:%s;color:%s;',
							esc_attr( $item['highlight_bg'] ? $item['highlight_bg'] : '#111' ),
							esc_attr( $item['highlight_text'] ? $item['highlight_text'] : '#fff' )
						);
					}
					?>
					<div class="qt-card qt-card-v4 <?php echo $is_hl ? 'qt-highlight' : ''; ?>" style="<?php echo esc_attr( $inline ); ?>">
						<?php self::render_card( $item, $atts ); ?>
					</div>
					<?php
				endforeach;
				?>
			</div>
		</div>
		<?php
	}

	public static function render_v5( $items, $atts ) {
		$uid   = self::uid();
		$speed = max( 5, (int) $atts['speed'] );
		$items = array_slice( $items, 0, 29 );
		if ( empty( $items ) ) {
			return;
		}
		?>
		<div class="qt qt-v5" id="<?php echo esc_attr( $uid ); ?>" data-speed="<?php echo esc_attr( $speed ); ?>">
			<div class="qt-v5-avatars" role="tablist">
				<?php foreach ( $items as $i => $item ) : ?>
					<button type="button" class="qt-v5-avatar <?php echo 0 === $i ? 'is-active' : ''; ?>" data-index="<?php echo esc_attr( $i ); ?>" role="tab" aria-selected="<?php echo 0 === $i ? 'true' : 'false'; ?>" aria-label="<?php echo esc_attr( $item['name'] ); ?>">
						<?php if ( $item['photo_url'] ) : ?>
							<img src="<?php echo esc_url( $item['photo_url'] ); ?>" alt="<?php echo esc_attr( $item['name'] ); ?>" loading="lazy" />
						<?php else : ?>
							<span class="qt-v5-initial"><?php echo esc_html( mb_substr( $item['name'], 0, 1 ) ); ?></span>
						<?php endif; ?>
					</button>
				<?php endforeach; ?>
			</div>
			<div class="qt-v5-panels">
				<?php foreach ( $items as $i => $item ) : ?>
					<div class="qt-v5-panel <?php echo 0 === $i ? 'is-active' : ''; ?>" data-index="<?php echo esc_attr( $i ); ?>" role="tabpanel" itemscope itemtype="https://schema.org/Review">
						<div class="qt-customer-text-block" itemprop="author" itemscope itemtype="https://schema.org/Person">
							<div class="qt-name qt-name-large" itemprop="name"><?php echo esc_html( $item['name'] ); ?></div>
							<?php
							$pos = $item['position'];
							$co  = $item['company'];
							if ( $pos || $co ) :
								?>
								<div class="qt-meta qt-meta-center">
									<?php if ( $pos ) : ?><span itemprop="jobTitle"><?php echo esc_html( $pos ); ?></span><?php endif; ?>
									<?php if ( $pos && $co ) : ?>, <?php endif; ?>
									<?php if ( $co ) : ?><span itemprop="worksFor" itemscope itemtype="https://schema.org/Organization"><span itemprop="name"><?php echo esc_html( $co ); ?></span></span><?php endif; ?>
								</div>
							<?php endif; ?>
						</div>
						<?php if ( ! empty( $item['quote'] ) ) : ?>
							<blockquote class="qt-quote-text qt-quote-large" itemprop="reviewBody"><?php echo esc_html( $item['quote'] ); ?></blockquote>
						<?php endif; ?>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
		<?php
	}

	/** V6 — photo hero on top, dark content section below. Grid of cards. */
	public static function render_v6( $items, $atts ) {
		$uid     = self::uid();
		$style   = self::style_vars( $atts );
		$columns = max( 1, min( 4, (int) $atts['columns'] ) );
		if ( ! $columns ) {
			$columns = 3;
		}
		$quote_style = (int) $atts['quote_style'];
		?>
		<div class="qt qt-v6" id="<?php echo esc_attr( $uid ); ?>" style="<?php echo esc_attr( $style ); ?>">
			<div class="qt-v6-grid qt-v6-grid-<?php echo esc_attr( $columns ); ?>">
				<?php foreach ( $items as $item ) : ?>
					<article class="qt-card-v6" itemscope itemtype="https://schema.org/Review">
						<?php
						$hero_url = ! empty( $item['photo_url_large'] ) ? $item['photo_url_large'] : $item['photo_url'];
						if ( $hero_url ) : ?>
							<div class="qt-v6-photo"><img src="<?php echo esc_url( $hero_url ); ?>" alt="<?php echo esc_attr( $item['name'] ); ?>" loading="lazy" /></div>
						<?php else : ?>
							<div class="qt-v6-photo qt-v6-photo-empty" aria-hidden="true"></div>
						<?php endif; ?>
						<div class="qt-v6-content">
							<span class="qt-v6-quote-circle" aria-hidden="true"><?php echo Qt_Svg::quote_mark( $quote_style, 'qt-quote-mark qt-v6-quote' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
							<?php if ( ! empty( $item['heading'] ) ) : ?>
								<h3 class="qt-heading" itemprop="name"><?php echo esc_html( $item['heading'] ); ?></h3>
							<?php endif; ?>
							<?php if ( ! empty( $item['quote'] ) ) : ?>
								<blockquote class="qt-quote-text" itemprop="reviewBody"><?php echo esc_html( $item['quote'] ); ?></blockquote>
							<?php endif; ?>
							<div class="qt-v6-author" itemprop="author" itemscope itemtype="https://schema.org/Person">
								<div class="qt-name" itemprop="name"><?php echo esc_html( $item['name'] ); ?></div>
								<?php
								$pos = $item['position'];
								$co  = $item['company'];
								if ( $pos || $co ) :
									?>
									<div class="qt-meta">
										<?php if ( $pos ) : ?><span itemprop="jobTitle"><?php echo esc_html( $pos ); ?></span><?php endif; ?>
										<?php if ( $pos && $co ) : ?>, <?php endif; ?>
										<?php if ( $co ) : ?><span itemprop="worksFor" itemscope itemtype="https://schema.org/Organization"><span itemprop="name"><?php echo esc_html( $co ); ?></span></span><?php endif; ?>
									</div>
								<?php endif; ?>
							</div>
						</div>
					</article>
				<?php endforeach; ?>
			</div>
		</div>
		<?php
	}

	/** V7 — pastel colored, slightly rotated cards in a flex row. */
	public static function render_v7( $items, $atts ) {
		$uid         = self::uid();
		$style       = self::style_vars( $atts );
		$quote_style = (int) $atts['quote_style'];
		?>
		<div class="qt qt-v7" id="<?php echo esc_attr( $uid ); ?>" style="<?php echo esc_attr( $style ); ?>">
			<div class="qt-v7-stack">
				<?php
				foreach ( $items as $item ) :
					$card_bg = ! empty( $item['highlight_bg'] ) ? $item['highlight_bg'] : '#fce4d9';
					$card_tx = ! empty( $item['highlight_text'] ) ? $item['highlight_text'] : '#1d2327';
					$inline  = sprintf( 'background:%s;color:%s;', esc_attr( $card_bg ), esc_attr( $card_tx ) );
					?>
					<article class="qt-card-v7" style="<?php echo esc_attr( $inline ); ?>" itemscope itemtype="https://schema.org/Review">
						<?php echo Qt_Svg::quote_mark( $quote_style, 'qt-quote-mark qt-v7-quote' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						<?php if ( ! empty( $item['heading'] ) ) : ?>
							<h3 class="qt-heading" itemprop="name"><?php echo esc_html( $item['heading'] ); ?></h3>
						<?php endif; ?>
						<?php if ( ! empty( $item['quote'] ) ) : ?>
							<blockquote class="qt-quote-text" itemprop="reviewBody"><?php echo esc_html( $item['quote'] ); ?></blockquote>
						<?php endif; ?>
						<div class="qt-v7-author" itemprop="author" itemscope itemtype="https://schema.org/Person">
							<?php if ( $item['photo_url'] ) : ?>
								<div class="qt-photo"><img src="<?php echo esc_url( $item['photo_url'] ); ?>" alt="<?php echo esc_attr( $item['name'] ); ?>" loading="lazy" itemprop="image" /></div>
							<?php endif; ?>
							<div class="qt-v7-author-text">
								<div class="qt-name" itemprop="name"><?php echo esc_html( $item['name'] ); ?></div>
								<?php
								$pos = $item['position'];
								$co  = $item['company'];
								if ( $pos || $co ) :
									?>
									<div class="qt-meta">
										<?php if ( $pos ) : ?><span itemprop="jobTitle"><?php echo esc_html( $pos ); ?></span><?php endif; ?>
										<?php if ( $pos && $co ) : ?>, <?php endif; ?>
										<?php if ( $co ) : ?><span itemprop="worksFor" itemscope itemtype="https://schema.org/Organization"><span itemprop="name"><?php echo esc_html( $co ); ?></span></span><?php endif; ?>
									</div>
								<?php endif; ?>
							</div>
						</div>
					</article>
				<?php endforeach; ?>
			</div>
		</div>
		<?php
	}

	/** V8 — split: dark content left (quote watermark + text), photo top-right, author bottom-right.
	 * V8 is designed to feature ONE testimonial — render only the first item. */
	public static function render_v8( $items, $atts ) {
		$uid         = self::uid();
		$style       = self::style_vars( $atts );
		$quote_style = (int) $atts['quote_style'];
		$items       = array_slice( $items, 0, 1 );
		?>
		<div class="qt qt-v8" id="<?php echo esc_attr( $uid ); ?>" style="<?php echo esc_attr( $style ); ?>">
			<?php foreach ( $items as $item ) : ?>
				<article class="qt-card-v8" itemscope itemtype="https://schema.org/Review">
					<div class="qt-v8-content">
						<span class="qt-v8-watermark" aria-hidden="true"><?php echo Qt_Svg::quote_mark( $quote_style, 'qt-quote-mark qt-v8-quote' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
						<?php if ( ! empty( $item['heading'] ) ) : ?>
							<h3 class="qt-heading" itemprop="name"><?php echo esc_html( $item['heading'] ); ?></h3>
						<?php endif; ?>
						<?php if ( ! empty( $item['quote'] ) ) : ?>
							<blockquote class="qt-quote-text" itemprop="reviewBody"><?php echo esc_html( $item['quote'] ); ?></blockquote>
						<?php endif; ?>
					</div>
					<div class="qt-v8-right">
						<?php
						$v8_photo = ! empty( $item['photo_url_large'] ) ? $item['photo_url_large'] : $item['photo_url'];
						if ( $v8_photo ) : ?>
							<div class="qt-v8-photo"><img src="<?php echo esc_url( $v8_photo ); ?>" alt="<?php echo esc_attr( $item['name'] ); ?>" loading="lazy" /></div>
						<?php else : ?>
							<div class="qt-v8-photo qt-v8-photo-empty" aria-hidden="true"></div>
						<?php endif; ?>
						<div class="qt-v8-author" itemprop="author" itemscope itemtype="https://schema.org/Person">
							<div class="qt-name" itemprop="name"><?php echo esc_html( $item['name'] ); ?></div>
							<?php
							$pos = $item['position'];
							$co  = $item['company'];
							if ( $pos || $co ) :
								?>
								<div class="qt-meta">
									<?php if ( $pos ) : ?><span itemprop="jobTitle"><?php echo esc_html( $pos ); ?></span><?php endif; ?>
									<?php if ( $pos && $co ) : ?>, <?php endif; ?>
									<?php if ( $co ) : ?><span itemprop="worksFor" itemscope itemtype="https://schema.org/Organization"><span itemprop="name"><?php echo esc_html( $co ); ?></span></span><?php endif; ?>
								</div>
							<?php endif; ?>
						</div>
					</div>
				</article>
			<?php endforeach; ?>
		</div>
		<?php
	}
}
