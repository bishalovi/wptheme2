<?php
defined( 'ABSPATH' ) || exit;
/**
 * WooCommerce Product Loop Item template override.
 * File: woocommerce/loop/loop-start.php
 *
 * IMPORTANT: All official WooCommerce loop hooks remain intact.
 * Only visual/CSS changes are applied.
 */
?>
<ul class="products swift-products-grid columns-<?php echo esc_attr( wc_get_loop_prop( 'columns' ) ); ?>">
