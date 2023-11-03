<?php

// add_action( 'edd_cart_contents_loaded', 'ma_empty_cart_template' );
add_filter( 'the_content', 'ma_empty_cart_template', 1 );
function ma_empty_cart_template( $content ) {
	if ( is_page( 631 ) && ! did_action( 'edd_checkout_cart_item_title_after' ) ) {
		if ( ! edd_get_cart_contents() ) {
			$content = '<div class="edd-cart-empty">';
			$content .= '<h1>سبد خرید شما خالی است</h1>';
			$content .= '<img src="' . esc_url( EDD_CUSTOM_URL . 'assets/images/svg/empty-cart.svg' ) . '" alt="empty-cart" class="empty-cart">';
			$content .= '</div>';
		}
	}
	return $content;
}
