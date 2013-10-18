<?php

/**
 * Creates admin settings page
 *
 * @package Remove Inline Styles & Fonts
 * @since   1.0
 */
function risf_do_settings_page() {

	// Create admin menu item
	add_options_page( RISF_PLUGIN_NAME, 'Remove Inline Styles & Fonts', 'manage_options', 'risf-settings-page', 'risf_output_settings');

}
add_action('admin_menu', 'risf_do_settings_page');

/**
 * Outputs settings page with form
 *
 * @package Remove Inline Styles & Fonts
 * @since   1.0
 */
function risf_output_settings() { ?>
	<div class="wrap">
		<?php screen_icon(); ?>
		<h2><?php echo RISF_PLUGIN_NAME; ?></h2>
		<form method="post" action="options.php">
		    <?php settings_fields( 'risf-settings-page' ); ?>
		    <?php do_settings_sections( 'risf-settings-page' ); ?>
			<?php submit_button(); ?>
		</form>
	</div>
<?php }

/**
 * Registers plugin settings
 *
 * @package Remove Inline Styles & Fonts
 * @since   1.0
 */
function risf_register_settings() {
	
	// Main Settings Section
	add_settings_section(
		'risf-main-settings-section',
		__( 'Main Settings', 'risf' ),
		'',
		'risf-settings-page'
	);

	// Post Types Section
	add_settings_section(
		'risf-post-types-section',
		__( 'Post Types to Affect', 'risf' ),
		'',
		'risf-settings-page'
	);

	/**
	 * Main Settings Section
	 */

	// Removal Method
	$fields[] = array (
		'id' => 'risf-removal-method',
		'title' => __( 'Removal Method', 'risf' ),
		'callback' => 'risf_output_fields',
		'section' => 'risf-main-settings-section',
		'page' => 'risf-settings-page',
		'args' => array( 
			'type' => 'radio',
			'options' => array (
				__( 'Output <em>(filters output, preserves styles/fonts in database - uses <code>the_content</code> filter)</em>', 'risf' ) => 'output',
				__( 'Content <em>(filters actual content, removes styles/fonts from database - uses <code>content_save_pre</code> filter)</em>', 'risf' ) => 'content',
			),
			'default' => 'output',
		)
	);

	// Filter Priority
	$fields[] = array (
		'id' => 'risf-filter-priority',
		'title' => __( 'Filter Priority', 'risf' ),
		'callback' => 'risf_output_fields',
		'section' => 'risf-main-settings-section',
		'page' => 'risf-settings-page',
		'args' => array( 
			'type' => 'text',
			'validation' => 'risf_intval',
			'description' => __( 'How soon to run the filter. Correlates to the <code>$priority</code> parameter in <code>add_filter()</code> - <a href="http://codex.wordpress.org/Function_Reference/add_filter">read more</a>', 'risf' ),
			'default' => 10,
		)
	);

	// Style Attributes
	$fields[] = array (
		'id' => 'risf-style-attributes',
		'title' => __( 'Style Attributes', 'risf' ),
		'callback' => 'risf_output_fields',
		'section' => 'risf-main-settings-section',
		'page' => 'risf-settings-page',
		'args' => array( 
			'type' => 'checkbox',
			'label' => __( 'Remove all <code>style=""</code> attributes', 'risf' ),
		)
	);
	// Font Tags
	$fields[] = array (
		'id' => 'risf-font-tags',
		'title' => __( 'Font Tags', 'risf' ),
		'callback' => 'risf_output_fields',
		'section' => 'risf-main-settings-section',
		'page' => 'risf-settings-page',
		'args' => array( 
			'type' => 'checkbox',
			'label' => __( 'Remove all <code>&lt;font&gt;</code> tags', 'risf' ),
		)
	);

	// Empty Span Elements
	$fields[] = array (
		'id' => 'risf-empty-span-elements',
		'title' => __( 'Empty Span Elements', 'risf' ),
		'callback' => 'risf_output_fields',
		'section' => 'risf-main-settings-section',
		'page' => 'risf-settings-page',
		'args' => array( 
			'type' => 'checkbox',
			'label' => __( 'Remove <code>&lt;span&gt;</code> elements that are empty or have no attributes', 'risf' ),
		)
	);

	/**
	 * Post Types Section
	 */
	foreach ( get_post_types( '', 'objects' ) as $post_type ) {
		$fields[] = array (
			'id' => 'risf-post-type-' . $post_type->name,
			'title' => $post_type->labels->name,
			'callback' => 'risf_output_fields',
			'section' => 'risf-post-types-section',	
			'page' => 'risf-settings-page',
			'args' => array(
				'type' => 'checkbox',
				'default' => 1,
			)
		);
	}

	// Add settings fields
	foreach( $fields as $field ) {
		risf_register_settings_field( $field['id'], $field['title'], $field['callback'], $field['page'], $field['section'], $field );	
	}

	// Register settings
	register_setting('risf-main-settings-section','risf-output-method');
	register_setting('risf-post-types-section','risf-output-method');

}
add_action( 'admin_init', 'risf_register_settings' );

/**
 * Adds and registers settings field
 *
 * @package Remove Inline Styles & Fonts
 * @since   1.0		
 */	
function risf_register_settings_field( $id, $title, $callback, $section, $page, $field ) {

	// Add settings field	
	add_settings_field( $id, $title, $callback, $section, $page, $field );

	// Register setting with appropriate validation
	$validation = !empty( $field['args']['validation'] ) ? $field['args']['validation'] : '';
	register_setting( $section, $id, $validation );

}

function risf_output_fields( $field ) {
	
	// Set defaults if empty
	if ( !get_option( $field['id'] ) && isset( $field['args']['default'] ) )
		update_option( $field['id'], $field['args']['default'] );

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
			echo '<input name="' . $field['id'] . '" id="' . $field['id'] . '" type="hidden" value="empty"' . '" />';
			echo '<input name="' . $field['id'] . '" id="' . $field['id'] . '" type="' . $type . '" value="1"' . checked( get_option( $field['id'] ), 1, false ) . '" /> <label for="' . $field['id'] . '">' . $field['args']['label'] . '</label>';
			break;

		// Radio Buttons
		case 'radio':
			$i = 1;
			foreach ( $field['args']['options'] as $option => $value ) {
				echo (1 != $i ? '<br />' : '') . '<input name="' . $field['id'] . '" id="' . $field['id'] . '-' . $value . '" type="' . $type . '" value="' . $value . '"' . checked( get_option( $field['id'] ), $value, false ) . '" /> <label for="' . $field['id'] . '-' . $value . '">' . $option . '</label>';
				$i++;
			}
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
 * Mimics intval() validation method but returns 10 instead of 0 on failure
 *
 * @param  mixed $input
 * @return int returns $input, or 10 on fail
 */
function risf_intval( $input ) {
	return intval( $input ) ? $input: 10;
}