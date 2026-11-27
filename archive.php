<?php
defined( 'ABSPATH' ) || exit;
get_header();
?>
<main id="main" class="swift-main" role="main">
<div class="swift-container">
	<header class="page-header">
		<?php the_archive_title( '<h1 class="page-title">', '</h1>' ); ?>
		<?php the_archive_description( '<div class="archive-description">', '</div>' ); ?>
	</header>
	<?php
	if ( have_posts() ) :
		while ( have_posts() ) :
			the_post();
			get_template_part( 'template-parts/content', get_post_type() );
		endwhile;
		the_posts_pagination();
	else :
		?>
		<p><?php esc_html_e( 'কোনো পোস্ট পাওয়া যায়নি।', 'swift-woo' ); ?></p>
		<?php
	endif;
	?>
</div>
</main>
<?php get_footer(); ?>
