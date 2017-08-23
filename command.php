<?php

if ( ! class_exists( 'WP_CLI' ) ) {
	return;
}

include_once __DIR__ . '/commands/wsuwp-cli-users.php';
include_once __DIR__ . '/commands/wsuwp-cli-site.php';
