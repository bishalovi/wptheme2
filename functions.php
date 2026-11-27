<?php
defined( 'ABSPATH' ) || exit;

define( 'SWIFT_WOO_VERSION', '1.0.14' );
define( 'SWIFT_WOO_DIR', get_template_directory() );
define( 'SWIFT_WOO_URI', get_template_directory_uri() );

/* -----------------------------------------------------------------------
 * 1. THEME SETUP
 * --------------------------------------------------------------------- */
if ( ! function_exists( 'swift_woo_setup' ) ) {
	function swift_woo_setup() {
		load_theme_textdomain( 'swift-woo', SWIFT_WOO_DIR . '/languages' );

		add_theme_support( 'title-tag' );
		add_theme_support( 'post-thumbnails' );
		add_theme_support( 'custom-logo' );
		add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' ) );
		add_theme_support( 'woocommerce' );
		add_theme_support( 'wc-product-gallery-zoom' );
		add_theme_support( 'wc-product-gallery-lightbox' );
		add_theme_support( 'wc-product-gallery-slider' );

		register_nav_menus( array(
			'primary' => esc_html__( 'Primary Menu', 'swift-woo' ),
		) );
	}
}
add_action( 'after_setup_theme', 'swift_woo_setup' );

/* -----------------------------------------------------------------------
 * 2. WOOCOMMERCE HPOS COMPATIBILITY
 * --------------------------------------------------------------------- */
if ( ! function_exists( 'swift_woo_declare_hpos' ) ) {
	function swift_woo_declare_hpos() {
		if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
		}
	}
}
add_action( 'before_woocommerce_init', 'swift_woo_declare_hpos' );

/* -----------------------------------------------------------------------
 * 3. ENQUEUE STYLES & SCRIPTS
 * --------------------------------------------------------------------- */
if ( ! function_exists( 'swift_woo_enqueue' ) ) {
	function swift_woo_enqueue() {
		// Main stylesheet
		wp_enqueue_style(
			'swift-woo-style',
			SWIFT_WOO_URI . '/assets/css/main.min.css',
			array(),
			SWIFT_WOO_VERSION
		);

		// Local fonts
		wp_enqueue_style(
			'swift-woo-fonts',
			SWIFT_WOO_URI . '/assets/fonts/fonts.css',
			array(),
			SWIFT_WOO_VERSION
		);

		// WooCommerce block styles (if block checkout is active)
		if ( class_exists( 'WooCommerce' ) ) {
			wp_enqueue_style(
				'swift-woo-wc',
				SWIFT_WOO_URI . '/assets/css/woocommerce.min.css',
				array( 'woocommerce-general' ),
				SWIFT_WOO_VERSION
			);
		}

		// Main JS (deferred, non-blocking)
		wp_enqueue_script(
			'swift-woo-main',
			SWIFT_WOO_URI . '/assets/js/main.min.js',
			array( 'jquery' ),
			SWIFT_WOO_VERSION,
			array(
				'in_footer' => true,
				'strategy'  => 'defer',
			)
		);

		// Variation & Multi-Color Picker JS (single product only)
		if ( is_product() ) {
			wp_enqueue_script(
				'swift-woo-variation',
				SWIFT_WOO_URI . '/assets/js/variation.min.js',
				array( 'jquery', 'wc-add-to-cart-variation' ),
				SWIFT_WOO_VERSION,
				array(
					'in_footer' => true,
					'strategy'  => 'defer',
				)
			);
		}

		// Pass AJAX URL and nonce to JS
		wp_localize_script( 'swift-woo-main', 'swiftWoo', array(
			'ajaxUrl'     => esc_url( admin_url( 'admin-ajax.php' ) ),
			'nonce'       => wp_create_nonce( 'swift_woo_nonce' ),
			'cartUrl'     => esc_url( swift_woo_get_wc_url( 'cart' ) ),
			'checkoutUrl' => esc_url( swift_woo_get_wc_url( 'checkout' ) ),
			'whatsapp'    => esc_attr( get_theme_mod( 'swift_whatsapp', '' ) ),
		) );
	}
}
add_action( 'wp_enqueue_scripts', 'swift_woo_enqueue' );

