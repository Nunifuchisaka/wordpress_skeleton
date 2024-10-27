<?php
add_action('after_setup_theme', function() {

	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'html5', array(
		'style', 'script', 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption'
	) );
	add_theme_support( 'automatic-feed-links' );

	add_theme_support( 'post-formats', array(
		'aside', 'audio', 'chat', 'gallery', 'image', 'link', 'quote', 'status', 'video'
	) );

	// register_nav_menus( array(
	// 	'primary'				=> esc_html__( 'Main Navigation', 'graphy' ),
	// 	'header-social'	=> esc_html__( 'Header Social Links', 'graphy' ),
	// ) );

});
