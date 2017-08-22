<?php

add_filter( 'wsuwp_load_mu_plugins', 'wsuwp_add_mu_plugins' );
/**
 * Filters the list of MU plugins loaded with the WSUWP Platform.
 *
 * @since 0.0.1
 *
 * @return array
 */
function wsuwp_add_mu_plugins() {
	return array(
		'wsuwp-mu-simple-filters/wsuwp-mu-simple-filters.php',
		'wsuwp-multiple-networks/wsuwp-multiple-networks.php',
	);
}
