<?php
defined( 'ABSPATH' ) || exit;
/**
 * Single Product page template.
 * Uses get_header / get_footer to maintain all WordPress hooks.
 * All WooCommerce single product hooks remain 100% intact.
 */
get_header();
?>
<main id="main" class="swift-main swift-single-product" role="main">
	<div class="swift-container">
		<?php
		while ( have_posts() ) :
			the_post();
			// OFFICIAL HOOK - never removed
			do_action( 'woocommerce_before_single_product' );

			if ( post_password_required() ) {
				echo get_the_password_form(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				return;
			}
			?>
			<div id="product-<?php the_ID(); ?>" <?php wc_product_class( 'swift-single-product__wrapper', $product ); ?>>

				<div class="swift-single-product__gallery">
					<?php
					// OFFICIAL HOOK — intact
					do_action( 'woocommerce_before_single_product_summary' );
					?>
				</div>

				<div class="swift-single-product__summary">
					<?php
					// OFFICIAL HOOK — intact (outputs title, price, excerpt, add-to-cart, meta, sharing)
					do_action( 'woocommerce_single_product_summary' );
					?>

					<!-- Buy Now Button (RIGHT of Cart button — via CSS flex) -->
					<?php swift_woo_order_now_button( $product ); ?>

					<!-- Cash on Delivery Trust Badge -->
					<?php swift_woo_cod_badge(); ?>

					<!-- WhatsApp Order Button -->
					<?php swift_woo_whatsapp_button(); ?>
				</div>

			</div>

			<?php
			// OFFICIAL HOOK — intact (tabs, related products, upsells)
			do_action( 'woocommerce_after_single_product' );
		endwhile;
		?>
	</div>
</main>
<?php get_footer(); ?>
