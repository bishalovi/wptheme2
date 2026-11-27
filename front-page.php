<?php
defined( 'ABSPATH' ) || exit;

/**
 * The template for displaying the Homepage.
 * Inspired by ichoicebd.com: Direct All Products Grid.
 * Preserves 100% of WooCommerce official loop hooks for PixelYourSite.
 */
get_header();
?>

<main id="main" class="swift-main swift-homepage" role="main">
	<div class="swift-container">
		
		<div class="swift-section-header">
			<h1 class="swift-section-title"><?php esc_html_e( 'সকল পণ্য', 'swift-woo' ); ?></h1>
		</div>

		<?php
		if ( class_exists( 'WooCommerce' ) ) {
			// Query latest products natively
			$paged = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : ( ( get_query_var( 'page' ) ) ? get_query_var( 'page' ) : 1 );
			$args = array(
				'post_type'      => 'product',
				'post_status'    => 'publish',
				'posts_per_page' => 16,
				'paged'          => $paged,
				'orderby'        => 'date',
				'order'          => 'DESC',
			);

			$products_query = new WP_Query( $args );

			if ( $products_query->have_posts() ) {
				// OFFICIAL HOOK — intact
				do_action( 'woocommerce_before_shop_loop' );

				woocommerce_product_loop_start();

				while ( $products_query->have_posts() ) {
					$products_query->the_post();

					/**
					 * Hook: woocommerce_shop_loop.
					 */
					do_action( 'woocommerce_shop_loop' );

					wc_get_template_part( 'content', 'product' );
				}

				woocommerce_product_loop_end();

				// OFFICIAL HOOK — intact (pagination)
				do_action( 'woocommerce_after_shop_loop' );

				wp_reset_postdata();
			} else {
				/**
				 * Hook: woocommerce_no_products_found.
				 */
				do_action( 'woocommerce_no_products_found' );
			}
		} else {
			echo '<p class="swift-notice">' . esc_html__( 'অনুগ্রহ করে WooCommerce প্লাগইনটি ইনস্টল ও সক্রিয় করুন।', 'swift-woo' ) . '</p>';
		}
		?>

	</div>
</main>

<?php
get_footer();