/* -----------------------------------------------------------------------
 * 4. CUSTOMIZER OPTIONS
 * --------------------------------------------------------------------- */
if ( ! function_exists( 'swift_woo_customizer' ) ) {
	function swift_woo_customizer( $wp_customize ) {
		$wp_customize->add_section( 'swift_woo_options', array(
			'title'    => esc_html__( 'Swift Woo Options', 'swift-woo' ),
			'priority' => 30,
		) );

		// Announcement Bar Text
		$wp_customize->add_setting( 'swift_announcement', array( 'default' => '', 'sanitize_callback' => 'sanitize_text_field' ) );
		$wp_customize->add_control( 'swift_announcement', array(
			'label'   => esc_html__( 'Announcement Bar Text', 'swift-woo' ),
			'section' => 'swift_woo_options',
			'type'    => 'text',
		) );

		// Announcement Bar Show/Hide
		$wp_customize->add_setting( 'swift_announcement_show', array( 'default' => '1', 'sanitize_callback' => 'absint' ) );
		$wp_customize->add_control( 'swift_announcement_show', array(
			'label'   => esc_html__( 'Show Announcement Bar', 'swift-woo' ),
			'section' => 'swift_woo_options',
			'type'    => 'checkbox',
		) );

		// Primary Brand Color
		$wp_customize->add_setting( 'swift_primary_color', array( 'default' => '#e53e3e', 'sanitize_callback' => 'sanitize_hex_color' ) );
		$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'swift_primary_color', array(
			'label'   => esc_html__( 'Primary Brand Color', 'swift-woo' ),
			'section' => 'swift_woo_options',
		) ) );

		// WhatsApp Number
		$wp_customize->add_setting( 'swift_whatsapp', array( 'default' => '', 'sanitize_callback' => 'sanitize_text_field' ) );
		$wp_customize->add_control( 'swift_whatsapp', array(
			'label'       => esc_html__( 'WhatsApp Number (e.g. 8801XXXXXXXXX)', 'swift-woo' ),
			'section'     => 'swift_woo_options',
			'type'        => 'text',
			'description' => esc_html__( 'Used for the WhatsApp order button.', 'swift-woo' ),
		) );

		// WhatsApp Show/Hide
		$wp_customize->add_setting( 'swift_whatsapp_show', array( 'default' => '1', 'sanitize_callback' => 'absint' ) );
		$wp_customize->add_control( 'swift_whatsapp_show', array(
			'label'   => esc_html__( 'Show WhatsApp Order Button', 'swift-woo' ),
			'section' => 'swift_woo_options',
			'type'    => 'checkbox',
		) );

		// Order Button Text
		$wp_customize->add_setting( 'swift_order_btn_label', array( 'default' => 'অর্ডার করুন', 'sanitize_callback' => 'sanitize_text_field' ) );
		$wp_customize->add_control( 'swift_order_btn_label', array(
			'label'   => esc_html__( 'Order Button Label', 'swift-woo' ),
			'section' => 'swift_woo_options',
			'type'    => 'text',
		) );
	}
}
add_action( 'customize_register', 'swift_woo_customizer' );

/* -----------------------------------------------------------------------
 * 5. OUTPUT DYNAMIC PRIMARY COLOR
 * --------------------------------------------------------------------- */
if ( ! function_exists( 'swift_woo_inline_css' ) ) {
	function swift_woo_inline_css() {
		$primary = get_theme_mod( 'swift_primary_color', '#e53e3e' );
		if ( $primary !== '#e53e3e' ) {
			$safe = sanitize_hex_color( $primary );
			if ( $safe ) {
				$css = ':root { --swift-primary: ' . $safe . '; }';
				wp_add_inline_style( 'swift-woo-style', $css );
			}
		}
	}
}
add_action( 'wp_enqueue_scripts', 'swift_woo_inline_css' );

