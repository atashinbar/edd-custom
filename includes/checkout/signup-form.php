<?php
/**
 * Adding a custom field to the checkout screen
 *
 * Covers:
 *
 * Adding a phone number field to the checkout
 * Making the phone number field required
 * Setting an error when the phone number field is not filled out
 * Storing the phone number into the payment meta
 * Adding the customer's phone number to the "view order details" screen
 * Adding a new {phone} email tag so you can display the phone number in the email notifications (standard purchase receipt or admin notification)
 */
defined( 'ABSPATH' ) || exit;

/**
 * Display phone number field at checkout
 * Add more here if you need to
 */

add_action( 'wp_ajax_send_mobile_verification_sms', 'send_mobile_verification_sms' );
add_action( 'wp_ajax_nopriv_send_mobile_verification_sms', 'send_mobile_verification_sms' );

function send_mobile_verification_sms() {
    if ( ! wp_verify_nonce( $_POST['nonce'], 'sendcodetaeed' ) ) {
        wp_send_json( 'این درخواست مجاز نیست', 403 );
    }
    session_start();

    $pin = generatePIN();
    $phone = sanitize_text_field( $_POST['phone'] );
    $_SESSION['phone'] = $phone;
    $_SESSION['sms_code'] = $pin;

    $status = sendCodeMelliPayamak( $phone, '165925', $pin );

    if ( isset( $status->StrRetStatus ) && $status->StrRetStatus === 'Ok' ) {
        wp_send_json( 'کد تایید ارسال شد', 200 );
    }

    wp_send_json( 'کد تایید ارسال نشد لطفا 30 ثانیه بعد تلاش کنید', 403 );

    wp_die();
}

function is_edd_checkout() {
    if (class_exists('Easy_Digital_Downloads')) {
        // Check if EDD is active
        global $edd_options;

        if (is_page() && isset($edd_options['purchase_page'])) {
            // Check if the current page is the EDD purchase/checkout page
            return is_page($edd_options['purchase_page']);
        }
    }
    return false;
}

