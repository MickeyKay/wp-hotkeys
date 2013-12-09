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
add_action( 'admin_menu', 'wh_do_settings_page' );

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
		<form method="post" action="options.php" class="wh-form">
			<p class="submit">
				<input type="submit" name="submit" id="submit" class="button button-primary" value="Save Changes">
				<a type="reset" name="wh-reset" id="wh-reset-top" class="button button-primary wh-reset" href="<?php echo admin_url( 'options-general.php?page=wordpress-hotkeys&wh-reset=true&wh-nonce='. wp_create_nonce( 'wh-nonce' ) ); ?>" onClick="return whConfirmReset()"><?php _e( 'Reset Defaults', 'wordpress-hotkeys' ); ?></a>
			</p>
			<?php settings_fields( 'wordpress-hotkeys' ); ?>
			<h2>General Settings</h2>
		    <?php do_settings_sections( 'general-settings' ); ?>
		    <br />
		    <h2>Hotkeys</h2>
		    <?php do_settings_sections( 'wordpress-hotkeys' ); ?>
			<p class="submit">
				<input type="submit" name="submit" id="submit" class="button button-primary" value="Save Changes">
				<a type="reset" name="wh-reset" id="wh-reset-bottom" class="button button-primary wh-reset" href="<?php echo admin_url( 'options-general.php?page=wordpress-hotkeys&wh-reset=true&wh-nonce='. wp_create_nonce( 'wh-nonce' ) ); ?>" onClick="return whConfirmReset()"><?php _e( 'Reset Defaults', 'wordpress-hotkeys' ); ?></a>
			</p>
			
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

	global $wh_menu_items;

	register_setting( 'wh-settings-group', 'wh-settings-group', 'wh-settings-validate' );

	// General Options
	add_settings_section(
		'wh-general-settings',
		'',
		'',
		'general-settings'
	);

	// Show hotkey hints
	$fields[] = array (
		'id' => 'show-hints',
		'title' => __( 'Show hotkey hints', 'wordpress-hotkeys' ),
		'callback' => 'wh_output_fields',
		'page' => 'general-settings',
		'section' => 'wh-general-settings',
		'args' => array( 
			'type' => 'checkbox',
			'validation' => 'wp_kses_post',
		)
	);

	// Exit hotkey
	$fields[] = array (
		'id' => 'close-hover-hotkey',
		'title' => __( 'Hotkey to close hover menu', 'wordpress-hotkeys' ),
		'callback' => 'wh_output_fields',
		'page' => 'general-settings',
		'section' => 'wh-general-settings',
		'args' => array( 
			'type' => 'text',
			'validation' => 'wp_kses_post',
		)
	);

	// Do hotkey settings for each admin menu item
	foreach ( $wh_menu_items as $item_name => $item) {	

		// Menu item setting sections
		add_settings_section(
			'wh-settings-section-' . $item_name,
			$item_name,
			'',
			'wordpress-hotkeys'
		);

		// Top level menu items
		$fields[] = array (
			'id' => htmlspecialchars( $item_name ),
			'title' => $item_name,
			'callback' => 'wh_output_fields',
			'page' => 'wordpress-hotkeys',
			'section' => 'wh-settings-section-' . $item_name,
			'args' => array( 
				'type' => 'text',
				'validation' => 'wp_kses_post',
				'level' => 'top',
				'default_hotkey' => ! empty( $item['default_hotkey'] ) ? $item['default_hotkey'] : '',
			)
		);

		// Sub menu items
		if ( !empty ( $item['sub_items'] ) ) {

			foreach( $item['sub_items'] as $sub_item_name => $sub_item ) {

				$fields[] = array (
					'id' => htmlspecialchars( $item_name ) . '-' . htmlspecialchars( $sub_item_name ),
					'title' => $sub_item_name,
					'callback' => 'wh_output_fields',
					'page' => 'wordpress-hotkeys',
					'section' => 'wh-settings-section-' . $item_name,
					'args' => array( 
						'type' => 'text',
						'validation' => 'wp_kses_post',
						'default_hotkey' => ! empty( $sub_item['default_hotkey'] ) ? $sub_item['default_hotkey'] : '',
					)
				);

			}

		}

	}

	foreach ( $fields as $field )
		wh_register_settings_field( $field['id'], $field['title'], $field['callback'], $field['page'], $field['section'], $field );

	// Register settings
	register_setting( 'wordpress-hotkeys', 'wh-options' );

}
add_action( 'admin_init', 'wh_register_settings' );

/**
 * Adds and registers settings field
 *
 * @package WordPress Hotkeys
 * @since   1.0		
 */	
function wh_register_settings_field( $id, $title, $callback, $page, $section, $field ) {

	// Add settings field	
	add_settings_field( $id, $title, $callback, $page, $section, $field );

	// Register setting with appropriate validation
	$validation = !empty( $field['args']['validation'] ) ? $field['args']['validation'] : '';

}

function wh_output_fields( $field ) {

	// Get hotkey options
	$options = get_option( 'wh-options' );

	$value = isset( $options[ htmlspecialchars( $field['id'] ) ] ) ? $options[ htmlspecialchars( $field['id'] ) ] : '';
	
	// Get necessary input args
	$type = $field['args']['type'];

	// Output form elements
	switch( $type ) {

		// Text fields
		case 'text':
			echo '<input name="wh-options[' . htmlspecialchars( $field['id'] ) . ']" id="' . $field['id'] . '" type="' . $type . '" value="' . $value . '"/>';
			if ( isset( $field['args']['level'] ) )
				echo ' [top level]';
			break;

		// Checkbox
		case 'checkbox':
			echo '<input name="wh-options[' . $field['id'] . ']" id="' . $field['id'] . '" type="hidden" value="0"' . checked( get_option( 'wh-options' )[ $field['id'] ], 1, false ) . '" />';
			echo '<input name="wh-options[' . $field['id'] . ']" id="' . $field['id'] . '" type="' . $type . '" value="1"' . checked( get_option( 'wh-options' )[ $field['id'] ], 1, false ) . '" />';
			break;

	}
	
	// After text
	if ( !empty( $field['args']['after_text'] ) )
		echo ' <em>' . $field['args']['after_text'] . '</em>';

	// Description
	if ( !empty( $field['args']['description'] ) )
		echo '<br /><em>' . $field['args']['description'] . "</em>\n";

}