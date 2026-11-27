<?php
defined( 'ABSPATH' ) || exit;

get_header();
?>
<main id="main" class="swift-main" role="main">

	<?php if ( is_front_page() ) : ?>
	<!-- ========================
	     HOMEPAGE: All Products Grid (direct, no category slider - ichoicebd.com style)
	     ======================== -->
	<section class="swift-products-section">
		<div class="swift-container">
			<h2 class="swift-section-title"><?php esc_html_e( 'সকল পণ্য', 'swift-woo' ); ?></h2>
			<?php
			// Use official WooCommerce shortcode rendering for hook integrity
			echo do_shortcode( '[products limit="16" columns="4" orderby="date" order="DESC"]' );
			?>
		</div>
	</section>

	<?php else : ?>
	<!-- Standard Page / Blog content -->
	<div class="swift-container">
		<?php
		while ( have_posts() ) :
			the_post();
			get_template_part( 'template-parts/content', get_post_type() );
		endwhile;
		?>
	</div>
	<?php endif; ?>

</main>
<?php get_footer(); ?>
