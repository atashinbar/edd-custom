<?php

add_action( 'edd_cart_contents_loaded', 'ma_empty_cart_template' );
// add_filter( 'the_content', 'filter_the_content_in_the_main_loop', 1 );
function ma_empty_cart_template() {
	if ( is_page( 631 ) && ! did_action( 'edd_checkout_cart_item_title_after' ) ) {
		echo '<img src="' . esc_url( EDD_CUSTOM_URL . 'assets/images/svg/empty-cart.svg' ) . '" alt="empty-cart" class="empty-cart">';
	}
}
