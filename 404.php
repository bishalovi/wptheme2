<?php
defined( 'ABSPATH' ) || exit;
get_header();
?>
<main id="main" class="swift-main" role="main">
<div class="swift-container">
	<h1><?php esc_html_e( 'পেজটি পাওয়া যায়নি', 'swift-woo' ); ?></h1>
	<p><?php esc_html_e( 'আপনি যা খুঁজছেন তা হয়তো সরিয়ে ফেলা হয়েছে বা URL ভুল। পণ্য খুঁজুন:', 'swift-woo' ); ?></p>
	<?php get_search_form(); ?>
	<a href="<?php echo esc_url( swift_woo_get_wc_url( 'shop' ) ); ?>" class="swift-btn swift-btn--primary">
		<?php esc_html_e( 'শপিং চালিয়ে যান', 'swift-woo' ); ?>
	</a>
</div>
</main>
<?php get_footer(); ?>