/* -----------------------------------------------------------------------
 * 6. WOOCOMMERCE CART FRAGMENTS (Mini Cart)
 *    Uses OFFICIAL woocommerce_add_to_cart_fragments — NOT removed/modified
 * --------------------------------------------------------------------- */
if ( ! function_exists( 'swift_woo_cart_fragment' ) ) {
	function swift_woo_cart_fragment( $fragments ) {
		ob_start();
		?>
		<span class="swift-cart-count"><?php echo absint( WC()->cart->get_cart_contents_count() ); ?></span>
		<?php
		$fragments['span.swift-cart-count'] = ob_get_clean();
		return $fragments;
	}
}
add_filter( 'woocommerce_add_to_cart_fragments', 'swift_woo_cart_fragment' );

/* -----------------------------------------------------------------------
 * 7. AJAX LIVE SEARCH (nonce-verified, sanitized)
 * --------------------------------------------------------------------- */
if ( ! function_exists( 'swift_woo_ajax_search' ) ) {
	function swift_woo_ajax_search() {
		check_ajax_referer( 'swift_woo_nonce', 'nonce' );

		$term = isset( $_GET['term'] ) ? sanitize_text_field( wp_unslash( $_GET['term'] ) ) : '';
		if ( strlen( $term ) < 2 ) {
			wp_send_json_success( array() );
		}

		$args = array(
			'post_type'      => 'product',
			'post_status'    => 'publish',
			's'              => $term,
			'posts_per_page' => 6,
		);
		$query   = new WP_Query( $args );
		$results = array();
		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();
				$product = wc_get_product( get_the_ID() );
				if ( ! $product ) {
					continue;
				}
				$results[] = array(
					'id'    => absint( get_the_ID() ),
					'title' => esc_html( get_the_title() ),
					'url'   => esc_url( get_permalink() ),
					'img'   => esc_url( get_the_post_thumbnail_url( get_the_ID(), 'thumbnail' ) ),
					'price' => wp_kses_post( $product->get_price_html() ),
				);
			}
			wp_reset_postdata();
		}
		wp_send_json_success( $results );
	}
}
add_action( 'wp_ajax_swift_woo_search', 'swift_woo_ajax_search' );
add_action( 'wp_ajax_nopriv_swift_woo_search', 'swift_woo_ajax_search' );

/* -----------------------------------------------------------------------
 * 8. BUY NOW / ORDER NOW BUTTON
 *    Uses standard WooCommerce add-to-cart + redirect — no hook removal
 * --------------------------------------------------------------------- */
if ( ! function_exists( 'swift_woo_buy_now' ) ) {
	function swift_woo_buy_now() {
		check_ajax_referer( 'swift_woo_nonce', 'nonce' );

		$product_id   = absint( $_POST['product_id'] ?? 0 );
		$quantity     = absint( $_POST['quantity'] ?? 1 );
		$variation_id = absint( $_POST['variation_id'] ?? 0 );
		$variation    = array();

		if ( ! $product_id ) {
			wp_send_json_error( array( 'message' => esc_html__( 'Invalid product.', 'swift-woo' ) ) );
		}

		// Sanitize variation attributes
		if ( ! empty( $_POST['variation'] ) && is_array( $_POST['variation'] ) ) {
			foreach ( $_POST['variation'] as $key => $val ) {
				$variation[ sanitize_key( $key ) ] = sanitize_text_field( wp_unslash( $val ) );
			}
		}

		// If variable product without variation_id (e.g. clicked from card), auto-select first in-stock child
		$product = wc_get_product( $product_id );
		if ( $product && $product->is_type( 'variable' ) && ! $variation_id ) {
			$children = $product->get_children();
			if ( ! empty( $children ) ) {
				foreach ( $children as $child_id ) {
					$child_product = wc_get_product( $child_id );
					if ( $child_product && $child_product->is_in_stock() ) {
						$variation_id = $child_id;
						$variation    = $child_product->get_variation_attributes();
						break;
					}
				}
			}
		}

		// Add to cart via WooCommerce native method
		$cart_item_key = WC()->cart->add_to_cart( $product_id, $quantity, $variation_id, $variation );

		if ( $cart_item_key ) {
			wp_send_json_success( array( 'redirect' => esc_url( wc_get_checkout_url() ) ) );
		} else {
			$notices = wc_print_notices( true );
			wp_send_json_error( array( 'message' => wp_kses_post( $notices ) ) );
		}
	}
}
add_action( 'wp_ajax_swift_woo_buy_now', 'swift_woo_buy_now' );
add_action( 'wp_ajax_nopriv_swift_woo_buy_now', 'swift_woo_buy_now' );

