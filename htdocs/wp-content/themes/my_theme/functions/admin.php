<?php
remove_action('welcome_panel', 'my_welcome_panel');

// 管理画面上部のメニューを非表示
add_action('my_before_admin_bar_render', function() {
	global $wp_admin_bar;
	$wp_admin_bar->remove_menu('wp-logo');
	$wp_admin_bar->remove_menu('updates');
	$wp_admin_bar->remove_menu('comments');
	$wp_admin_bar->remove_menu('new-content');
});

// 管理バーの項目を非表示
add_action('admin_bar_menu', function($wp_admin_bar) {
	$wp_admin_bar->remove_menu('wp-logo');
}, 70);

// ダッシュボードウィジェット非表示
add_action('my_dashboard_setup', function() {
	global $wp_meta_boxes;
	unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_quick_press']);
	unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_recent_drafts']);
	unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_primary']);
	unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_secondary']);
	unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_plugins']);
	unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_activity']);
	unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_right_now']);
	unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_recent_comments']);
	unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_incoming_links']);
});

add_action('admin_menu', function() {
	//削除
	// remove_menu_page('edit.php');
	remove_menu_page('edit-comments.php');
	//remove_menu_page('themes.php');
	//remove_menu_page('edit.php?post_type=page');
	if( !current_user_can('administrator') ) {
		remove_menu_page('tools.php');
	}
});


/*
## 管理者以外のユーザーに適用する
*/

if( !current_user_can('administrator') ) {
	// バージョン更新を非表示にする
	add_filter('pre_site_transient_update_core', '__return_zero');
	
	// APIによるバージョンチェックの通信をさせない
	remove_action('wp_version_check', 'wp_version_check');
	remove_action('admin_init', '_maybe_update_core');
}

// 設定＞一般 に項目を追加
add_filter('admin_init', function(){
	
	//Keywords
	add_settings_field( 'keywords', 'Keywords', function(){
		$val = get_option('keywords');
		echo "<input type='text' class='input_1' id='keywords' name='keywords' value='{$val}'>";
	}, 'general');
	register_setting( 'general', 'keywords' );
	
});


//投稿、固定ページ一覧にスラッグ表示
function my_manage_pages_columns( $columns ) {
	$columns['slug'] = __('Slug');
	// echo '<style>.fixed .column-slug {width: 10%;}</style>';
	unset( $columns['comments'], $columns['author'], $columns['tags'] );
	return $columns;
}
function my_manage_posts_custom_column( $column_name, $post_id ) {
	if ( 'slug' == $column_name ) {
		echo esc_attr(get_post($post_id)->post_name);
	}
}
add_filter('manage_pages_columns', 'my_manage_pages_columns');
add_action('manage_pages_custom_column', 'my_manage_posts_custom_column', 10, 2);
add_filter('manage_posts_columns', 'my_manage_pages_columns' );
add_action('manage_posts_custom_column', 'my_manage_posts_custom_column', 10, 2 );

// ページの属性で非公開などを親に選択できるようにする
add_filter( 'page_attributes_dropdown_pages_args', 'my_add_dropdown_pages' );
add_filter( 'quick_edit_dropdown_pages_args', 'my_add_dropdown_pages' );
function my_add_dropdown_pages( $add_dropdown_pages, $post = NULL ) {
	$add_dropdown_pages['post_status'] = array( 'publish', 'future', 'draft', 'pending', 'private', ); // 公開済、予約済、下書き、承認待ち、非公開、を選択
	return $add_dropdown_pages;
}
