<?php

/**
 * @link                https://github.com/ICTU/ictuwp-plugin-gc-reply-to-mail_address
 * @package             ictuwp-plugin-gc-reply-to-mail_address
 *
 * @wordpress-plugin
 * Plugin Name:         ICTU / Gebruiker Centraal / Set reply-to email_address
 * Plugin URI:          https://github.com/ICTU/ictuwp-plugin-gc-reply-to-mail_address
 * Description:         Filter om het antwoord-adres voor e-mail te corrigeren. Gebruikt GC_REPLY_MAIL of de instellingen van SMTP plugin (wpmailsmtp).
 * Version:             1.0.1
 * Version description: Fix thema_sort_order
 * Author:              Paul van Buuren
 * Author URI:          https://github.com/paulvanbuuren
 * License:             GPL-2.0+
 * License URI:         http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:         gctheme
 * Domain Path:         /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Appends 'reply to' mail address to header.
 * - If a GC_REPLY_MAIL is set (anywhere), use that address
 * - else, if a mail_address is set through the SMTP plugin (wpmailsmtp), use that address
 * - else, use 'admin_email' address
 *
 * code inspired by:
 * https://wpmailsmtp.com/docs/setting-a-custom-reply-to-email/
 *
 * @param $args
 *
 * @return mixed
 */
function gc_set_reply_mail_address( $args ) {

	// Defaults
	$reply_to_mail = get_option( 'admin_email' ); // default is the admin's email
	$reply_to_name = get_option( 'blogname' );

	if ( defined( 'GC_REPLY_MAIL' ) ) {
		// somewhere the constant GC_REPLY_MAIL is set
		$reply_to_mail = GC_REPLY_MAIL;
		$reply_to_name = $reply_to_mail;

	} else {
		// Get the proper send-from mail address.
		// We try to get it from the SMTP plugin settings
		$smtp_option_setting      = get_option( 'wp_mail_smtp' );
		$smtp_option_unserialized = maybe_unserialize( $smtp_option_setting );

		if ( $smtp_option_unserialized['mail'] ) {
			// there are values in the settings
			$email_from_settings = $smtp_option_unserialized['mail']['from_email'];
			if ( filter_var( $email_from_settings, FILTER_VALIDATE_EMAIL ) ) {
				// good, this should be a valid mail address
				$reply_to_mail = $smtp_option_unserialized['mail']['from_email'];
				if ( $smtp_option_unserialized['mail']['from_name'] ) {
					$reply_to_name = $smtp_option_unserialized['mail']['from_name'];
				} else {
					$reply_to_name = $reply_to_mail;
				}
			}
		}
	}

	// set the reply-to string
	$reply_to = 'Reply-To: ' . $reply_to_name . ' <' . $reply_to_mail . '>';

	// check and clean headers if necessary
	if ( ! empty( $args['headers'] ) ) {
		if ( ! is_array( $args['headers'] ) ) {
			$args['headers'] = array_filter( explode( "\n", str_replace( "\r\n", "\n", $args['headers'] ) ) );
		}

		// Filter out all other Reply-To headers.
		$args['headers'] = array_filter( $args['headers'], function ( $header ) {
			return strpos( strtolower( $header ), 'reply-to' ) !== 0;
		} );
	} else {
		$args['headers'] = [];
	}

	// append replyto to the headers
	$args['headers'][] = $reply_to;
	if ( WP_DEBUG ) {
		error_log( 'reply address set to: ' . esc_html( $reply_to ) );
	}

	return $args;

}

add_filter( 'wp_mail', 'gc_set_reply_mail_address', PHP_INT_MAX );