/* -----------------------------------------------------------------------
 * 9. MULTI-COLOR DATA — SAVE TO CART, CHECKOUT & ORDER
 *    All ADDITIVE hooks — zero existing hooks removed or modified
 * --------------------------------------------------------------------- */
if ( ! function_exists( 'swift_woo_add_color_cart_data' ) ) {
	function swift_woo_add_color_cart_data( $cart_item_data, $product_id, $variation_id ) {
		if ( isset( $_POST['swift_colors'] ) && is_array( $_POST['swift_colors'] ) ) {
			$colors = array();
			foreach ( $_POST['swift_colors'] as $i => $color ) {
				$colors[ absint( $i ) ] = sanitize_text_field( wp_unslash( $color ) );
			}
			if ( ! empty( $colors ) ) {
				$cart_item_data['swift_selected_colors'] = $colors;
			}
		}
		return $cart_item_data;
	}
}
add_filter( 'woocommerce_add_cart_item_data', 'swift_woo_add_color_cart_data', 10, 3 );

if ( ! function_exists( 'swift_woo_display_color_cart' ) ) {
	function swift_woo_display_color_cart( $item_data, $cart_item ) {
		if ( ! empty( $cart_item['swift_selected_colors'] ) ) {
			foreach ( $cart_item['swift_selected_colors'] as $i => $color ) {
				$item_data[] = array(
					'key'   => esc_html( sprintf( __( 'Color %d', 'swift-woo' ), absint( $i ) + 1 ) ),
					'value' => esc_html( $color ),
				);
			}
		}
		return $item_data;
	}
}
add_filter( 'woocommerce_get_item_data', 'swift_woo_display_color_cart', 10, 2 );

if ( ! function_exists( 'swift_woo_save_color_order_meta' ) ) {
	function swift_woo_save_color_order_meta( $item, $cart_item_key, $values, $order ) {
		if ( ! empty( $values['swift_selected_colors'] ) ) {
			foreach ( $values['swift_selected_colors'] as $i => $color ) {
				$item->add_meta_data(
					esc_html( sprintf( __( 'Color %d', 'swift-woo' ), absint( $i ) + 1 ) ),
					esc_html( $color ),
					true
				);
			}
		}
	}
}
add_action( 'woocommerce_checkout_create_order_line_item', 'swift_woo_save_color_order_meta', 10, 4 );

/* -----------------------------------------------------------------------
 * 10. CHECKOUT: 11-DIGIT BD PHONE VALIDATION (server-side)
 *     ADDITIVE — does NOT modify or unset any checkout fields
 * --------------------------------------------------------------------- */
if ( ! function_exists( 'swift_woo_validate_bd_phone' ) ) {
	function swift_woo_validate_bd_phone() {
		if ( isset( $_POST['billing_phone'] ) ) {
			$phone = sanitize_text_field( wp_unslash( $_POST['billing_phone'] ) );
			if ( ! preg_match( '/^01[3-9]\d{8}$/', $phone ) ) {
				wc_add_notice( esc_html__( 'অনুগ্রহ করে সঠিক ১১ ডিজিটের বাংলাদেশি মোবাইল নম্বর দিন (যেমন: 01XXXXXXXXX)', 'swift-woo' ), 'error' );
			}
		}
	}
}
add_action( 'woocommerce_checkout_process', 'swift_woo_validate_bd_phone' );

/* -----------------------------------------------------------------------
 * 11. SEO & OPEN GRAPH META TAGS (Single Product)
 *     Outputs rich social/CAPI previews — purely additive
 * --------------------------------------------------------------------- */
