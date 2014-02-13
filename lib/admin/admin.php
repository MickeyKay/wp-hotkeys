<?php

/**
 * Creates admin settings page
 *
 * @since   1.0.0
 * 
 * @package WP Hotkeys
 */

/**

	TODO:
	- Fix reset to reset modifiers to
	- COmment all functions

**/


function wh_do_settings_page() {

	// Create admin menu item
	add_options_page( WH_PLUGIN_NAME, 'WP Hotkeys', 'manage_options', 'wp-hotkeys', 'wh_output_settings');

}
add_action( 'admin_menu', 'wh_do_settings_page' );

/**
 * Outputs settings page with form
 *
 * @package WP Hotkeys
 * @since   1.0
 */
function wh_output_settings() { ?>
	<div class="wrap">
		<?php screen_icon(); ?>
		<h2><?php echo WH_PLUGIN_NAME; ?></h2>
		<form method="post" action="options.php" class="wh-form">
			<style>
				.wh-form tr:first-child .warning {
					border-color: red;
				}

				.wh-form .warning {
					border-color: orange;
				}


			</style>
			<p class="submit">
				<input type="submit" name="submit" id="submit" class="button button-primary" value="Save Changes">
				<a type="reset" name="wh-reset" id="wh-reset-top" class="button button-primary wh-reset" href="<?php echo admin_url( 'options-general.php?page=wp-hotkeys&wh-reset=true&wh-nonce='. wp_create_nonce( 'wh-nonce' ) ); ?>" onClick="return whConfirmReset()"><?php _e( 'Reset Defaults', 'wp-hotkeys' ); ?></a>
			</p>
			<?php settings_fields( 'wp-hotkeys' ); ?>
			<h2><?php _e( 'General Settings', 'wp-hotkeys'); ?></h2>
		    <?php do_settings_sections( 'general-settings' ); ?>
		    <br />
		    <h2><?php _e( 'Hotkeys', 'wp-hotkeys'); ?></h2>
		    <?php do_settings_sections( 'wp-hotkeys' ); ?>
			<p class="submit">
				<input type="submit" name="submit" id="submit" class="button button-primary" value="Save Changes">
				<a type="reset" name="wh-reset" id="wh-reset-bottom" class="button button-primary wh-reset" href="<?php echo admin_url( 'options-general.php?page=wp-hotkeys&wh-reset=true&wh-nonce='. wp_create_nonce( 'wh-nonce' ) ); ?>" onClick="return whConfirmReset()"><?php _e( 'Reset Defaults', 'wp-hotkeys' ); ?></a>
			</p>
			
		</form>
	</div>
<?php }

/**
 * Registers plugin settings
 *
 * @package WP Hotkeys
 * @since   1.0
 */
function wh_register_settings() {

	global $menu, $submenu, $wh_menu_items;
			
	register_setting( 'wh-settings-group', 'wh-settings-group', 'wh-settings-validate' );

	// 1. General Options
	add_settings_section(
		'wh-general-settings',
		'',
		'',
		'general-settings'
	);

	// Show hotkey hints
	$fields[] = array (
		'id' => 'show-hints',
		'title' => __( 'Show hotkey hints', 'wp-hotkeys' ),
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
		'title' => __( 'Hotkey to close hover menu', 'wp-hotkeys' ),
		'callback' => 'wh_output_fields',
		'page' => 'general-settings',
		'section' => 'wh-general-settings',
		'args' => array( 
			'type' => 'text',
			'validation' => 'wp_kses_post',
		)
	);

	// 2. Do hotkey settings for each admin menu item
	$duplicate = '';
	$sub_duplicates = '';

	// Check for duplicates
	foreach ( $wh_menu_items as $item_file => $item ) {

		// Only continue if hotkey's associated menu item is active
		if ( ! hotkey_item_is_active( $item_file ) )
			continue;

		// Top level
		if ( $item['hotkey'] )
			$hotkeys[ $item_file ] = $item['hotkey'] . '-' . $item['modifier'];

		// Sub level
		if ( empty( $item['sub_items'] ) )
			continue;

		foreach ( $item['sub_items'] as $sub_item_file => $sub_item ) {

			// Only continue if hotkey's associated menu item is active
			if ( ! hotkey_item_is_active( $sub_item_file ) )
				continue;

			if ( $sub_item['hotkey'] )
				$sub_hotkeys[ $item_file ][ $sub_item_file ] = $sub_item['hotkey'] . '-' . $sub_item['modifier'];

		}

		if ( isset( $sub_hotkeys[ $item_file ] ) && wh_get_keys_for_duplicates( $sub_hotkeys[ $item_file ] ) )
			$sub_duplicates[ $item_file ] = wh_get_keys_for_duplicates( $sub_hotkeys[ $item_file ] );
	
	}

	// Top level duplicates array
	if ( $hotkeys )
		$duplicates = wh_get_keys_for_duplicates( $hotkeys );

	if ( $duplicates || $sub_duplicates )
		add_action( 'admin_notices', 'wh_admin_notice' );
			

	// Output actual fields
	foreach ( $wh_menu_items as $item_file => $item) {				
		if ( empty( $item['name'] ) )
			continue;

		$item_name = $item['name'];

		// Menu item setting sections
		add_settings_section(
			'wh-settings-section-' . $item_name,
			'<hr />',
			'',
			'wp-hotkeys'
		);

		// Add duplicate arg if two of the same hotkey exist
		$duplicate = false;
		if ( in_array( $item_file, $duplicates ) )
			$duplicate = true;

		// Top level menu items
		$fields[] = array (
			'id' => $item_file,
			'title' => '<span class="wph-top-level">' . $item_name . '</span>',
			'callback' => 'wh_output_fields',
			'page' => 'wp-hotkeys',
			'section' => 'wh-settings-section-' . $item_name,
			'args' => array( 
				'type' => 'text',
				'validation' => 'wp_kses_post',
				'level' => 'top',
				'duplicate' => $duplicate,
			)
		);


		// Sub menu items
		if ( !empty ( $item['sub_items'] ) ) {

			foreach( $item['sub_items'] as $sub_item_file => $sub_item ) {

				if ( empty( $sub_item['name'] ) )
					continue;

				$sub_item_name = $sub_item['name'];

				// Add duplicate arg if two of the same hotkey exist
				$duplicate = false;
				if ( isset( $sub_duplicates[ $item_file ] ) && in_array( $sub_item_file, $sub_duplicates[ $item_file ] ) )
					$duplicate = true;

				$fields[] = array (
					'id' => $item_file. '-' . $sub_item_file,
					'title' => $sub_item_name,
					'callback' => 'wh_output_fields',
					'page' => 'wp-hotkeys',
					'section' => 'wh-settings-section-' . $item_name,
					'args' => array( 
						'type' => 'text',
						'validation' => 'wp_kses_post',
						'duplicate' => $duplicate,
					)
				);

			}

		}

	}

	foreach ( $fields as $field )
		wh_register_settings_field( $field['id'], $field['title'], $field['callback'], $field['page'], $field['section'], $field );

	// Register settings
	register_setting( 'wp-hotkeys', 'wh-options' );

}
add_action( 'admin_init', 'wh_register_settings' );

