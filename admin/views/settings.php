<div class="wrap">
	<h2><?php echo esc_html( get_admin_page_title() ); ?></h2>
	<form method="post" action="options.php">
		<?php settings_fields( $this->plugin_slug ); ?>
	    <?php do_settings_sections( $this->plugin_slug ); ?>
	    <?php submit_button(); ?>
	</form>
</div>