if ( ! function_exists( 'swift_woo_seo_meta' ) ) {
	function swift_woo_seo_meta() {
		if ( ! is_product() ) {
			return;
		}
		global $post;
		$product = wc_get_product( $post->ID );
		if ( ! $product ) {
			return;
		}

		$title       = esc_attr( $product->get_name() );
		$short_desc  = $product->get_short_description();
		$description = esc_attr( wp_strip_all_tags( $short_desc ? $short_desc : $product->get_name() ) );
		$url         = esc_url( get_permalink() );
		$price       = esc_attr( $product->get_price() );
		$currency    = esc_attr( get_woocommerce_currency() );

		echo '<meta property="og:type" content="product" />' . "\n";
		echo '<meta property="og:title" content="' . $title . '" />' . "\n";
		echo '<meta property="og:description" content="' . $description . '" />' . "\n";
		echo '<meta property="og:url" content="' . $url . '" />' . "\n";

		$img_id = $product->get_image_id();
		if ( $img_id ) {
			$img_url = wp_get_attachment_image_url( $img_id, 'full' );
			if ( $img_url ) {
				echo '<meta property="og:image" content="' . esc_url( $img_url ) . '" />' . "\n";
			}
		}

		if ( $price ) {
			echo '<meta property="product:price:amount" content="' . $price . '" />' . "\n";
			echo '<meta property="product:price:currency" content="' . $currency . '" />' . "\n";
		}
		echo '<meta property="product:availability" content="' . ( $product->is_in_stock() ? 'in stock' : 'out of stock' ) . '" />' . "\n";
		echo '<meta name="twitter:card" content="summary_large_image" />' . "\n";
	}
}
add_action( 'wp_head', 'swift_woo_seo_meta', 5 );

/* -----------------------------------------------------------------------
 * 12. GRIHOTOOLS STYLE CHECKOUT CUSTOMIZATIONS
 *     Zero unset() calls — 100% hook compliant, PixelYourSite friendly
 * --------------------------------------------------------------------- */
if ( ! function_exists( 'swift_woo_customize_checkout_fields' ) ) {
	function swift_woo_customize_checkout_fields( $fields ) {
		// Essential fields with Bengali labels & clean placeholders
		if ( isset( $fields['billing']['billing_first_name'] ) ) {
			$fields['billing']['billing_first_name']['label']       = __( 'আপনার সম্পূর্ণ নাম', 'swift-woo' );
			$fields['billing']['billing_first_name']['placeholder'] = __( 'আপনার নাম লিখুন', 'swift-woo' );
			$fields['billing']['billing_first_name']['class']       = array( 'form-row-wide', 'swift-field-name' );
			$fields['billing']['billing_first_name']['required']    = true;
		}

		if ( isset( $fields['billing']['billing_phone'] ) ) {
			$fields['billing']['billing_phone']['label']       = __( 'মোবাইল নাম্বার', 'swift-woo' );
			$fields['billing']['billing_phone']['placeholder'] = __( '১১ ডিজিটের মোবাইল নম্বর দিন (যেমন: 01XXXXXXXXX)', 'swift-woo' );
			$fields['billing']['billing_phone']['class']       = array( 'form-row-wide', 'swift-field-phone' );
			$fields['billing']['billing_phone']['required']    = true;
		}

		if ( isset( $fields['billing']['billing_address_1'] ) ) {
			$fields['billing']['billing_address_1']['label']       = __( 'সম্পূর্ণ ডেলিভারি ঠিকানা', 'swift-woo' );
			$fields['billing']['billing_address_1']['placeholder'] = __( 'বাসা/রোড নং, এলাকা/থানা এবং জেলার নাম লিখুন', 'swift-woo' );
			$fields['billing']['billing_address_1']['class']       = array( 'form-row-wide', 'swift-field-address' );
			$fields['billing']['billing_address_1']['required']    = true;
		}

		// Make non-essential fields optional to ensure seamless submission without unset()
		$optional_fields = array(
			'billing_last_name',
			'billing_company',
			'billing_country',
			'billing_city',
			'billing_state',
			'billing_postcode',
			'billing_address_2',
			'billing_email',
		);
		foreach ( $optional_fields as $f ) {
			if ( isset( $fields['billing'][ $f ] ) ) {
				$fields['billing'][ $f ]['required'] = false;
			}
		}

		if ( isset( $fields['order']['order_comments'] ) ) {
			$fields['order']['order_comments']['label']       = __( 'অতিরিক্ত তথ্য (ঐচ্ছিক)', 'swift-woo' );
			$fields['order']['order_comments']['placeholder'] = __( 'ডেলিভারি সংক্রান্ত কোনো বিশেষ নির্দেশনা থাকলে এখানে লিখুন...', 'swift-woo' );
		}

		return $fields;
	}
}
add_filter( 'woocommerce_checkout_fields', 'swift_woo_customize_checkout_fields', 20 );

