<?php
defined( 'ABSPATH' ) || exit;
?>
<footer class="swift-footer" role="contentinfo">
	<div class="swift-footer__inner">
		<?php
		$footer_text = wp_kses_post( get_theme_mod( 'swift_footer_text', '' ) );
		if ( $footer_text ) {
			echo $footer_text;
		} else {
			echo '<p>&copy; ' . esc_html( date( 'Y' ) ) . ' ' . esc_html( get_bloginfo( 'name' ) ) . '. ' . esc_html__( 'All rights reserved.', 'swift-woo' ) . '</p>';
		}
		?>
	</div>
</footer>
<?php wp_footer(); // OFFICIAL HOOK — never removed ?>
</body>
</html>
