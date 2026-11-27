<?php
defined( 'ABSPATH' ) || exit;

/**
 * Output the Buy Now / Order Now button.
 * Uses standard WooCommerce AJAX cart flow — zero hook removal.
 */
if ( ! function_exists( 'swift_woo_order_now_button' ) ) {
	function swift_woo_order_now_button( $passed_product = null ) {
		if ( $passed_product && is_a( $passed_product, 'WC_Product' ) ) {
			$the_product = $passed_product;
		} else {
			global $product;
			$the_product = $product;
		}
		if ( ! $the_product || ! is_a( $the_product, 'WC_Product' ) ) {
			return;
		}
		$label = esc_html( get_theme_mod( 'swift_order_btn_label', 'অর্ডার করুন' ) );
		?>
		<button
			type="button"
			class="swift-buy-now-btn button"
			data-product-id="<?php echo esc_attr( $the_product->get_id() ); ?>"
			aria-label="<?php echo esc_attr( $label . ' - ' . $the_product->get_name() ); ?>"
		>
			<?php echo esc_html( $label ); ?>
		</button>
		<?php
	}
}

/**
 * Hook Buy Now & COD badge inside single product add-to-cart form
 * Zero official hooks modified — purely additive.
 */
if ( ! function_exists( 'swift_woo_add_buttons_to_single_form' ) ) {
	function swift_woo_add_buttons_to_single_form() {
		global $product;
		if ( ! $product ) {
			return;
		}
		swift_woo_order_now_button( $product );
	}
}
add_action( 'woocommerce_after_add_to_cart_button', 'swift_woo_add_buttons_to_single_form', 5 );

if ( ! function_exists( 'swift_woo_add_cod_to_single_form' ) ) {
	function swift_woo_add_cod_to_single_form() {
		swift_woo_cod_badge();
		swift_woo_whatsapp_button();
	}
}
add_action( 'woocommerce_after_add_to_cart_form', 'swift_woo_add_cod_to_single_form', 10 );

/**
 * Wrap loop action buttons AFTER woocommerce_template_loop_product_link_close (priority 5)
 * so </a> never closes inside .swift-card-actions.
 */
if ( ! function_exists( 'swift_woo_loop_actions_open' ) ) {
	function swift_woo_loop_actions_open() {
		echo '<div class="swift-card-actions">';
	}
}
add_action( 'woocommerce_after_shop_loop_item', 'swift_woo_loop_actions_open', 8 );

/**
 * Hook Buy Now to Shop/Home product loop cards
 * Zero official hooks modified — purely additive.
 */
if ( ! function_exists( 'swift_woo_add_loop_buy_now' ) ) {
	function swift_woo_add_loop_buy_now() {
		global $product;
		if ( ! $product ) {
			return;
		}
		swift_woo_order_now_button( $product );
	}
}
add_action( 'woocommerce_after_shop_loop_item', 'swift_woo_add_loop_buy_now', 15 );

if ( ! function_exists( 'swift_woo_loop_actions_close' ) ) {
	function swift_woo_loop_actions_close() {
		echo '</div>';
	}
}
add_action( 'woocommerce_after_shop_loop_item', 'swift_woo_loop_actions_close', 20 );

/**
 * Output WhatsApp order button (Single Product Page only).
 */
if ( ! function_exists( 'swift_woo_whatsapp_button' ) ) {
	function swift_woo_whatsapp_button() {
		if ( ! get_theme_mod( 'swift_whatsapp_show', '1' ) ) {
			return;
		}
		$number = sanitize_text_field( get_theme_mod( 'swift_whatsapp', '' ) );
		if ( ! $number ) {
			return;
		}
		global $product;
		if ( ! $product || ! is_a( $product, 'WC_Product' ) ) {
			return;
		}
		$text = rawurlencode( $product->get_name() . ' - ' . get_permalink() );
		$url  = 'https://wa.me/' . $number . '?text=' . $text;
		?>
		<a
			href="<?php echo esc_url( $url ); ?>"
			class="swift-whatsapp-btn"
			target="_blank"
			rel="noopener noreferrer"
			aria-label="<?php esc_attr_e( 'WhatsApp-এ অর্ডার করুন', 'swift-woo' ); ?>"
		>
			<svg aria-hidden="true" focusable="false" width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/><path d="M11.999 2C6.477 2 2 6.477 2 12c0 1.989.574 3.842 1.565 5.408L2.045 22l4.735-1.493A9.953 9.953 0 0012 22c5.523 0 10-4.477 10-10S17.523 2 11.999 2zm.001 18c-1.721 0-3.33-.463-4.72-1.272l-.339-.201-3.512 1.107.943-3.436-.22-.354A7.964 7.964 0 014 12c0-4.418 3.582-8 8-8s8 3.582 8 8-3.582 8-8 8z"/></svg>
			<?php esc_html_e( 'WhatsApp-এ অর্ডার করুন', 'swift-woo' ); ?>
		</a>
		<?php
	}
}

/**
 * Output Cash on Delivery trust badge.
 */
if ( ! function_exists( 'swift_woo_cod_badge' ) ) {
	function swift_woo_cod_badge() {
		?>
		<div class="swift-trust-badge" aria-label="<?php esc_attr_e( 'Cash on Delivery Available', 'swift-woo' ); ?>">
			<svg aria-hidden="true" focusable="false" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="6" width="20" height="12" rx="2"/><path d="M12 12h.01M6 12h.01M18 12h.01"/></svg>
			<span><?php esc_html_e( 'Cash on Delivery', 'swift-woo' ); ?></span>
		</div>
		<?php
	}
}

/**
 * Helper: Get WooCommerce URL safely (returns home_url if WooCommerce inactive).
 */
if ( ! function_exists( 'swift_woo_get_wc_url' ) ) {
	function swift_woo_get_wc_url( $type = 'cart' ) {
		if ( ! class_exists( 'WooCommerce' ) ) {
			return home_url( '/' );
		}
		switch ( $type ) {
			case 'checkout':
				return wc_get_checkout_url();
			case 'shop':
				return wc_get_page_permalink( 'shop' );
			default:
				return wc_get_cart_url();
		}
	}
}

/**
 * Helper: Get cart count safely.
 */
if ( ! function_exists( 'swift_woo_cart_count' ) ) {
	function swift_woo_cart_count() {
		if ( ! class_exists( 'WooCommerce' ) || ! WC()->cart ) {
			return 0;
		}
		return WC()->cart->get_cart_contents_count();
	}
}
