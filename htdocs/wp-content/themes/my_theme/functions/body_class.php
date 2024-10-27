<?php
add_filter('body_class', function($classes = ''){
	if (is_page()) {
		$page = get_page(get_the_ID());
		$classes[] = 'page_'.$page->post_name;
		if ($page->post_parent) {
			$classes[] = 'page_'.get_page_uri($page->post_parent);
		}
	}
	return $classes;
});