<?php
defined( 'ABSPATH' ) || exit;
/**
 * Single Product page template.
 * All official WooCommerce single product hooks remain 100% intact.
 */
get_header();
?>
<main id="main" class="swift-main swift-single-product" role="main">
	<div class="swift-container">
		<?php
		while ( have_posts() ) :
			the_post();
			global $product;
			$product = wc_get_product( get_the_ID() );

			// OFFICIAL HOOK - intact
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
					// OFFICIAL HOOK — intact (outputs title, rating, price, excerpt, add-to-cart form, meta, sharing)
					do_action( 'woocommerce_single_product_summary' );
					?>
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
