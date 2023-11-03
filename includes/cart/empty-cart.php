<?php

add_action( 'edd_cart_contents_loaded', 'ma_empty_cart_template' );

function ma_empty_cart_template() {
	var_dump( is_page( 631 ) );
	echo 'test';
}
