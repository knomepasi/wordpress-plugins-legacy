<?php

add_action( 'init', 'compact_chronological_block_init' );

function compact_chronological_block_init( ) {
	wp_register_script( 'compact-chronological-block', plugins_url( 'gutenberg.js', __FILE__ ), array( 'wp-blocks', 'wp-element' ) );

	register_block_type( 'compact-chronological/block', array( 'editor_script' => 'compact-chronological-block', 'render_callback' => 'compact_chronological_block' ) );
}

function compact_chronological_block( $attr, $content ) {
	ob_start( );
	//var_dump( $attr );
	$instance = array(
		'article_counts' => $attr['showPostCount']
	);
	the_widget( 'compact_chronological_Widget', $instance );
	return ob_get_clean( );
//	return 'show ' . $attributes['showPostCount'];
}