// Default country to Bangladesh & default city to Dhaka
add_filter( 'default_checkout_billing_country', function() { return 'BD'; } );
add_filter( 'default_checkout_billing_city', function() { return 'Dhaka'; } );
add_filter( 'default_checkout_billing_state', function() { return 'BD-13'; } );

/* -----------------------------------------------------------------------
 * 13. DELIVERY AREA SELECTOR (Inside Dhaka 70 / Outside Dhaka 120)
 * --------------------------------------------------------------------- */
if ( ! function_exists( 'swift_woo_render_delivery_area_selector' ) ) {
	function swift_woo_render_delivery_area_selector( $checkout ) {
		$current_area = WC()->session ? WC()->session->get( 'swift_delivery_area', 'inside_dhaka' ) : 'inside_dhaka';
		?>
		<div class="swift-delivery-area-box" id="swift-delivery-area-box">
			<label class="swift-area-box-title">
				<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
				<span><?php esc_html_e( 'ডেলিভারি এরিয়া বেছে নিন *', 'swift-woo' ); ?></span>
			</label>
			<div class="swift-area-options">
				<label class="swift-area-option <?php echo ( $current_area === 'inside_dhaka' ) ? 'is-selected' : ''; ?>">
					<input type="radio" name="swift_delivery_area" value="inside_dhaka" <?php checked( $current_area, 'inside_dhaka' ); ?>>
					<div class="swift-area-option__body">
						<span class="swift-area-option__name"><?php esc_html_e( 'ঢাকার ভিতরে', 'swift-woo' ); ?></span>
						<span class="swift-area-option__price">৳ ৭০</span>
					</div>
				</label>
				<label class="swift-area-option <?php echo ( $current_area === 'outside_dhaka' ) ? 'is-selected' : ''; ?>">
					<input type="radio" name="swift_delivery_area" value="outside_dhaka" <?php checked( $current_area, 'outside_dhaka' ); ?>>
					<div class="swift-area-option__body">
						<span class="swift-area-option__name"><?php esc_html_e( 'ঢাকার বাইরে', 'swift-woo' ); ?></span>
						<span class="swift-area-option__price">৳ ১২০</span>
					</div>
				</label>
			</div>
		</div>
		<?php
	}
}
add_action( 'woocommerce_after_checkout_billing_form', 'swift_woo_render_delivery_area_selector', 10 );

// AJAX handler to switch delivery area
if ( ! function_exists( 'swift_woo_ajax_update_delivery_area' ) ) {
	function swift_woo_ajax_update_delivery_area() {
		check_ajax_referer( 'swift_woo_nonce', 'nonce' );
		$area = isset( $_POST['area'] ) && $_POST['area'] === 'outside_dhaka' ? 'outside_dhaka' : 'inside_dhaka';
		if ( WC()->session ) {
			WC()->session->set( 'swift_delivery_area', $area );
		}
		wp_send_json_success( array( 'area' => $area ) );
	}
}
add_action( 'wp_ajax_swift_woo_update_delivery_area', 'swift_woo_ajax_update_delivery_area' );
add_action( 'wp_ajax_nopriv_swift_woo_update_delivery_area', 'swift_woo_ajax_update_delivery_area' );

