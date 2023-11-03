<?php

add_action( 'kata_page_after_the_content', 'ma_empty_cart_template' );

function ma_empty_cart_template() {
	if ( is_page( 631 ) )
	echo '<img src="' . esc_url( EDD_CUSTOM_URL . 'assets/images/svg/empty-cart.svg' ) . '" alt="empty-cart" class="empty-cart">';
}
