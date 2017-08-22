<?php

namespace WSUWP\CLI\User;
use WP_CLI;

/**
 * WP-CLI commands for managing WSUWP.
 *
 * @package WSUWP\CLI\User;
 */
class Command {
	/**
	 * Create a new user.
	 *
	 * ## OPTIONS
	 *
	 * <user-email>
	 * : The WSU email address of the user to create.
	 *
	 * [--first_name=<first_name>]
	 * : The user's first name.
	 *
	 * [--last_name=<last_name>]
	 * : The user's last name.
	 *
	 * [--porcelain]
	 * : Output just the new user id.
	 *
	 * ## EXAMPLES
	 *
	 *     # Create user
	 *     $ wp user create jeremy.felt@wsu.edu
	 *     Success: Created user ID 3 for WSU NID jeremy.felt.
	 *
	 *     # Create user without showing password upon success
	 *     $ wp user create jeremy.felt@wsu.edu --porcelain
	 *     4
	 */
	public function create( $args, $assoc_args ) {
		$user = new \stdClass;

		list( $user->user_email ) = $args;

		$assoc_args = wp_slash( $assoc_args );

		if ( ! is_email( $user->user_email ) ) {
			WP_CLI::error( "'{$user->user_email}' is not a valid email." );
		}

		$user_parts = explode( '@', $user->user_email );

		if ( 'wsu.edu' !== $user_parts[1] ) {
			WP_CLI::error( "'{$user->user_email}' is not a valid WSU email." );
		}

		$user->user_login = $user_parts[0];

		if ( username_exists( $user->user_login ) ) {
			WP_CLI::error( "The '{$user->user_login}' username is already registered." );
		}

		$user->first_name = WP_CLI\Utils\get_flag_value( $assoc_args, 'first_name', false );

		$user->last_name = WP_CLI\Utils\get_flag_value( $assoc_args, 'last_name', false );

		$user->user_pass = wp_generate_password( 24 );

		add_filter( 'send_password_change_email', '__return_false' );
		add_filter( 'send_email_change_email', '__return_false' );

		if ( is_multisite() ) {
			$ret = wpmu_validate_user_signup( $user->user_login, $user->user_email );
			if ( is_wp_error( $ret['errors'] ) && ! empty( $ret['errors']->errors ) ) {
				WP_CLI::error( $ret['errors'] );
			}
			$user_id = wpmu_create_user( $user->user_login, $user->user_pass, $user->user_email );
			if ( ! $user_id ) {
				WP_CLI::error( "Unknown error creating new user." );
			}
			$user->ID = $user_id;
			$user_id = wp_update_user( $user );
			if ( is_wp_error( $user_id ) ) {
				WP_CLI::error( $user_id );
			}
		} else {
			$user_id = wp_insert_user( $user );
		}

		if ( ! $user_id || is_wp_error( $user_id ) ) {
			if ( ! $user_id ) {
				$user_id = 'Unknown error creating new user.';
			}
			WP_CLI::error( $user_id );
		}

		delete_user_option( $user_id, 'capabilities' );
		delete_user_option( $user_id, 'user_level' );

		if ( WP_CLI\Utils\get_flag_value( $assoc_args, 'porcelain' ) ) {
			WP_CLI::line( $user_id );
		} else {
			WP_CLI::success( "Created user $user_id." );
		}
	}
}
WP_CLI::add_command( 'wsuwp user', 'WSUWP\CLI\User\Command' );
