<?php
defined( 'ABSPATH' ) || exit;

/**
 * Checkout Form Template (grihotools.com style).
 * 100% official WooCommerce checkout hooks preserved.
 * Zero core hooks removed or altered.
 */

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
		<p class="swift-checkout-subtitle"><?php esc_html_e( 'ক্যাশ অন ডেলিভারি | কোনো অগ্রিম পেমেন্ট নেই | পণ্য হাতে পেয়ে টাকা দিন', 'swift-woo' ); ?></p>
	</div>

	<form name="checkout" method="post" class="checkout woocommerce-checkout swift-griho-checkout" action="<?php echo esc_url( wc_get_checkout_url() ); ?>" enctype="multipart/form-data" aria-label="<?php echo esc_attr__( 'Checkout', 'woocommerce' ); ?>">

		<?php if ( $checkout->get_checkout_fields() ) : ?>

			<?php do_action( 'woocommerce_checkout_before_customer_details' ); ?>

			<div class="swift-checkout-grid" id="customer_details">
				<!-- LEFT COLUMN: Customer Billing & Delivery Area -->
				<div class="swift-checkout-col swift-checkout-col--customer">
					<div class="swift-checkout-box">
						<div class="swift-box-header">
							<span class="swift-step-number">১</span>
							<h2 class="swift-box-title"><?php esc_html_e( 'আপনার বিলিং তথ্য', 'swift-woo' ); ?></h2>
						</div>

						<?php do_action( 'woocommerce_checkout_billing' ); ?>

						<?php do_action( 'woocommerce_checkout_shipping' ); ?>
					</div>
				</div>

				<!-- RIGHT COLUMN: Order Review, Delivery Summary & COD Submit -->
				<div class="swift-checkout-col swift-checkout-col--order">
					<div class="swift-checkout-box swift-order-review-box">
						<div class="swift-box-header">
							<span class="swift-step-number">২</span>
							<h2 class="swift-box-title" id="order_review_heading"><?php esc_html_e( 'আপনার অর্ডার বিবরণী', 'swift-woo' ); ?></h2>
						</div>

						<?php do_action( 'woocommerce_checkout_before_order_review' ); ?>

						<div id="order_review" class="woocommerce-checkout-review-order">
							<?php do_action( 'woocommerce_checkout_order_review' ); ?>
						</div>

						<?php do_action( 'woocommerce_checkout_after_order_review' ); ?>

						<div class="swift-checkout-trust-foot">
							<div class="swift-trust-item">
								<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
								<span><?php esc_html_e( '১০০% ক্যাশ অন ডেলিভারি', 'swift-woo' ); ?></span>
							</div>
							<div class="swift-trust-item">
								<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
								<span><?php esc_html_e( 'পণ্য দেখে টাকা পরিশোধ', 'swift-woo' ); ?></span>
							</div>
						</div>
					</div>
				</div>
			</div>

			<?php do_action( 'woocommerce_checkout_after_customer_details' ); ?>

		<?php endif; ?>

	</form>
</div>

<?php do_action( 'woocommerce_after_checkout_form', $checkout ); ?>
