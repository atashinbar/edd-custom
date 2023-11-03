<?php

add_action( 'edd_cart_contents_loaded', 'ma_empty_cart_template' );

function ma_empty_cart_template() {
	var_dump( edd_get_cart_contents() );
	echo 'test';
}
