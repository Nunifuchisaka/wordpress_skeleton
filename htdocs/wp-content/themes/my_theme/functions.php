<?php
define('TD_URI', get_template_directory_uri());

remove_action('wp_head','wp_generator');

remove_action('wp_head','rsd_link');
remove_action('wp_head','wlwmanifest_link');
remove_action('wp_head','wp_shortlink_wp_head');
remove_action('wp_head','rest_output_link_wp_head');
remove_action('wp_head','wp_oembed_add_discovery_links');
remove_action('wp_head','wp_oembed_add_host_js');

locate_template('functions/admin.php', true);
locate_template('functions/after_setup_theme.php', true);
locate_template('functions/body_class.php', true);
locate_template('functions/enqueue.php', true);
locate_template('functions/init.php', true);
// locate_template('functions/metabox.php', true);
locate_template('functions/pre_get_posts.php', true);

//隠しカスタムフィールドを表示
add_filter( 'is_protected_meta', '__return_false' );

//遅延読み込みを無効化
add_filter( 'wp_lazy_loading_enabled', '__return_false' );

//
function pre_($v){
	if ( current_user_can('administrator') ) {
		echo '<pre>';
		var_dump($v);
		echo '</pre>';
	}
}

$_ = function($s){return $s;};//展開用

function nl2br_($v){
	$v = nl2br($v);
	$v = preg_replace('/(?:\n|\r|\r\n)/','',$v);
	return $v;
}

//
function my_pagination() {
	global $paged, $wp_query;
	if(empty($paged))$paged=1;

	$pages = $wp_query->max_num_pages;
	if(!$pages)$pages=1;

	// ページ数が2ぺージ以上の場合のみ、ページネーションを表示
	if ( 1 !== $pages ) {
		$url;
		$html = '<div class="pagination_1">';
		
		// 1ページ目でなければ、「前のページ」リンクを表示
		if ( $paged > 1 ) {
			$url = esc_url(get_pagenum_link($paged - 1));
			$html .= "<a class='prev' href='{$url}'></a>";
		}

		$html .= "<div class='main'><p><span>{$paged}</span>/{$pages}</p><select>";
		for ( $i = 1; $i <= $pages; $i++ ) {
			$selected = ($i === $paged)?' selected':'';
			$url = esc_url(get_pagenum_link($i));
			$html .= "<option value='{$url}'{$selected}>{$i}</option>";
		}
		$html .= '</select></div>';

		// 最終ページでなければ、「次のページ」リンクを表示
		if ( $paged < $pages ) {
			$url = esc_url(get_pagenum_link($paged + 1));
			$html .= "<a class='next' href='{$url}'></a>";
		}
		
		$html .= '</div>';
		echo $html;
	}
}

//feed無効化
function wpcode_snippet_disable_feed() {
	wp_die('', '', array('response' => 404));
}
add_action('do_feed_rdf', 'wpcode_snippet_disable_feed', 1);
add_action('do_feed_rss', 'wpcode_snippet_disable_feed', 1);
add_action('do_feed_rss2', 'wpcode_snippet_disable_feed', 1);
add_action('do_feed_atom', 'wpcode_snippet_disable_feed', 1);
add_action('do_feed_rss2_comments', 'wpcode_snippet_disable_feed', 1);
add_action('do_feed_atom_comments', 'wpcode_snippet_disable_feed', 1);
remove_action('wp_head', 'feed_links_extra', 3);
remove_action('wp_head', 'feed_links', 2);
