<?php
defined( 'ABSPATH' ) || exit;

/**
 * Single Product page template.
 * Calls standard WooCommerce single-product hooks & template parts.
 * All official WooCommerce hooks remain 100% intact.
 */
get_header( 'shop' );

/**
 * Hook: woocommerce_before_main_content.
 *
 * @hooked woocommerce_output_content_wrapper - 10 (outputs opening divs for the content)
 * @hooked woocommerce_breadcrumb - 20
 */
do_action( 'woocommerce_before_main_content' );
?>

<main id="main" class="swift-main swift-single-product" role="main">
	<div class="swift-container">
		<?php
		while ( have_posts() ) :
			the_post();
			wc_get_template_part( 'content', 'single-product' );
		endwhile;
		?>
	</div>
</main>

<?php
/**
 * Hook: woocommerce_after_main_content.
 *
 * @hooked woocommerce_output_content_wrapper_end - 10 (outputs closing divs for the content)
 */
do_action( 'woocommerce_after_main_content' );

/**
 * Hook: woocommerce_sidebar.
 */
do_action( 'woocommerce_sidebar' );

get_footer( 'shop' );
