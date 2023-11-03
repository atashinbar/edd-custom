<?php

add_action( 'kata_page_after_the_content', 'ma_empty_cart_template' );
// add_filter( 'the_content', 'filter_the_content_in_the_main_loop', 1 );
function filter_the_content_in_the_main_loop() {
	if ( is_page( 631 ) ) {
		var_dump( has_action( 'edd_cart_items_before' ) );
		echo '<img src="' . esc_url( EDD_CUSTOM_URL . 'assets/images/svg/empty-cart.svg' ) . '" alt="empty-cart" class="empty-cart">';
	}
}
