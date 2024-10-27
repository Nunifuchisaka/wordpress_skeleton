<?php
add_action('pre_get_posts', function($query) {
	if ( is_admin() || ! $query->is_main_query() ) return;

	// if (  ) {
	//   $query->set('post_type', '');
	//   $query->set('posts_per_page', );
	// }

});