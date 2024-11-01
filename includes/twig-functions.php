<?php

function twig_yield_error( $message ) {

	if ( !is_admin() && !in_array( $GLOBALS['pagenow'], array('wp-login.php', 'wp-register.php') ) ) {

		echo '<div class="error"><p><strong>Error:</strong> ' . $message . '</p></div>';
	} else {

		add_action( 'admin_notices', function() use ( $message ) {

			echo '<div class="error"><p><strong>Error:</strong> ' . $message . '</p></div>';
		});
	}
}