function footer_script() {
	if ( is_edd_checkout() ) :
		?>
		<script>
			(function($){
				$( ".sendcodetaeed" ).on( "click", function(e) {
					e.preventDefault();
					var $this = $(this),
						$wrap = $this.closest('.taeed-phone'),
						$phone_number = $wrap.find('[name="edd_phone"]').val();
						$nonce = $wrap.find('[name="edd_phone"]').attr('data-nonce');

					if ( $phone_number.length === 11 ) {
						$this.attr('disabled', true);
						$this.find('.loader').show();
						$.ajax({
							type: "POST",
							dataType: "json",
							url: "<?php echo admin_url( 'admin-ajax.php' ); ?>",
							data: {
								nonce: $nonce,
								action: 'send_mobile_verification_sms',
								phone: $phone_number
							},
							success: function (response) {
								$('#edd-phone-taeed-wrap').fadeIn();
								$('#edd-phone-wrap').fadeOut();
								$this.removeAttr('disabled');
								$this.find('.loader').hide();

								// Start the countdown when the document is ready
								countdown(120);

							},
							error: function (jqXHR, textStatus, errorThrown) {
								$this.removeAttr('disabled');
								$this.find('.loader').hide();
								if ( jqXHR.status !== 200 ) {
									alert( jqXHR.responseJSON );
								}
							}
						});
					}
				} );


				function countdown(number) {
					if (number >= 0) {
						// Update the content of the span with the current number
						$('span.counter').text(number);

						// Decrement the number and recursively call the countdown function
						setTimeout(function() {
							countdown(number - 1);
						}, 1000); // Change the delay (in milliseconds) as needed
					} else {
						// Stop the countdown when the number reaches 0
						$('#edd-phone-wrap').fadeIn();
						$('#edd-phone-taeed-wrap').fadeOut();
						// $('.sendcodetaeed').removeAttr('disabled')
						// $('.sendcodetaeed').text('ارسال کد تایید شماره موبایل')
					}
				}
			})(jQuery);
		</script>

		<style>
			.wp-block-edd-checkout#edd_checkout_form_wrap label {
				color: #000;
				font-size: 20px;
				font-family:'Vazirmatn',Arial,Roboto;
				margin-bottom: 20px;
			}
			.wp-block-edd-checkout#edd_checkout_form_wrap input[type=email],
			.wp-block-edd-checkout#edd_checkout_form_wrap input[type=password],
			.wp-block-edd-checkout#edd_checkout_form_wrap input[type=text],
			.wp-block-edd-checkout#edd_checkout_form_wrap select,
			.wp-block-edd-checkout#edd_checkout_form_wrap [type="tel"] {
				border: 1px solid #aaa !important;
				border-radius: 6px !important;
				background: #FCFCFC !important;
				padding: 10px !important;
				box-sizing: unset !important;
				font-size: 17px;
				font-family:'Vazirmatn',Arial,Roboto;
			}

			.wp-block-edd-checkout#edd_checkout_form_wrap .edd-submit, .sendcodetaeed {
				background: #487bff;
				height: 50px;
				padding: 0 30px;
				position: relative;
				border-radius: 12px;
				margin-top: 10px;
				margin-left: 10px;
				box-shadow: none !important;
				border: none;
				color: #fff;
				font-family:'Vazirmatn',Arial,Roboto;
				cursor: pointer;
				transition: all .3s ease;
			}

			.sendcodetaeed:disabled {
				background: #b3b3b3;
				cursor: no-drop;
				pointer-events: none;
			}

			.wp-block-edd-checkout#edd_checkout_form_wrap .edd-submit:hover, .sendcodetaeed:hover {
				background: #ffab48;
			}

			.sendcodetaeed:before {
				content: "";
				width: 100%;
				background: transparent;
				display: block;
				height: 47px;
				position: absolute;
				top: -5px;
				left: -9px;
				border-radius: 12px;
				border: 2px solid #15121d;
			}

			#edd_purchase_form form input::-webkit-input-placeholder { /* Chrome/Opera/Safari */
				color: #aaa;
				font-size: 14px;
			}
			#edd_purchase_form form input::-moz-placeholder { /* Firefox 19+ */
				color: #aaa;
				font-size: 14px;
			}
			#edd_purchase_form form input:-ms-input-placeholder { /* IE 10+ */
				color: #aaa;
				font-size: 14px;
			}
			#edd_purchase_form form input:-moz-placeholder { /* Firefox 18- */
				color: #aaa;
				font-size: 14px;
			}

			.loader {
				width: 18px;
				height: 18px;
				border: 2px solid #FFF;
				border-bottom-color: transparent;
				border-radius: 50%;
				display: inline-block;
				box-sizing: border-box;
				animation: rotation 1s linear infinite;
			}

			@keyframes rotation {
				0% {
					transform: rotate(0deg);
				}
				100% {
					transform: rotate(360deg);
				}
			}
		</style>
		<?php
	endif;
}
add_action( 'wp_footer', 'footer_script', 9999 );

