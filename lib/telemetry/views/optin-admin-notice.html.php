<div id="ninja-shop-telemetry-optin-nag" class="it-exchange-nag">
	<?php echo $message; ?>
	<a class="dismiss btn" href="<?php echo esc_url( $dismiss_url ); ?>">&times;</a>
</div>
<script type="text/javascript">
	jQuery( document ).ready( function () {
		if ( jQuery( '.wrap > h1' ).length == '1' ) {
			jQuery( "#ninja-shop-telemetry-optin-nag" ).insertAfter( '.wrap > h1' ).addClass( 'after-h2' );
		}
	} );
</script>
