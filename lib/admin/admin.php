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
	
	// Parsing Method
	$fields[] = array (
		'id' => 'risf-parsing-method',
		'title' => __( 'Parsing Method', 'risf' ),
		'callback' => 'risf_output_fields',
		'section' => 'risf-main-settings-section',
		'page' => 'risf-settings-page',
		'args' => array( 
			'type' => 'radio',
			'options' => array (
				'htmlpurify' => array(
					'label' => __( 'HTML Purify', 'risf' ),
					'description' => __( 'More thorough. Automatically completes unclosed tags, removes malicious code, ensures standards compliance - <a href="http://htmlpurifier.org/">read more</a>.', 'risf' )
				),
				'regex' => array(
					'label' => __( 'Regex', 'risf' ),
					'description' => __( 'Less thorough. Uses simple pattern matching. Try using this method if the HTML Purify method is doing more than you want.', 'risf' )
				),
			),
			'default' => 'htmlpurify',
			'label_location' => 'below',
		)
	);

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
				'output' => array(
					'label' => __( 'Output', 'risf' ),
					'description' => __( 'Filters output. Preserves styles/fonts in database, but filters the front-end output - uses <code>the_content</code> filter', 'risf' )
				),
				'content' => array(
					'label' => __( 'Content', 'risf' ),
					'description' => __( 'Warning: Filters actual content. Removes styles/fonts when post/page content is saved - uses <code>content_save_pre</code> filter.<br />Note: this method works on future saves, and will not retroactively modify post/page content.', 'risf' )
				),
			),
			'default' => 'output',
			'label_location' => 'below',
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
			'description' => __( 'How soon to run the filter (default is 10). Correlates to the <code>$priority</code> parameter in <code>add_filter()</code> - <a href="http://codex.wordpress.org/Function_Reference/add_filter">read more</a>.', 'risf' ),
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
			'label' => __( 'Remove empty <code>&lt;span&gt;</code> elements', 'risf' ),
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
	$value = get_option( $field['id'] );
	if ( empty( $value ) && isset( $field['args']['default'] ) )
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
			echo '<input name="' . $field['id'] . '" id="' . $field['id'] . '-empty" type="hidden" value="empty"' . '" />';
			echo '<input name="' . $field['id'] . '" id="' . $field['id'] . '" type="' . $type . '" value="1"' . checked( get_option( $field['id'] ), 1, false ) . '" />' . ( !empty( $field['args']['label'] ) ? ' <label for="' . $field['id'] . '">'  . $field['args']['label'] . '</label>' : '');
			break;

		// Radio Buttons
		case 'radio':
			$i = 1;
			foreach ( $field['args']['options'] as $option => $option_array ) {
				echo (1 != $i ? '<br />' : '') . '<input name="' . $field['id'] . '" id="' . $field['id'] . '-' . $option_array['label'] . '" type="' . $type . '" value="' . $option . '"' . checked( get_option( $field['id'] ), $option, false ) . '" /> ';
				echo '<label for="' . $field['id'] . '-' . $option_array['label'] . '"><b>' . $option_array['label'] . '</b></label>'; 
				if ( !empty( $option_array['description'] ) ) {
					if ( isset( $field['args']['label_location'] ) && 'below' == $field['args']['label_location'] )
						echo '<br />';
					else
						echo ' ';
					echo '<em>' . $option_array['description'] . '</em>';
				}
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
 * Outputs include/exclude metabox for all checked post types
 *
 * @package Remove Inline Styles & Fonts
 * @since   1.0
 */
function risf_do_meta_boxes() {

	foreach ( get_post_types( '', 'objects' ) as $post_type ) {

		// See if post type is checked in the admin settings
		$post_type_checked = get_option( 'risf-post-type-' . $post_type->name );

		// Add a metabox to this post type if checked
		if ( 1 == $post_type_checked )
			add_meta_box('risf-meta-box', RISF_PLUGIN_NAME, 'risf_do_meta_box', $post_type->name, 'side', 'low', array( 'post_type' => $post_type->labels->singular_name ) );
	}

}
add_action( 'add_meta_boxes', 'risf_do_meta_boxes' );

/**
 * Produces metabox
 *
 * @package Remove Inline Styles & Fonts
 * @since   1.0
 *
 * @param   array $args callback arguments
 */
function risf_do_meta_box( $post, $metabox ) {
	
	global $post;

	// Add an nonce field so we can check for it later.
	wp_nonce_field( basename( __FILE__ ), 'risf_nonce' );

	// Get post meta value
	$value = get_post_meta( $post->ID, '_risf_exclude', true );

	echo '<input type="hidden" id="risf-exclude-risf-empty" name="_risf_exclude" value="empty" />';
	echo '<input type="checkbox" id="risf-exclude" name="_risf_exclude" value="1" ' . checked( $value, '1', false ). '/> <label for="risf-exclude">Don\'t apply to this ' . $metabox['args']['post_type'] . '</label>';
	echo '<br /><br /><em>Note: when checking/unchecking this box using the "Content" removal method, please update the post/page twice to see changes.';

}

function risf_save_meta_box( $post_id, $post ) {

	// Verify the nonce before proceeding
	if ( !isset( $_POST['risf_nonce'] ) || !wp_verify_nonce( $_POST['risf_nonce'], basename( __FILE__ ) ) )
		return $post_id;

	// Get the post type object
	$post_type = get_post_type_object( $post->post_type );

	// Check if the current user has permission to edit the post
	if ( !current_user_can( $post_type->cap->edit_post, $post_id ) )
		return $post_id;

	// Get the posted data and sanitize it for use as an HTML class 
	$new_meta_value = ( isset( $_POST['_risf_exclude'] ) ? sanitize_html_class( $_POST['_risf_exclude'] ) : '' );

	// Get the meta key
	$meta_key = '_risf_exclude';

	// Get the meta value of the custom field key
	$meta_value = get_post_meta( $post_id, $meta_key, true );

	// If a new meta value was added and there was no previous value, add it
	if ( $new_meta_value && '' == $meta_value )
		add_post_meta( $post_id, $meta_key, $new_meta_value, true );

	// If the new meta value does not match the old value, update it 
	elseif ( $new_meta_value && $new_meta_value != $meta_value )
		update_post_meta( $post_id, $meta_key, $new_meta_value );

	// If there is no new meta value but an old value exists, delete it
	elseif ( '' == $new_meta_value && $meta_value )
		delete_post_meta( $post_id, $meta_key, $meta_value );

}
add_action( 'save_post', 'risf_save_meta_box', 0, 2 );

/**
 * Mimics intval() validation method but returns 10 instead of 0 on failure
 *
 * @param  mixed $input
 * @return int returns $input, or 10 on fail
 */
function risf_intval( $input ) {
	return intval( $input ) ? $input: 10;
}