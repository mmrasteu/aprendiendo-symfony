<?php

/**
 * Enqueue block editor JavaScript and CSS
*/
add_action( 'enqueue_block_editor_assets', 'upper_editor_scripts' );
function upper_editor_scripts() {

	// Enqueue the bundled block JS file
	wp_enqueue_script(
		'upper-block',
		plugins_url( 'upper-block.js', __FILE__ ), 
		[ 'wp-blocks', 'wp-element', 'wp-components', 'wp-i18n' ], // dependencies
		filemtime( plugin_dir_path( __FILE__ ) . 'upper-block.js' ) // version by file last modified date
	);
	
	remove_filter( 'gutenberg_can_edit_post_type', 'vc_gutenberg_check_disabled' );
	add_filter( 'gutenberg_can_edit_post_type', 'upper_gutenberg_check_disabled', 10, 3 ); // increased priority for second pass

	//vc_include_template( 'editors/partials/access-manager-js.tpl.php' );
	require_once( ABSPATH . PLUGINDIR . "/js_composer/js_composer.php" );
	
	global $post, $post_type;

	$upper_force_vc_backend = new Vc_Backend_Editor();
	$upper_force_vc_backend->registerScripts();
	$upper_force_vc_backend->addHooksSettings();
	$upper_force_vc_backend->render( $post_type );
}


add_action( 'enqueue_block_assets', 'upper_editor_frontend_scripts' );
function upper_editor_frontend_scripts() {
	wp_enqueue_style(
		'upper-block-frontend', // Handle.
		plugins_url( 'upper-block-frontend.css', __FILE__ ), 
		array( 'wp-blocks' ), 
		filemtime( plugin_dir_path( __FILE__ ) . 'upper-block-frontend.css' ) // filemtime — Gets file modification time.
	);
	
	/* pode acontecer o ULtimate addons nao estar activo. pensar nesse caso. */
} 

// overrides WPBakery check for gutenberg
if (!function_exists('upper_gutenberg_check_disabled')){
	function upper_gutenberg_check_disabled(){
		return false; // false forces the vc backend to load even with gutenberg on
	}
}