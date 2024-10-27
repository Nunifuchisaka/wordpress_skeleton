<?php
add_action('wp_enqueue_scripts', 'my_enqueue_scripts');
add_action('admin_print_scripts', 'my_admin_enqueue');

function my_enqueue_scripts() {
	global $post;

	wp_enqueue_style(
		'base',
		TD_URI.'/css/base.css',
		array(),
		filemtime(TEMPLATEPATH.'/css/base.css')
	);

	wp_enqueue_style(
		'common',
		TD_URI.'/css/common.css',
		array(),
		filemtime(TEMPLATEPATH.'/css/common.css')
	);

	wp_enqueue_script(
		'common',
		TD_URI.'/js/common.js',
		array(),
		filemtime(TEMPLATEPATH.'/js/common.js'),
		array(
			'strategy' => 'defer',
		)
	);
	
}

function my_admin_enqueue() {
	
	wp_enqueue_style(
		'admin',
		TD_URI.'/css/admin.css',
		array(),
		filemtime(TEMPLATEPATH.'/css/admin.css')
	);

	wp_enqueue_script(
		'admin',
		TD_URI.'/js/admin.js',
		['jquery'],
		filemtime(TEMPLATEPATH.'/js/admin.js'),
		['strategy' => 'defer']);

}
