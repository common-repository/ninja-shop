<?php
/**
 * This file contains the notice for the upgrades available notice.
 * @package IT_Exchange
 *
 */
?>
<div id="ninja-shop-upgrades-available-nag" class="it-exchange-nag">
	<?php printf(
		__( 'Ninja Shop needs to perform an %supgrade%s.' ),
		'<a href="' . esc_url( $upgrades_url ) . '">', '</a>' ) ?>
	<a class="dismiss btn" href="<?php echo esc_url( $dismiss_url ); ?>">&times;</a>
</div>
<script type="text/javascript">
	jQuery( document ).ready( function () {
		if ( jQuery( '.wrap > h1' ).length == '1' ) {
			jQuery( "#ninja-shop-upgrades-available-nag" ).insertAfter( '.wrap > h1' ).addClass( 'after-h2' );
		}
	} );
</script>
