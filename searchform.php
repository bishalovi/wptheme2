<?php
defined( 'ABSPATH' ) || exit;
?>
<form role="search" method="get" class="swift-search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<label for="swift-searchform-input" class="screen-reader-text"><?php esc_html_e( 'খুঁজুন:', 'swift-woo' ); ?></label>
	<input
		type="search"
		id="swift-searchform-input"
		class="swift-search-input"
		name="s"
		value="<?php echo get_search_query(); ?>"
		placeholder="<?php esc_attr_e( 'পণ্য খুঁজুন...', 'swift-woo' ); ?>"
		aria-label="<?php esc_attr_e( 'খুঁজুন', 'swift-woo' ); ?>"
	>
	<input type="hidden" name="post_type" value="product">
	<button type="submit" class="swift-search-submit swift-btn swift-btn--primary" aria-label="<?php esc_attr_e( 'সার্চ করুন', 'swift-woo' ); ?>">
		<?php esc_html_e( 'সার্চ', 'swift-woo' ); ?>
	</button>
</form>
