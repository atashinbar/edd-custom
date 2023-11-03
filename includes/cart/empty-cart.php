<?php

add_action( 'edd_cart_contents_loaded', 'ma_empty_cart_template' );

function ma_empty_cart_template() {
	if ( is_page( 631 ) )
	echo '<img src="' . esc_url( EDD_CUSTOM_URL . 'assets/images/svg/empty-cart.svg' ) . '" alt="empty-cart" class="empty-cart">';
}
