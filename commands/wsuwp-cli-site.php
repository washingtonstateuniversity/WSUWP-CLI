<?php

namespace WSUWP\CLI\Site;
use WP_CLI;

/**
 * WP-CLI command for managing WSUWP sites
 *
 * @package WSUWP\CLI\Site;
 */
class Command {
	/**
	 * Create a new site.
	 *
	 * ## OPTIONS
	 *
	 * <site-url>
	 * : The domain and path of the new site.
	 *
	 * <admin-email>
	 * : The WSU email for first site administrator.
	 *
	 * <site-name>
	 * : The name of the site.
	 *
	 * <network-id>
	 * : The ID of the network the site should be created under.
	 *
	 * [--porcelain]
	 * : Output just the new site id.
	 *
	 * ## EXAMPLES
	 *
	 *     # Create site
	 *     $ wp wsuwp site create web.wsu.edu/sub-site jeremy.felt@wsu.edu "Web Sub Site" 2
	 *     Success: Created site ID 3 with admin user jeremy.felt
	 *
	 *     # Create site and returned only the ID
	 *     $ wp wsuwp site create web.wsu.edu/sub-site jeremy.felt@wsu.edu "Web Sub Site" 2 --porcelain
	 *     3
	 */
	public function create( $args, $assoc_args ) {
		global $wpdb;
		$new_site = new \stdClass;

		list( $new_site->url, $new_site->email, $new_site->name, $new_site->network ) = $args;

		$assoc_args = wp_slash( $assoc_args );

		$new_site->url = esc_url( $new_site->url );
		$new_site->domain = wp_parse_url( $new_site->url, PHP_URL_HOST );
		$new_site->path = trim( wp_parse_url( $new_site->url, PHP_URL_PATH ), '/' );

		if ( strpos( $new_site->path, '/' ) ) {
			WP_CLI::error( 'A site can only have one path.' );
		}

		if ( 0 === substr_count( $new_site->domain, '.' ) ) {
			WP_CLI::error( "'{$new_site->domain}'" . ' is not a valid domain.' );
		}

		$new_site->path = '/' . trailingslashit( $new_site->path );

		if ( wsuwp_validate_domain( $new_site->domain ) ) {
			$new_site->domain = strtolower( $new_site->domain );
		} else {
			WP_CLI::error( 'Invalid site address. Non valid characters were found in the domain.' );
		}

		if ( wsuwp_validate_path( $new_site->path ) ) {
			$new_site->path = strtolower( $new_site->path );
		} else {
			WP_CLI::error( 'Invalid site path. Non standard characters were found in the path name.' );
		}

		$existing = get_sites( array(
			'domain' => $new_site->domain,
			'path' => $new_site->path,
		) );

		if ( 0 !== count( $existing ) ) {
			WP_CLI::error( 'A site with this domain and path combination already exists.' );
		}


		$user_id = email_exists( $new_site->email );

		if ( ! $user_id ) { // Create a new user with a random password
			WP_CLI::error( 'The user does not exist.' );
		}

		$wpdb->hide_errors();
		$id = wpmu_create_blog( $new_site->domain, $new_site->path, $new_site->name, $user_id , array(
			'public' => 1,
		), $new_site->network );
		$wpdb->show_errors();

		if ( is_wp_error( $id ) ) {
			WP_CLI::error( $id->get_error_message() );
		}

		if ( ! is_super_admin( $user_id ) && ! get_user_option( 'primary_blog', $user_id ) ) {
			update_user_option( $user_id, 'primary_blog', $id, true );
		}

		// Clear any stale cache related to this domain and path request. See sunrise.
		wp_cache_delete( $new_site->domain . $new_site->path, 'wsuwp:site' );

		$content_mail = sprintf( __( 'New site created by %1$s

Address: %2$s
Name: %3$s' ), wp_get_current_user()->user_login , get_site_url( $id ), wp_unslash( $new_site->name ) );
		wp_mail( get_site_option( 'admin_email' ), sprintf( __( '[%s] New Site Created' ), get_current_site()->site_name ), $content_mail, 'From: "Site Admin" <' . get_site_option( 'admin_email' ) . '>' );

		if ( WP_CLI\Utils\get_flag_value( $assoc_args, 'porcelain' ) ) {
			WP_CLI::line( $id );
		} else {
			WP_CLI::success( "Created site $id." );
		}
	}
}
WP_CLI::add_command( 'wsuwp site', 'WSUWP\CLI\Site\Command' );
