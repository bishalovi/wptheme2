<?php
defined( 'ABSPATH' ) || exit;
?>
<article id="post-<?php the_ID(); ?>" <?php post_class( 'swift-content-card' ); ?>>
	<?php if ( has_post_thumbnail() ) : ?>
	<a href="<?php the_permalink(); ?>" class="swift-content-card__thumb">
		<?php the_post_thumbnail( 'medium', array( 'loading' => 'lazy', 'decoding' => 'async' ) ); ?>
	</a>
	<?php endif; ?>
	<div class="swift-content-card__body">
		<?php the_title( '<h2 class="entry-title"><a href="' . esc_url( get_permalink() ) . '">', '</a></h2>' ); ?>
		<p class="entry-meta"><?php echo esc_html( get_the_date() ); ?></p>
		<div class="entry-excerpt"><?php the_excerpt(); ?></div>
	</div>
</article>