function custom_edd_display_checkout_custom_fields() {
    ?>
	<script>
        function checkPasswordStrength() {
            const password = document.getElementById("edd-password").value;
            const passwordStrength = document.getElementById("password-strength");

            // Define your password strength criteria
            const minLength = 8; // Minimum length
            const minUpperCase = 1; // Minimum uppercase characters
            const minLowerCase = 1; // Minimum lowercase characters
            const minDigits = 1; // Minimum digits
            const minSpecialChars = 1; // Minimum special characters

            // Regular expressions for checking criteria
            const upperCaseRegex = /[A-Z]/;
            const lowerCaseRegex = /[a-z]/;
            const digitRegex = /\d/;
            const specialCharRegex = /[!@#$%^&*()_+{}\[\]:;<>,.?~\\-]/;

            // Check each criteria and update the feedback
            const isLengthValid = password.length >= minLength;
            const isUpperCaseValid = (password.match(upperCaseRegex) || []).length >= minUpperCase;
            const isLowerCaseValid = (password.match(lowerCaseRegex) || []).length >= minLowerCase;
            const isDigitValid = (password.match(digitRegex) || []).length >= minDigits;
            const isSpecialCharValid = (password.match(specialCharRegex) || []).length >= minSpecialChars;

            if (isLengthValid && isUpperCaseValid && isLowerCaseValid && isDigitValid && isSpecialCharValid) {
                passwordStrength.innerHTML = "Strong password!";
                passwordStrength.style.color = "green";
            } else {
                passwordStrength.innerHTML = "رمز عبور باید شامل " +
                    " حروف بزرگ,  " +
                    " حروف کوچک, " +
                    " اعداد, " +
                    " علائم مثل @!% " +
					" و حداقل شامل " +
                    minLength + " تعداد کاراکتر. ";
                passwordStrength.style.color = "red";
            }
        }
    </script>
	<?php if ( ! is_user_logged_in() ) : ?>

		<p id="edd-password-wrap">
			<label class="edd-label" for="edd-password">
			<?php esc_html_e( 'رمز عبور', 'easy-digital-downloads' ); ?>
			<?php if ( edd_field_is_required( 'edd_password' ) ) : ?>
				<span class="edd-required-indicator">*</span>
			<?php endif; ?>
			</label>
			<input onkeyup="checkPasswordStrength()" class="edd_password edd-input<?php if ( edd_field_is_required( 'edd_password' ) ) { echo ' اجباری'; } ?>" type="text" name="edd_password" id="edd-password" placeholder="<?php _e( 'رمز عبور', 'easy-digital-downloads' ); ?>">
			<div id="password-strength"></div>
		</p>
		<div class="taeed-phone">
			<p id="edd-phone-wrap">
				<label class="edd-label" for="edd-phone">
				<?php esc_html_e( 'شماره موبایل', 'easy-digital-downloads' ); ?>
				<?php if ( edd_field_is_required( 'edd_phone' ) ) : ?>
					<span class="edd-required-indicator">*</span>
				<?php endif; ?>
				</label>
				<input data-nonce="<?php echo wp_create_nonce( 'sendcodetaeed' ); ?>" class="edd_phone edd-input<?php if ( edd_field_is_required( 'edd_phone' ) ) { echo ' اجباری'; } ?>" type="tel" name="edd_phone" id="edd-phone" pattern="[0-9]{11}" placeholder="<?php _e( 'فرمت: 09121234567', 'easy-digital-downloads' ); ?>">
				<button href="#" class="sendcodetaeed">
					<span class="loader" style="display: none;"></span>
					ارسال کد تایید شماره موبایل
				</button>
			</p>
			<p id="edd-phone-taeed-wrap" style="display:none;">
				<label class="edd-label" for="edd-phone">
				<?php esc_html_e( 'کد تایید را وارد کنید', 'easy-digital-downloads' ); ?>
				<?php if ( edd_field_is_required( 'edd_phone_taeed' ) ) : ?>
					<span class="edd-required-indicator">*</span>
				<?php endif; ?>
				</label>
				<input class="edd_phone_taeed edd-input<?php if ( edd_field_is_required( 'edd_phone_taeed' ) ) { echo ' اجباری'; } ?>" type="tel" name="edd_phone_taeed" id="edd-phone" pattern="[0-9]{6}" >
				<button href="#" class="sendcodetaeed" disabled>
					<span class="loader" style="display: none;"></span>
					ارسال مجدد بعدی از
					<span class="counter"></span>
					ثانیه
				</button>
			</p>
		</div>
    <?php
		endif;
}
add_action( 'edd_purchase_form_user_info_fields', 'custom_edd_display_checkout_custom_fields' );

/**
 * Make phone number required
 * Add more required fields here if you need to
 */
function custom_edd_required_checkout_fields( $required_fields ) {
	if ( ! is_user_logged_in() ) {
		$required_fields['edd_phone'] = array(
			'error_id' => 'invalid_phone',
			'error_message' => 'لطفا یک شماره موبایل معتبر وارد کنید'
		);
		$required_fields['edd_phone_taeed'] = array(
			'error_id' => 'invalid_edd_phone_taeed',
			'error_message' => 'کد تایید صحیح نمیباشد'
		);
		$required_fields['edd_password'] = array(
			'error_id' => 'invalid_password',
			'error_message' => 'رمز عبور اجباری است'
		);
	}

	// disable billing form
	unset( $required_fields['card_zip'] );
	unset( $required_fields['card_city'] );
	unset( $required_fields['billing_country'] );
	unset( $required_fields['card_state'] );

    return $required_fields;
}
add_filter( 'edd_purchase_form_required_fields', 'custom_edd_required_checkout_fields' );

/**
 * Set error if phone number field is empty
 * You can do additional error checking here if required
 */
function custom_edd_validate_checkout_fields( $valid_data, $data ) {

	if ( ! is_user_logged_in() ) {
		if ( username_exists( $data['edd_phone'] ) ) {
			edd_set_error( 'invalid_phone', 'قبلا با این شماره موبایل در سایت ثبت نام صورت گرفته است لطفا اول وارد شودی' );
		}

		if ( email_exists( $data['edd_email'] ) ) {
			edd_set_error( 'invalid_email', 'قبلا با این شماره ایمیل در سایت ثبت نام صورت گرفته است' );
		}

		if ( empty( $data['edd_phone'] ) && ! is_numeric( $data['edd_phone'] ) ) {
			edd_set_error( 'invalid_phone', 'شماره موبایل معتبر نیست لطفا مجدد تلاش کنید.' );
		}

		if ( empty( $data['edd_phone_taeed'] ) ) {
			edd_set_error( 'invalid_edd_phone_taeed', 'شماره موبایل تایید نشده است.' );
		} else {
			if ( $_SESSION['sms_code'] !== $data['edd_phone_taeed'] ) {
				edd_set_error( 'invalid_edd_phone_taeed', 'کد تایید صحیح نمیباشد' );
			}
		}

		if ( empty( $data['edd_password'] ) ) {
			edd_set_error( 'invalid_password', 'رمز عبور اجباری است' );
		}

		if ( ! empty( $data['edd_password'] ) && ! is_strong_password( $data['edd_password'] ) ) {
			edd_set_error( 'invalid_password', 'رمز عبور قوی نیست' );
		}
	}
}
add_action( 'edd_checkout_error_checks', 'custom_edd_validate_checkout_fields', 10, 2 );

/**
 * Store the custom field data into EDD's order mtea
 */
function custom_edd_store_custom_fields( $order_id, $order_data ) {

	if ( did_action( 'edd_pre_process_purchase' ) && ! is_user_logged_in() ) {
		$username		= isset( $_POST['edd_phone'] ) ? sanitize_text_field( $_POST['edd_phone'] ) : '';
		$password		= isset( $_POST['edd_password'] ) ? sanitize_text_field( $_POST['edd_password'] ) : '';
		$email			= isset( $_POST['edd_email'] ) ? sanitize_text_field( $_POST['edd_email'] ) : '';
		$phone			= isset( $_POST['edd_phone'] ) ? sanitize_text_field( $_POST['edd_phone'] ) : '';
		$phone_taeed    = isset( $_POST['edd_phone_taeed'] ) ? sanitize_text_field( $_POST['edd_phone_taeed'] ) : '';
		$display_name	= isset( $_POST['edd_first'] ) ? sanitize_text_field( $_POST['edd_first'] ) : '';

		create_new_wordpress_user( $username, $password, $email, $phone, $display_name );
		edd_add_order_meta( $order_id, 'phone', $phone );
	}

}
add_action( 'edd_built_order', 'custom_edd_store_custom_fields', 10, 2 );

/**
 * Add the phone number to the "View Order Details" page
 */
function custom_edd_view_order_details( $order_id ) {
	$phone = edd_get_order_meta( $order_id, 'phone', true );
?>
	<div class="column-container">
		<div class="column">
			<strong>شماره موبایل: </strong>
			<?php echo esc_html( $phone ); ?>
		</div>
	</div>
 <?php
}
add_action( 'edd_payment_view_details', 'custom_edd_view_order_details', 10, 1 );

/**
 * Add a {phone} tag for use in either the purchase receipt email or admin notification emails
 */
function custom_edd_add_email_tag() {

	edd_add_email_tag( 'phone', 'شماره موبایل مشتری', 'custom_edd_email_tag_phone' );
}
add_action( 'edd_add_email_tags', 'custom_edd_add_email_tag' );

/**
 * The {phone} email tag
 */
function custom_edd_email_tag_phone( $payment_id ) {
	$phone = edd_get_order_meta( $payment_id, 'phone', true );
	return $phone;
}

function create_new_wordpress_user( $username, $password, $email, $phone, $display_name = '' ) {
    // Check if the username or email already exists
    if ( username_exists( $username ) || email_exists( $email ) ) {
        return new WP_Error( 'registration-error', 'قبلا با این شماره موبایل در سایت ثبت نام صورت گرفته است' );
    }

    // Create the user
    $user_id = wp_create_user( $username, $password, $email );

    if ( is_wp_error( $user_id ) ) {
        return $user_id; // Return the error if user creation failed
    }

    // Update the display name (optional)
    if ( ! empty( $display_name ) ) {
        wp_update_user( array( 'ID' => $user_id, 'display_name' => $display_name ) );
    }

	// update user phone number
	update_user_meta( $user_id, 'edd_phone', $phone );

    // Optionally, you can add the user to a specific role
    $user = new WP_User( $user_id );
    $user->set_role( 'subscriber' ); // Change 'subscriber' to the desired role

    // Return the user ID on success
    return $user_id;
}

function is_strong_password( $password ) {
    // Define your password strength criteria
    $minLength = 8; // Minimum length
    $minUpperCase = 1; // Minimum uppercase characters
    $minLowerCase = 1; // Minimum lowercase characters
    $minDigits = 1; // Minimum digits
    $minSpecialChars = 1; // Minimum special characters

    // Regular expressions for checking criteria
    $upperCaseRegex = '/[A-Z]/';
    $lowerCaseRegex = '/[a-z]/';
    $digitRegex = '/\d/';
    $specialCharRegex = '/[!@#$%^&*()_+{}\[\]:;<>,.?~\\-]/';

    // Check each criteria
    $isLengthValid = strlen($password) >= $minLength;
    $isUpperCaseValid = preg_match_all($upperCaseRegex, $password) >= $minUpperCase;
    $isLowerCaseValid = preg_match_all($lowerCaseRegex, $password) >= $minLowerCase;
    $isDigitValid = preg_match_all($digitRegex, $password) >= $minDigits;
    $isSpecialCharValid = preg_match_all($specialCharRegex, $password) >= $minSpecialChars;

    // Check if all criteria are met
    return $isLengthValid && $isUpperCaseValid && $isLowerCaseValid && $isDigitValid && $isSpecialCharValid;
}

function generatePIN( $digits = 6 ) {
    $i      = 0; //counter
    $pin    = ""; //our default pin is blank.
    while ( $i < $digits ) {
        //generate a random number between 0 and 9.
        $pin .= mt_rand( 0, 9 );
        $i++;
    }
    return $pin;
}

function sendCodeMelliPayamak($mobile,$pattern,$code) {

    //MelliPayamak
		$url = 'https://rest.payamak-panel.com/api/SendSMS/BaseServiceNumber';
		$data = array(
			'username'=>'9355012489',
		 	'password'=> '5f367c',
			'to' => $mobile,
			'bodyId'=> (int)$pattern,
			'text'=>$code
		);
		$data_string = json_encode($data);
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);

		// Next line makes the request absolute insecure
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		// Use it when you have trouble installing local issuer certificate
		// See https://stackoverflow.com/a/31830614/1743997

		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER,
			array('Content-Type: application/json')
		);
		$result = curl_exec($ch);
		return json_decode($result);

}

/**
 * Removes the billing details section on the checkout screen.
 */
function jp_disable_billing_details() {
	remove_action( 'edd_after_cc_fields', 'edd_default_cc_address_fields' );
}
add_action( 'init', 'jp_disable_billing_details' );