// Calculate delivery charge fee
if ( ! function_exists( 'swift_woo_apply_delivery_area_fee' ) ) {
	function swift_woo_apply_delivery_area_fee( $cart ) {
		if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
			return;
		}
		// If WooCommerce shipping rates are already active, let them handle it
		$packages = WC()->shipping()->get_packages();
		if ( ! empty( $packages ) && ! empty( $packages[0]['rates'] ) ) {
			return;
		}

		$area  = WC()->session ? WC()->session->get( 'swift_delivery_area', 'inside_dhaka' ) : 'inside_dhaka';
		$fee   = ( $area === 'outside_dhaka' ) ? 120 : 70;
		$label = ( $area === 'outside_dhaka' ) ? __( 'ডেলিভারি চার্জ (ঢাকার বাইরে)', 'swift-woo' ) : __( 'ডেলিভারি চার্জ (ঢাকার ভিতরে)', 'swift-woo' );
		$cart->add_fee( $label, $fee, false );
	}
}
add_action( 'woocommerce_cart_calculate_fees', 'swift_woo_apply_delivery_area_fee' );

// Save delivery area to WooCommerce order meta
if ( ! function_exists( 'swift_woo_save_delivery_area_order_meta' ) ) {
	function swift_woo_save_delivery_area_order_meta( $order ) {
		$area  = WC()->session ? WC()->session->get( 'swift_delivery_area', 'inside_dhaka' ) : 'inside_dhaka';
		$label = ( $area === 'outside_dhaka' ) ? __( 'ঢাকার বাইরে (৳ ১২০)', 'swift-woo' ) : __( 'ঢাকার ভিতরে (৳ ৭০)', 'swift-woo' );
		$order->add_meta_data( __( 'ডেলিভারি এরিয়া', 'swift-woo' ), $label, true );
	}
}
add_action( 'woocommerce_checkout_create_order', 'swift_woo_save_delivery_area_order_meta', 10, 1 );

/* -----------------------------------------------------------------------
 * 14. CHECKOUT ORDER REVIEW THUMBNAIL (GrihoTools style)
 * --------------------------------------------------------------------- */
if ( ! function_exists( 'swift_woo_checkout_product_thumbnail' ) ) {
	function swift_woo_checkout_product_thumbnail( $name, $cart_item, $cart_item_key ) {
		if ( ! is_checkout() ) {
			return $name;
		}
		$product = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
		if ( ! $product || ! $product->exists() ) {
			return $name;
		}
		$thumb = $product->get_image(
			array( 56, 56 ),
			array(
				'class'   => 'swift-checkout-thumb',
				'loading' => 'lazy',
				'alt'     => esc_attr( $product->get_name() ),
			)
		);
		return '<div class="swift-checkout-line-item">' . $thumb . '<div class="swift-checkout-item-details">' . $name . '</div></div>';
	}
}
add_filter( 'woocommerce_cart_item_name', 'swift_woo_checkout_product_thumbnail', 10, 3 );

// Checkout Submit Button Text
if ( ! function_exists( 'swift_woo_order_button_text' ) ) {
	function swift_woo_order_button_text() {
		return esc_html__( 'ক্যাশ অন ডেলিভারিতে অর্ডার করুন', 'swift-woo' );
	}
}
add_filter( 'woocommerce_order_button_text', 'swift_woo_order_button_text' );

// Translate Add to Cart button text to Bengali
add_filter( 'woocommerce_product_single_add_to_cart_text', function() {
	return esc_html__( 'কার্টে যোগ করুন', 'swift-woo' );
} );
add_filter( 'woocommerce_product_add_to_cart_text', function() {
	return esc_html__( 'কার্টে যোগ করুন', 'swift-woo' );
} );

/* -----------------------------------------------------------------------
 * 15. INCLUDE MODULES
 * --------------------------------------------------------------------- */
require_once SWIFT_WOO_DIR . '/inc/template-tags.php';
require_once SWIFT_WOO_DIR . '/inc/updater.php';
