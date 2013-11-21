<?php 

/**
 * Creates admin settings page
 *
 * @package WordPress Hotkeys
 * @since   1.0
 */
function wh_do_settings_page() {

	// Create admin menu item
	add_options_page( WH_PLUGIN_NAME, 'WordPress Hotkeys', 'manage_options', 'wordpress-hotkeys', 'wh_output_settings');

}
add_action('admin_menu', 'wh_do_settings_page');

/**
 * Outputs settings page with form
 *
 * @package WordPress Hotkeys
 * @since   1.0
 */
function wh_output_settings() { ?>
	<div class="wrap">
		<?php screen_icon(); ?>
		<h2><?php echo WH_PLUGIN_NAME; ?></h2>
		<form method="post" action="options.php">
		    <?php settings_fields( 'wordpress-hotkeys' ); ?>
		    <?php do_settings_sections( 'wordpress-hotkeys' ); ?>
			<?php submit_button(); ?>
		</form>
	</div>
<?php }

/**
 * Registers plugin settings
 *
 * @package WordPress Hotkeys
 * @since   1.0
 */
function wh_register_settings() {

	register_setting( 'wh-settings-group', 'wh-settings-group', 'wh-settings-validate' );
	
	// Setting sections
	add_settings_section(
		'wh-settings-section',
		'Main Settings',
		'',
		'wordpress-hotkeys'
	);

	/* Define settings fields */

	// Menu Containers
	$fields[] = array (
		'id' => 'wh-containers',
		'title' => __( 'Menu Container(s) Class / ID', 'wh' ),
		'callback' => 'wh_output_fields',
		'section' => 'wordpress-hotkeys',
		'page' => 'wh-settings-section',
		'args' => array( 
			'type' => 'text',
			'validation' => 'wp_kses_post',
			'description' => __( 'Comma separated list of selectors for the parent div containing each menu &lt;ul&gt;.<br />Example: #nav, .mini-nav', 'wh' ),
		)
	);

	// Maximum width
	$fields[] = array (
		'id' => 'wh-width',
		'title' => __( 'Maximum Menu Width', 'wh' ),
		'callback' => 'wh_output_fields',
		'section' => 'wordpress-hotkeys',
		'page' => 'wh-settings-section',
		'args' => array( 
			'type' => 'text',
			'validation' => 'intval',
			'after_text' => 'px',
			'description' => __( 'The width at which the responsive select menu should appear/disappear.', 'wh' ),
		)
	);

	// Sub-item spacer
	$fields[] = array (
		'id' => 'wh-sub-item-spacer',
		'title' => __( 'Sub Item Spacer', 'wh' ),
		'callback' => 'wh_output_fields',
		'section' => 'wordpress-hotkeys',
		'page' => 'wh-settings-section',
		'args' => array(
			'type' => 'text',
			'validation' => 'wp_kses_post',
			'description' => __( 'The character(s) used to indent sub items.', 'wh' ),
		)
	);

	// First term name
	$fields[] = array (
		'id' => 'wh-first-term',
		'title' => __( 'First Term', 'wh' ),
		'callback' => 'wh_output_fields',
		'section' => 'wordpress-hotkeys',
		'page' => 'wh-settings-section',
		'args' => array(
			'type' => 'text',
			'validation' => 'wp_kses_post',
			'description' => __( 'The text for the select menu\'s top-level "dummy" item.<br />Example: ⇒ Navigation', 'wh' ),
		)
	);

	// Show current page
	$fields[] = array (
		'id' => 'wh-show-current-page',
		'title' => __( 'Show Current Page', 'wh' ),
		'callback' => 'wh_output_fields',
		'section' => 'wordpress-hotkeys',
		'page' => 'wh-settings-section',
		'args' => array(
			'type' => 'checkbox',
			'after_text' => __( 'Show the currently selected page instead of the top level "dummy" item.', 'wh' ),
		)
	);

	// Add settings fields
	foreach( $fields as $field ) {
		wh_register_settings_field( $field['id'], $field['title'], $field['callback'], $field['section'], $field['page'], $field );	
	}

	// Register settings
	register_setting('wordpress-hotkeys','wh-output-method');

}
add_action( 'admin_init', 'wh_register_settings' );

/**
 * Adds and registers settings field
 *
 * @package WordPress Hotkeys
 * @since   1.0		
 */	
function wh_register_settings_field( $id, $title, $callback, $section, $page, $field ) {

	// Add settings field	
	add_settings_field( $id, $title, $callback, $section, $page, $field );

	// Register setting with appropriate validation
	$validation = !empty( $field['args']['validation'] ) ? $field['args']['validation'] : '';
	register_setting( $section, $id, $validation );

}

function wh_output_fields( $field ) {
	
	/* Set default values if setting is empty */

	// Get setting
	$value = get_option( $field['id'] );
	
	// Set defaults if empty
	if ( empty( $value ) ) {

		switch( $field['id'] ) {

			// Examples
			
			/*
			case 'wh-first-term-name':
				update_option( 'wh-first-term-name', '⇒ Navigation' );
				break;

			case 'wh-sub-item-spacer':
				update_option( 'wh-sub-item-spacer', '-' );
				break;
			*/
		
		}

	}
	
	/* Output admin form elements for each settings field */
	
	// Get necessary input args
	$type = $field['args']['type'];
	$placeholder = !empty( $field['args']['placeholder'] ) ? ' placeholder="' . $field['args']['placeholder'] . '" ' : '';

	// Output form elements
	switch( $type ) {

		// Text fields
		case 'text':
			echo '<input name="' . $field['id'] . '" id="' . $field['id'] . '" type="' . $type . '" value="' . $value . '"' . $placeholder . '" />';
			break;

		// Checkbox
		case 'checkbox':
			echo '<input name="' . $field['id'] . '" id="' . $field['id'] . '" type="' . $type . '" value="1"' . $placeholder . checked( get_option( $field['id'] ), 1, false ) . '" />';
			break;

	}
	
	// After text
	if ( !empty( $field['args']['after_text'] ) )
		echo ' <em>' . $field['args']['after_text'] . '</em>';

	// Description
	if ( !empty( $field['args']['description'] ) )
		echo '<br /><em>' . $field['args']['description'] . "</em>\n";
}