/**
 * Adds and registers settings field
 *
 * @package WP Hotkeys
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
				
	$id = $field['id'];

	$value = isset( $options[ htmlspecialchars( $id ) ] ) ? $options[ htmlspecialchars( $id ) ] : '';
	
	// Get necessary input args
	$type = $field['args']['type'];

	// Output form elements
	switch( $type ) {

		// Text fields
		case 'text':
			// Check if this hotkey has a duplicate
			$class = '';
			if ( isset( $field['args']['duplicate'] ) && $field['args']['duplicate'] )
				$class = ' class="warning" ';

			echo '<input name="wh-options[' . htmlspecialchars( $id ) . ']" id="' . $id . '" type="' . $type . '" value="' . $value . '"' . $class . '/>';
			echo ' + ';
			$modifier_id = 'modifier-' . $id;
			?>
			<select name="wh-options[<?php echo htmlspecialchars( $modifier_id ); ?>]" id="<?php echo $modifier_id; ?>">
				<option value="0" <?php selected( $options[ $modifier_id ], 0 ); ?>>No modifier key</option>
				<option value="shift" <?php selected( $options[ $modifier_id ], 'shift' ); ?>>Shift</option>
				<option value="meta" <?php selected( $options[ $modifier_id ], 'meta' ); ?>>Command</option>
				<option value="ctrl" <?php selected( $options[ $modifier_id ], 'ctrl' ); ?>>Control</option>
				<option value="alt" <?php selected( $options[ $modifier_id ], 'alt' ); ?>>Option / Alt</option>
			</select>
			<?
			break;

		// Checkbox
		case 'checkbox':
			echo '<input name="wh-options[' . $id . ']" id="' . $id . '" type="hidden" value="0"' . checked( $options[ $id ], 1, false ) . '" />';
			echo '<input name="wh-options[' . $id . ']" id="' . $id . '" type="' . $type . '" value="1"' . checked( $options[ $id ], 1, false ) . '" />';
			break;

	}
	
	// After text
	if ( !empty( $field['args']['after_text'] ) )
		echo ' <em>' . $field['args']['after_text'] . '</em>';

	// Description
	if ( !empty( $field['args']['description'] ) )
		echo '<br /><em>' . $field['args']['description'] . "</em>\n";

}

/**
 * Check if a hotkey's associated menu item exists
 *
 * This check is necessary to prevent default hotkeys
 * from triggering duplicates on installs in which the 
 * associated menu item doesn't exist (e.g. Genesis)
 *
 * @package WP Hotkeys
 * @since   1.0	
 *
 * @param   string $item_file Hotkey's associated menu file
 * @return  book True if active
 */
function hotkey_item_is_active( $item_file ) {
	global $menu, $submenu;

	$hotkey_item_is_active = false;

	// Check top-level menu items
	foreach( $menu as $menu_item ) {
		// Compare active menu item file with hotkey's associated file
		if ( $menu_item[2] == $item_file )
			$hotkey_item_is_active = true;
	}

	// Check sub-level menu items
	foreach( $submenu as $top_file => $submenu_item ) {
		foreach( $submenu_item as $menu_item ) {
			// Compare active menu item file with hotkey's associated file
			if ( $menu_item[2] == $item_file )
				$hotkey_item_is_active = true;
		}
	}

	return $hotkey_item_is_active;
}

function wh_get_keys_for_duplicates( $array ) {

	$counts = array_count_values( $array );
	
	$filtered = array_filter( $counts, function( $value ) {
	    return $value != 1;
	});

	return array_keys( array_intersect( $array, array_keys( $filtered ) ) );

}

function wh_admin_notice() { ?>
	<div class="error">
		<p><?php printf( __( '<b>There are duplicate hotkeys.</b> Please visit the %sWP Hotkeys settings page%s to fix this issue.', 'wp-hotkeys' ), '<a href="options-general.php?page=wp-hotkeys&settings-updated=true">', '</a>' ); ?></p>
		<p>Top level duplicates are indicated with red.<br />
			Sub-level duplicates are indicated with orange.</p>
	</div>
<?php }