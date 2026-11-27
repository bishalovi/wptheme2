<?php
defined( 'ABSPATH' ) || exit;
get_header();
?>
<main id="main" class="swift-main" role="main">
<div class="swift-container">
	<?php
	while ( have_posts() ) :
		the_post();
		?>
		<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
			<header class="entry-header">
				<?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>
				<p class="entry-meta"><?php echo esc_html( get_the_date() ); ?></p>
			</header>
			<?php if ( has_post_thumbnail() ) : ?>
			<div class="entry-thumbnail">
				<?php the_post_thumbnail( 'large', array( 'loading' => 'eager' ) ); ?>
			</div>
			<?php endif; ?>
			<div class="entry-content">
				<?php
				the_content();
				wp_link_pages();
				?>
			</div>
		</article>
		<?php
	endwhile;
	?>
</div>
</main>
<?php get_footer(); ?>
