<?php
// Just adding internal CSS rule here since it won't be around long.
?>
<div id="it-exchange-database-rename-nag" class="it-exchange-nag">
  <?php echo __( 'The Ninja Shop database structure has been changed.' ); ?>
	<a class="dismiss btn" href="<?php echo esc_url( $dismiss_url ); ?>">&times;</a>
</div>
<script type="text/javascript">
	jQuery( document ).ready( function() {
		if ( jQuery( '.wrap > h2' ).length == '1' ) {
			jQuery("#it-exchange-database-rename-nag").insertAfter('.wrap > h2').addClass( 'after-h2' );
		}
	});
</script>
