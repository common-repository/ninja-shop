<?php
$transaction = it_exchange_get_transaction( $GLOBALS['it_exchange']['transaction'] );
$taxes       = $transaction->get_items( 'tax', true );
$segmented   = $taxes->segment( function ( ITE_Line_Item $item ) {
	return get_class( $item ) . $item->get_name();
} );
?>
<?php foreach ( $segmented as $segment ): ?>
<div class="ninja-shop-table-row ninja-shop-cart-totals-taxes">
	<?php do_action( 'ninja_shop_content_confirmation_before_totals_taxes_simple_element' ); ?>
	<div class="ninja-shop-confirmation-totals-title ninja-shop-table-column">
		<?php do_action( 'ninja_shop_content_confirmation_begin_totals_taxes_simple_element_label' ); ?>
		<div class="ninja-shop-table-column-inner">
			<?php echo $segment->first()->get_name(); ?>
		</div>
		<?php do_action( 'ninja_shop_content_confirmation_end_totals_taxes_simple_element_label' ); ?>
	</div>
	<div class="ninja-shop-confirmation-totals-amount ninja-shop-table-column">
		<?php do_action( 'ninja_shop_content_confirmation_begin_totals_taxes_simple_element_value' ); ?>
		<div class="ninja-shop-table-column-inner">
			<?php echo it_exchange_format_price( $segment->total() ); ?>
		</div>
		<?php do_action( 'ninja_shop_content_confirmation_end_totals_taxes_simple_element_value' ); ?>
	</div>
	<?php do_action( 'ninja_shop_content_confirmation_after_totals_taxes_simple_element' ); ?>
</div>
<?php endforeach; ?>
