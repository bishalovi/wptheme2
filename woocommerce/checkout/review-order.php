<?php
defined( 'ABSPATH' ) || exit;

/**
 * Review Order Template (GrihoTools Bengali Style).
 * Displays product thumbnail images, Bengali labels, and clean totals.
 * 100% official WooCommerce hooks preserved. Zero hooks altered or removed.
 */
?>
<table class="shop_table woocommerce-checkout-review-order-table swift-review-order-table">
	<thead>
		<tr>
			<th class="product-name"><?php esc_html_e( 'পণ্য', 'swift-woo' ); ?></th>
			<th class="product-total"><?php esc_html_e( 'মূল্য', 'swift-woo' ); ?></th>
		</tr>
	</thead>
	<tbody>
		<?php
		do_action( 'woocommerce_review_order_before_cart_contents' );

		foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
			$_product = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );

			if ( $_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters( 'woocommerce_checkout_cart_item_visible', true, $cart_item, $cart_item_key ) ) {
				$thumbnail = $_product->get_image( array( 56, 56 ), array( 'class' => 'swift-order-item__img' ) );
				?>
				<tr class="<?php echo esc_attr( apply_filters( 'woocommerce_cart_item_class', 'cart_item swift-cart-item-row', $cart_item, $cart_item_key ) ); ?>">
					<td class="product-name">
						<div class="swift-order-product-wrap">
							<div class="swift-order-product-thumb">
								<?php echo $thumbnail; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
								<span class="swift-order-product-qty"><?php echo esc_html( $cart_item['quantity'] ); ?></span>
							</div>
							<div class="swift-order-product-details">
								<span class="swift-order-product-name">
									<?php echo wp_kses_post( apply_filters( 'woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key ) ); ?>
								</span>
								<?php echo wc_get_formatted_cart_item_data( $cart_item ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							</div>
						</div>
					</td>
					<td class="product-total">
						<?php echo apply_filters( 'woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal( $_product, $cart_item['quantity'] ), $cart_item, $cart_item_key ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</td>
				</tr>
				<?php
			}
		}

		do_action( 'woocommerce_review_order_after_cart_contents' );
		?>
	</tbody>
	<tfoot>

		<tr class="cart-subtotal">
			<th><?php esc_html_e( 'সাবটোটাল', 'swift-woo' ); ?></th>
			<td><?php wc_cart_totals_subtotal_html(); ?></td>
		</tr>

		<?php foreach ( WC()->cart->get_coupons() as $code => $coupon ) : ?>
			<tr class="cart-discount coupon-<?php echo esc_attr( sanitize_title( $code ) ); ?>">
				<th><?php wc_cart_totals_coupon_label( $coupon ); ?></th>
				<td><?php wc_cart_totals_coupon_html( $coupon ); ?></td>
			</tr>
		<?php endforeach; ?>

		<?php if ( WC()->cart->needs_shipping() && WC()->cart->show_shipping() ) : ?>

			<?php do_action( 'woocommerce_review_order_before_shipping' ); ?>

			<?php wc_cart_totals_shipping_html(); ?>

			<?php do_action( 'woocommerce_review_order_after_shipping' ); ?>

		<?php endif; ?>

		<?php foreach ( WC()->cart->get_fees() as $fee ) : ?>
			<tr class="fee">
				<th><?php echo esc_html( $fee->name ); ?></th>
				<td><?php wc_cart_totals_fee_html( $fee ); ?></td>
			</tr>
		<?php endforeach; ?>

		<?php if ( wc_tax_enabled() && ! WC()->cart->display_prices_including_tax() ) : ?>
			<?php if ( 'itemized' === get_option( 'woocommerce_tax_total_display' ) ) : ?>
				<?php foreach ( WC()->cart->get_tax_totals() as $code => $tax ) : // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited ?>
					<tr class="tax-rate tax-rate-<?php echo esc_attr( sanitize_title( $code ) ); ?>">
						<th><?php echo esc_html( $tax->label ); ?></th>
						<td><?php echo wp_kses_post( $tax->formatted_amount ); ?></td>
					</tr>
				<?php endforeach; ?>
			<?php else : ?>
				<tr class="tax-total">
					<th><?php echo esc_html( WC()->countries->tax_or_vat() ); ?></th>
					<td><?php wc_cart_totals_taxes_total_html(); ?></td>
				</tr>
			<?php endif; ?>
		<?php endif; ?>

		<?php do_action( 'woocommerce_review_order_before_order_total' ); ?>

		<tr class="order-total">
			<th><?php esc_html_e( 'সর্বমোট', 'swift-woo' ); ?></th>
			<td><?php wc_cart_totals_order_total_html(); ?></td>
		</tr>

		<?php do_action( 'woocommerce_review_order_after_order_total' ); ?>

	</tfoot>
</table>
