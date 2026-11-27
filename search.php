<?php
defined( 'ABSPATH' ) || exit;
get_header();
?>
<main id="main" class="swift-main" role="main">
<div class="swift-container">
	<h1>
		<?php
		if ( have_posts() ) {
			/* translators: %s: search query */
			printf( esc_html__( '"%s" এর সার্চ ফলাফল', 'swift-woo' ), '<span>' . get_search_query() . '</span>' );
		} else {
			esc_html_e( 'কোনো ফলাফল পাওয়া যায়নি', 'swift-woo' );
		}
		?>
	</h1>
	<?php
	if ( have_posts() ) :
		while ( have_posts() ) :
			the_post();
			get_template_part( 'template-parts/content', get_post_type() );
		endwhile;
		the_posts_pagination();
	else :
		?>
		<p><?php esc_html_e( 'আপনার সার্চে কিছু পাওয়া যায়নি। আবার চেষ্টা করুন:', 'swift-woo' ); ?></p>
		<?php get_search_form(); ?>
		<a href="<?php echo esc_url( swift_woo_get_wc_url( 'shop' ) ); ?>" class="swift-btn swift-btn--primary">
			<?php esc_html_e( 'শপিং চালিয়ে যান', 'swift-woo' ); ?>
		</a>
		<?php
	endif;
	?>
</div>
</main>
<?php get_footer(); ?>
