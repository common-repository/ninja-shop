<?php

$cart      = it_exchange_get_current_cart();
$segmented = $cart->get_items( 'tax', true )->segment( function ( ITE_Line_Item $item ) {
	return get_class( $item ) . $item->get_name();
} );
?>
<?php do_action( 'ninja_shop_content_cart_before_totals_taxes_element' ); ?>

<?php foreach ( $segmented as $segment ) : ?>
<div class="ninja-shop-table-row ninja-shop-cart-totals-taxes">
	<div class="ninja-shop-cart-totals-title ninja-shop-table-column">
		<?php do_action( 'ninja_shop_content_cart_begin_totals_taxes_element_label' ); ?>
		<div class="ninja-shop-table-column-inner">
			<?php do_action( 'ninja_shop_content_cart_begin_totals_taxes_inner_element_label' ); ?>
			<?php echo $segment->first()->get_name(); ?>
			<?php do_action( 'ninja_shop_content_cart_end_totals_taxes_inner_element_label' ); ?>
		</div>
		<?php do_action( 'ninja_shop_content_cart_end_totals_taxes_element_label' ); ?>
	</div>
	<div class="ninja-shop-cart-totals-amount ninja-shop-table-column">
		<?php do_action( 'ninja_shop_content_cart_begin_totals_taxes_element_value' ); ?>
		<div class="ninja-shop-table-column-inner">
			<?php do_action( 'ninja_shop_content_cart_begin_totals_taxes_inner_element_value' ); ?>
			<?php echo it_exchange_format_price( $segment->total() ); ?>
			<?php do_action( 'ninja_shop_content_cart_end_totals_taxes_inner_element_value' ); ?>
		</div>
		<?php do_action( 'ninja_shop_content_cart_end_totals_taxes_element_value' ); ?>
	</div>
</div>
<?php endforeach; ?>
<?php do_action( 'ninja_shop_content_cart_after_totals_taxes_element' ); ?>
