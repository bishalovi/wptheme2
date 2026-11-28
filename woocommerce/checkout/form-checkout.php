<?php
/**
 * Checkout Form Template - 100% Official WooCommerce Standard Core Compatible.
 * GrihoTools Ultra-Compact 1-Screen Architecture.
 *
 * All 10 official WooCommerce checkout hooks remain 100% INTACT in their exact core positions.
 * Zero core hooks removed, zero actions unhooked, 100% Meta Pixel / PixelYourSite & Payment Gateway native support.
 *
 * @package SwiftWoo
 */

defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_before_checkout_form', $checkout );

// If checkout registration is disabled and not logged in, the user cannot checkout.
if ( ! $checkout->is_registration_enabled() && $checkout->is_registration_required() && ! is_user_logged_in() ) {
	echo esc_html( apply_filters( 'woocommerce_checkout_must_be_logged_in_message', __( 'You must be logged in to checkout.', 'woocommerce' ) ) );
	return;
}
?>

<div class="swift-checkout-container">
	<div class="swift-checkout-header-banner">
		<h1 class="swift-checkout-title"><?php esc_html_e( 'অর্ডারটি কনফার্ম করতে নিচের তথ্যগুলো পূরণ করুন', 'swift-woo' ); ?></h1>
		<p class="swift-checkout-subtitle"><?php esc_html_e( 'ক্যাশ অন ডেলিভারি | পণ্য দেখে টাকা পরিশোধ | দ্রুততম সময়ে হোম ডেলিভারি', 'swift-woo' ); ?></p>
	</div>

	<form name="checkout" method="post" class="checkout woocommerce-checkout swift-checkout-form" action="<?php echo esc_url( wc_get_checkout_url() ); ?>" enctype="multipart/form-data" aria-label="<?php echo esc_attr__( 'Checkout', 'woocommerce' ); ?>">

		<?php if ( $checkout->get_checkout_fields() ) : ?>

			<?php do_action( 'woocommerce_checkout_before_customer_details' ); ?>

			<div class="col2-set" id="customer_details">
				<div class="swift-box-header">
					<span class="swift-step-number">১</span>
					<h3 class="swift-box-title"><?php esc_html_e( 'কাস্টমার ইনফরমেশন', 'swift-woo' ); ?></h3>
				</div>

				<div class="col-1">
					<?php do_action( 'woocommerce_checkout_billing' ); ?>
				</div>

				<div class="col-2">
					<?php do_action( 'woocommerce_checkout_shipping' ); ?>
				</div>
			</div>

			<?php do_action( 'woocommerce_checkout_after_customer_details' ); ?>

		<?php endif; ?>

		<?php do_action( 'woocommerce_checkout_before_order_review_heading' ); ?>

		<h3 id="order_review_heading"><?php esc_html_e( 'আপনার অর্ডার বিবরণী', 'swift-woo' ); ?></h3>

		<?php do_action( 'woocommerce_checkout_before_order_review' ); ?>

		<div id="order_review" class="woocommerce-checkout-review-order">
			<div class="swift-box-header">
				<span class="swift-step-number">২</span>
				<h3 class="swift-box-title"><?php esc_html_e( 'আপনার অর্ডার বিবরণী', 'swift-woo' ); ?></h3>
			</div>
			<?php do_action( 'woocommerce_checkout_order_review' ); ?>
		</div>

		<?php do_action( 'woocommerce_checkout_after_order_review' ); ?>

	</form>
</div>

<?php do_action( 'woocommerce_after_checkout_form', $checkout ); ?>
