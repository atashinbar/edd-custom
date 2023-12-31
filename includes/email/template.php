<?php
/**
 * Email Template
 *
 * @author 		Easy Digital Downloads
 * @package 	Easy Digital Downloads/Templates/Emails
 * @version     2.1
 */
defined( 'ABSPATH' ) || exit;

/**
 * Processes the creation of a new renewal notice
 *
 * @since 3.0
 * @param array $data The post data
 * @return void
 */
function modian_abzar_edd_sl_process_add_renewal_notice( $data ) {

	if( ! is_admin() ) {
		return;
	}

	if( ! current_user_can( 'manage_shop_settings' ) ) {
		wp_die( __( 'You do not have permission to add renewal notices', 'edd_sl' ), __( 'Error', 'edd_sl' ), array( 'response' => 401 ) );
	}

	if( ! wp_verify_nonce( $data['edd-renewal-notice-nonce'], 'edd_renewal_nonce' ) ) {
		wp_die( __( 'Nonce verification failed', 'edd_sl' ), __( 'Error', 'edd_sl' ), array( 'response' => 401 ) );
	}

	$subject = isset( $data['subject'] ) ? sanitize_text_field( $data['subject'] ) : __( 'Your License Key is About to Expire', 'edd_sl' );
	$period  = isset( $data['period'] )  ? sanitize_text_field( $data['period'] )  : '+1month';
	$message = isset( $data['message'] ) ? stripslashes( $data['message'] ) : false;
	$result  = 'success';
	$notice  = __( 'Renewal Notice saved successfully.', 'edd_sl' );

	if ( empty( $message ) ) {
		$result  = 'warning';
		$notice  = __( 'Your message was empty and could not be saved. It has been reset to the default.', 'edd_sl' );
		$message = edd_sl_get_default_renewal_notice_message();
	}

	$notices   = edd_sl_get_renewal_notices();
	$key       = is_array( $notices ) ? count( $notices ) : 1;
	$notices[] = array(
		'subject'     => $subject,
		'message'     => $message,
		'send_period' => $period,
	);

	update_option( 'edd_sl_renewal_notices', $notices );

	$redirect_url = add_query_arg(
		array(
			'post_type'     => 'download',
			'page'          => 'edd-license-renewal-notice',
			'edd_sl_action' => 'edit-renewal-notice',
			'notice'        => urlencode( $key ),
			'edd-message'   => urlencode( $notice ),
			'edd-result'    => urlencode( $result ),
		),
		admin_url( 'edit.php' )
	);

	wp_safe_redirect( $redirect_url );
	exit;

}
remove_action( 'edd_add_renewal_notice', 'edd_sl_process_add_renewal_notice' );
add_action( 'edd_add_renewal_notice', 'modian_abzar_edd_sl_process_add_renewal_notice' );

/**
 * Processes the update of an existing renewal notice
 *
 * @since 3.0
 * @param array $data The post data
 * @return void
 */
function modian_abzar_edd_sl_process_update_renewal_notice( $data ) {

	if( ! is_admin() ) {
		return;
	}

	if( ! current_user_can( 'manage_shop_settings' ) ) {
		wp_die( __( 'You do not have permission to add renewal notices', 'edd_sl' ), __( 'Error', 'edd_sl' ), array( 'response' => 401 ) );
	}

	if( ! wp_verify_nonce( $data['edd-renewal-notice-nonce'], 'edd_renewal_nonce' ) ) {
		wp_die( __( 'Nonce verification failed', 'edd_sl' ), __( 'Error', 'edd_sl' ), array( 'response' => 401 ) );
	}

	if( ! isset( $data['notice-id'] ) ) {
		wp_die( __( 'No renewal notice ID was provided', 'edd_sl' ) );
	}

	$subject = isset( $data['subject'] ) ? sanitize_text_field( $data['subject'] ) : __( 'Your License Key is About to Expire', 'edd_sl' );
	$period  = isset( $data['period'] )  ? sanitize_text_field( $data['period'] )  : '1month';
	$message = isset( $data['message'] ) ? stripslashes( $data['message'] ) : false;
	$result  = 'success';
	$notice  = __( 'Renewal Notice saved successfully.', 'edd_sl' );

	if ( empty( $message ) ) {
		$result  = 'warning';
		$notice  = __( 'Your message was empty and could not be saved. It has been reset to the default.', 'edd_sl' );
		$message = edd_sl_get_default_renewal_notice_message();
	}

	$notices = edd_sl_get_renewal_notices();
	$notices[ absint( $data['notice-id'] ) ] = array(
		'subject'     => $subject,
		'message'     => $message,
		'send_period' => $period
	);

	update_option( 'edd_sl_renewal_notices', $notices );

	$redirect_url = add_query_arg(
		array(
			'post_type'     => 'download',
			'page'          => 'edd-license-renewal-notice',
			'edd_sl_action' => 'edit-renewal-notice',
			'notice'        => urlencode( $data['notice-id'] ),
			'edd-message'   => urlencode( $notice ),
			'edd-result'    => urlencode( $result ),
		),
		admin_url( 'edit.php' )
	);

	wp_safe_redirect( $redirect_url );

	exit;

}
remove_action( 'edd_edit_renewal_notice', 'edd_sl_process_update_renewal_notice' );
add_action( 'edd_edit_renewal_notice', 'modian_abzar_edd_sl_process_update_renewal_notice' );


/**
 * Processes the update of an existing reminder notice
 *
 * @since 2.4
 *
 * @param array $data The post data
 *
 * @return void
 */
function modian_abzar_edd_recurring_process_update_reminder_notice( $data ) {

	if ( ! is_admin() ) {
		return;
	}

	if ( ! current_user_can( 'manage_shop_settings' ) ) {
		wp_die( __( 'You do not have permission to add reminder notices', 'edd-recurring' ), __( 'Error', 'edd-recurring' ), array( 'response' => 401 ) );
	}

	if ( ! wp_verify_nonce( $data['edd-recurring-reminder-notice-nonce'], 'edd_recurring_reminder_nonce' ) ) {
		wp_die( __( 'Nonce verification failed', 'edd-recurring' ), __( 'Error', 'edd-recurring' ), array( 'response' => 401 ) );
	}

	if ( ! isset( $data['notice-id'] ) ) {
		wp_die( __( 'No reminder notice ID was provided', 'edd-recurring' ) );
	}

	$subject = isset( $data['subject'] ) ? sanitize_text_field( $data['subject'] ) : __( 'Your Subscription is About to Renew', 'edd-recurring' );
	$period  = isset( $data['period'] ) ? sanitize_text_field( $data['period'] ) : '+1month';
	$message = isset( $data['message'] ) ? stripslashes( $data['message'] ) : false;
	$type    = isset( $data['type'] ) ? sanitize_text_field( $data['type'] ) : 'renewal';

	if ( empty( $message ) ) {
		$message = 'Hello {name},

Your subscription for {subscription_name} will renew on {expiration}.';
	}

	$reminders                               = new EDD_Recurring_Reminders();
	$notices                                 = $reminders->get_notices();
	$notices[ absint( $data['notice-id'] ) ] = array(
		'subject'     => $subject,
		'message'     => $message,
		'send_period' => $period,
		'type'		  => $type
	);

	update_option( 'edd_recurring_reminder_notices', $notices );

	wp_safe_redirect( edd_recurring_get_email_settings_url() );
	exit;

}
remove_action( 'edd_recurring_edit_reminder_notice', 'edd_recurring_process_update_reminder_notice' );
add_action( 'edd_recurring_edit_reminder_notice', 'modian_abzar_edd_recurring_process_update_reminder_notice' );
