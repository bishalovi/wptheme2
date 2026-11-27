<?php
defined( 'ABSPATH' ) || exit;

/**
 * Single Product content template part (shovaas.com layout).
 * All official WooCommerce single product hooks remain 100% intact.
 */
global $product;

/**
 * Hook: woocommerce_before_single_product.
 *
 * @hooked woocommerce_output_all_notices - 10
 */
do_action( 'woocommerce_before_single_product' );

if ( post_password_required() ) {
	echo get_the_password_form(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	return;
}
?>
<div id="product-<?php the_ID(); ?>" <?php wc_product_class( 'swift-single-product__wrapper', $product ); ?>>

	<div class="swift-single-product__gallery">
		<?php
		/**
		 * Hook: woocommerce_before_single_product_summary.
		 *
		 * @hooked woocommerce_show_product_sale_flash - 10
		 * @hooked woocommerce_show_product_images - 20 (gallery zoom, lightbox, slider)
		 */
		do_action( 'woocommerce_before_single_product_summary' );
		?>
	</div>

	<div class="swift-single-product__summary summary entry-summary">
		<?php
		/**
		 * Hook: woocommerce_single_product_summary.
		 *
		 * @hooked woocommerce_template_single_title - 5
		 * @hooked woocommerce_template_single_rating - 10
		 * @hooked woocommerce_template_single_price - 10
		 * @hooked woocommerce_template_single_excerpt - 20
		 * @hooked woocommerce_template_single_add_to_cart - 30
		 * @hooked woocommerce_template_single_meta - 40
		 * @hooked woocommerce_template_single_sharing - 50
		 */
		do_action( 'woocommerce_single_product_summary' );
		?>
	</div>

</div>

<?php
/**
 * Hook: woocommerce_after_single_product_summary.
 *
 * @hooked woocommerce_output_product_data_tabs - 10 (Description, Reviews tabs)
 * @hooked woocommerce_upsell_display - 15 (Upsell products)
 * @hooked woocommerce_output_related_products - 20 (Related products)
 */
do_action( 'woocommerce_after_single_product_summary' );

/**
 * Hook: woocommerce_after_single_product.
 */
do_action( 'woocommerce_after_single_product' );
