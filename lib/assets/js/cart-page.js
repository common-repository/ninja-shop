(function ( $ ) {
	$( '.product-cart-quantity' ).change( function( e ) {
		$( '.ninja-shop-checkout-cart' ).addClass("ninja-shop-hidden");
		$( '.ninja-shop-update-cart' ).removeClass("ninja-shop-hidden");
	} );
})( jQuery );
