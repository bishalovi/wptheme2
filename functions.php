<?php
defined( 'ABSPATH' ) || exit;

define( 'SWIFT_WOO_VERSION', '1.0.2' );
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
			array(),
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
		) );

		// WhatsApp Button Show/Hide
		$wp_customize->add_setting( 'swift_whatsapp_show', array( 'default' => '1', 'sanitize_callback' => 'absint' ) );
		$wp_customize->add_control( 'swift_whatsapp_show', array(
			'label'   => esc_html__( 'Show WhatsApp Order Button', 'swift-woo' ),
			'section' => 'swift_woo_options',
			'type'    => 'checkbox',
		) );

		// Order Button Label
		$wp_customize->add_setting( 'swift_order_btn_label', array( 'default' => 'অর্ডার করুন', 'sanitize_callback' => 'sanitize_text_field' ) );
		$wp_customize->add_control( 'swift_order_btn_label', array(
			'label'   => esc_html__( '"Order Now" Button Label', 'swift-woo' ),
			'section' => 'swift_woo_options',
			'type'    => 'text',
		) );

		// Cart Button Label
		$wp_customize->add_setting( 'swift_cart_btn_label', array( 'default' => 'Cart', 'sanitize_callback' => 'sanitize_text_field' ) );
		$wp_customize->add_control( 'swift_cart_btn_label', array(
			'label'   => esc_html__( '"Add to Cart" Button Label', 'swift-woo' ),
			'section' => 'swift_woo_options',
			'type'    => 'text',
		) );

		// Footer Copyright
		$wp_customize->add_setting( 'swift_footer_text', array( 'default' => '', 'sanitize_callback' => 'wp_kses_post' ) );
		$wp_customize->add_control( 'swift_footer_text', array(
			'label'   => esc_html__( 'Footer Copyright Text', 'swift-woo' ),
			'section' => 'swift_woo_options',
			'type'    => 'textarea',
		) );
	}
}
add_action( 'customize_register', 'swift_woo_customizer' );

/* -----------------------------------------------------------------------
 * 5. INLINE PRIMARY COLOR CSS (from Customizer)
 * --------------------------------------------------------------------- */
if ( ! function_exists( 'swift_woo_inline_css' ) ) {
	function swift_woo_inline_css() {
		$color = sanitize_hex_color( get_theme_mod( 'swift_primary_color', '#e53e3e' ) );
		$css   = "
			:root { --swift-primary: {$color}; }
		";
		wp_add_inline_style( 'swift-woo-style', $css );
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
 * 11. INCLUDE MODULES
 * --------------------------------------------------------------------- */
require_once SWIFT_WOO_DIR . '/inc/template-tags.php';
require_once SWIFT_WOO_DIR . '/inc/updater.php';
