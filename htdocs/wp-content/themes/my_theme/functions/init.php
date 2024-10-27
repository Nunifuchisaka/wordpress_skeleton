<?php
add_action('init', function() {
	remove_action('wp_head', 'print_emoji_detection_script', 7);
	remove_action('admin_print_scripts', 'print_emoji_detection_script');
	remove_action('wp_print_styles', 'print_emoji_styles');
	remove_action('admin_print_styles', 'print_emoji_styles');
	remove_filter('the_content_feed', 'wp_staticize_emoji');
	remove_filter('comment_text_rss', 'wp_staticize_emoji');
	remove_filter('wp_mail', 'wp_staticize_emoji_for_email');

	if ( !is_admin() ) {
		$author = filter_input(INPUT_GET, 'author');
		if( $author || preg_match('#/author/.+#', $_SERVER['REQUEST_URI']) ){
			wp_redirect( home_url( '/404.php' ) );
			exit;
		}
	}

}, 11, 0);