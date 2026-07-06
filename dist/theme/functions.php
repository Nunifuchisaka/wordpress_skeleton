<?php
define('TD_URI', get_template_directory_uri());

remove_action('wp_head', 'wp_generator');
remove_action('wp_head', 'rsd_link');
remove_action('wp_head', 'wlwmanifest_link');
remove_action('wp_head', 'wp_shortlink_wp_head');
remove_action('wp_head', 'rest_output_link_wp_head');
remove_action('wp_head', 'wp_oembed_add_discovery_links');
remove_action('wp_head', 'wp_oembed_add_host_js');
remove_action('wp_head', 'wp_resource_hints', 2);
remove_action('wp_head', 'print_emoji_detection_script', 7);
remove_action('admin_print_scripts', 'print_emoji_detection_script');
remove_action('wp_print_styles', 'print_emoji_styles');
remove_action('admin_print_styles', 'print_emoji_styles');
remove_filter('the_content_feed', 'wp_staticize_emoji');
remove_filter('comment_text_rss', 'wp_staticize_emoji');
remove_filter('wp_mail', 'wp_staticize_emoji_for_email');

add_filter('is_protected_meta', '__return_false');
add_filter('wp_lazy_loading_enabled', '__return_false');

function pre_($v) {
  if (current_user_can('administrator')) {
    echo '<pre>';
    var_dump($v);
    echo '</pre>';
  }
}

function nl2br_($v) {
  $v = nl2br($v);
  $v = preg_replace('/(?:\n|\r|\r\n)/', '', $v);
  return $v;
}
?>
<?php
// 標準のウェルカムパネルを非表示（wp_welcome_panel が標準の名前です）
remove_action('welcome_panel', 'wp_welcome_panel');

// 管理画面上部のメニューを非表示
add_action('wp_before_admin_bar_render', function() {
  global $wp_admin_bar;
  $wp_admin_bar->remove_menu('wp-logo');
  $wp_admin_bar->remove_menu('updates');
  $wp_admin_bar->remove_menu('comments');
  $wp_admin_bar->remove_menu('new-content');
});

// ダッシュボードウィジェット非表示
add_action('wp_dashboard_setup', function() {
  remove_meta_box('dashboard_quick_press', 'dashboard', 'side');
  remove_meta_box('dashboard_recent_drafts', 'dashboard', 'side');
  remove_meta_box('dashboard_primary', 'dashboard', 'side');
  remove_meta_box('dashboard_secondary', 'dashboard', 'side');
  remove_meta_box('dashboard_plugins', 'dashboard', 'normal');
  remove_meta_box('dashboard_activity', 'dashboard', 'normal');
  remove_meta_box('dashboard_right_now', 'dashboard', 'normal');
  remove_meta_box('dashboard_recent_comments', 'dashboard', 'normal');
  remove_meta_box('dashboard_incoming_links', 'dashboard', 'normal');
});

// 管理メニューの制限
add_action('admin_menu', function() {
  remove_menu_page('edit-comments.php');
  if ( !current_user_can('administrator') ) {
    remove_menu_page('tools.php');
  }
});

/*
## 管理者以外のユーザーに適用する
*/
add_action('admin_init', function() {
  if ( !current_user_can('administrator') ) {
    add_filter('pre_site_transient_update_core', '__return_null');
    
    // APIによるバージョンチェックの通信をさせない
    remove_action('wp_version_check', 'wp_version_check');
    remove_action('admin_init', '_maybe_update_core');
  }
});

// 設定＞一般 に項目を追加
add_action('admin_init', function() {

  // Keywords
  add_settings_field( 'keywords', 'Keywords', function(){
    $val = get_option('keywords');
    // ★ 安全のため esc_attr() を追加しました
    echo "<input type='text' class='input_1' id='keywords' name='keywords' value='" . esc_attr($val) . "'>";
  }, 'general');
  register_setting( 'general', 'keywords' );

});

// 投稿、固定ページ一覧にスラッグ表示
function my_manage_pages_columns( $columns ) {
  $columns['slug'] = __('Slug');
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
  $add_dropdown_pages['post_status'] = array( 'publish', 'future', 'draft', 'pending', 'private' );
  return $add_dropdown_pages;
}

// 
add_action('admin_enqueue_scripts', function (){

  wp_enqueue_style(
    'my_admin_css',
    TD_URI . '/css/admin.css',
    array(),
    filemtime(TEMPLATEPATH . '/css/admin.css')
  );

  wp_enqueue_script(
    'my_admin_js',
    TD_URI . '/js/admin.js',
    array('jquery'),
    filemtime(TEMPLATEPATH . '/js/admin.js'),
    ['strategy' => 'defer']
  );

});
?>
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
  //   'primary' => esc_html__( 'Main Navigation', 'graphy' ),
  //   'header-social' => esc_html__( 'Header Social Links', 'graphy' ),
  // ) );

});
?>
<?php
add_filter('body_class', function ($classes = '') {
  if (is_page()) {
    $page = get_page(get_the_ID());
    $classes[] = 'page_' . $page->post_name;
    if ($page->post_parent) {
      $classes[] = 'page_' . get_page_uri($page->post_parent);
    }
  }
  return $classes;
});
?>
<?php
add_action('wp_enqueue_scripts', function (){
  global $wp_scripts;

  wp_dequeue_style('global-styles');
  wp_dequeue_style('wp-block-library');
  wp_dequeue_style('classic-theme-styles');

  wp_enqueue_style(
    'my_base_css',
    TD_URI . '/css/base.css',
    array(),
    filemtime(TEMPLATEPATH . '/css/base.css')
  );

  wp_enqueue_style(
    'my_common_css',
    TD_URI . '/css/common.css',
    array('my_base_css'),
    filemtime(TEMPLATEPATH . '/css/common.css')
  );

  // jquery-migrate.js を完全に削除する
  if (!is_admin() && isset($wp_scripts->registered['jquery'])) {
    $wp_scripts->registered['jquery']->deps = array_diff($wp_scripts->registered['jquery']->deps, array('jquery-migrate'));
  }

  wp_enqueue_script(
    'my_common_js',
    TD_URI . '/js/common.js',
    array('jquery'),
    filemtime(TEMPLATEPATH . '/js/common.js'),
    array(
      'strategy' => 'defer',
    )
  );

}, 100);
?>
<?php
add_action('init', function (){

  // 管理画面の裏側での自動通信（Heartbeat）を完全に停止する
  // wp_deregister_script('heartbeat');

  if (!is_admin()) {
    $author = filter_input(INPUT_GET, 'author');
    if ($author || preg_match('#/author/.+#', $_SERVER['REQUEST_URI'])) {
      wp_redirect(home_url('/404.php'));
      exit;
    }
  }

}, 1);
?>
<?php
// CSS・JSの末尾から不要なWordPress本体のバージョン表記（?ver=）を削除
function remove_wp_version_str($src) {
  if (strpos($src, 'ver=' . get_bloginfo('version'))) {
    $src = remove_query_arg('ver', $src);
  }
  return $src;
}
add_filter('script_loader_src', 'remove_wp_version_str', 15);
add_filter('style_loader_src', 'remove_wp_version_str', 15);
?>
<?php
add_action('pre_get_posts', function ($query) {
  if (is_admin() || !$query->is_main_query()) return;

  // if (  ) {
  //   $query->set('post_type', '');
  //   $query->set('posts_per_page', );
  // }

});
?>
<?php
// セルフピンバック（自演リンク通知）を禁止する
add_action('pre_ping', function(&$links) {
  $home = home_url();
  foreach ($links as $l => $link) {
    if (0 === strpos($link, $home)) {
      unset($links[$l]);
    }
  }
});
?>
<?php
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
?>
<?php
add_filter( 'ncf_register_fields', function( $configs ) {

  // --------------------------------------------------
  // 1. 使い回し用変数の定義
  // --------------------------------------------------
  
  // ラジオボタン定義（変数化）
  $common_radio = [
    'key'     => 'priority_radio',
    'label'   => '優先度 (Radio)',
    'type'    => 'radio',
    'options' => [
      'high'   => '高 (High)',
      'medium' => '中 (Medium)',
      'low'    => '低 (Low)',
    ],
  ];

  // チェックボックス定義（変数化）
  $common_check = [
    'key'     => 'options_check',
    'label'   => '設備オプション (Checkbox)',
    'type'    => 'checkbox',
    'options' => [
      'wifi'    => 'Wi-Fi',
      'power'   => '電源',
      'parking' => '駐車場',
    ],
    'desc'    => '複数選択可能です（配列で保存されます）',
  ];

  // --------------------------------------------------
  // 2. メタボックス定義
  // --------------------------------------------------

  $configs['ncf_full_spec_test'] = [
    'title'  => 'NCF 全フィールド入力テスト',
    'screen' => 'post',   // 投稿タイプ
    'context'=> 'normal', // 表示位置
    'fields' => [
      
      // --- 基本フィールド ---
      [
        'key'   => 'demo_text',
        'label' => '一行テキスト',
        'type'  => 'text',
        'desc'  => '標準的なテキスト入力です。',
      ],
      [
        'key'   => 'demo_textarea',
        'label' => 'テキストエリア',
        'type'  => 'textarea',
        'desc'  => '改行を含む文章用です。',
      ],
      [
        'key'     => 'demo_select',
        'label'   => 'セレクトボックス',
        'type'    => 'select',
        'options' => [
          ''        => '選択してください',
          'plan_a'  => 'Aプラン',
          'plan_b'  => 'Bプラン',
          'plan_c'  => 'Cプラン',
        ],
      ],

      // --- 選択系（変数利用） ---
      $common_radio,
      $common_check,

      // --- 入力形式バリエーション ---
      [
        'key'   => 'demo_number',
        'label' => '数値 (Number)',
        'type'  => 'number',
        'desc'  => '数値のみ入力できます。',
      ],
      [
        'key'   => 'demo_url',
        'label' => 'URL',
        'type'  => 'url',
        'desc'  => 'https:// から始まるURLを入力します。',
      ],
      [
        'key'   => 'demo_email',
        'label' => 'メールアドレス (Email)',
        'type'  => 'email',
      ],
      [
        'key'   => 'demo_date',
        'label' => '日付 (Date)',
        'type'  => 'date',
        'desc'  => 'カレンダーから日付を選択します。',
      ],
      [
        'key'   => 'demo_color',
        'label' => 'カラー (Color)',
        'type'  => 'color',
        'desc'  => 'カラーピッカーで色を選択します（HEX値で保存）。',
      ],

      // --- メディア・投稿関係 ---
      [
        'key'   => 'demo_image',
        'label' => 'メイン画像 (Image)',
        'type'  => 'image',
        'desc'  => 'メディアライブラリから選択します。',
      ],
      [
        'key'       => 'demo_post_select',
        'label'     => '関連記事 (Post Select)',
        'type'      => 'post',
        'post_type' => 'post', // 選択対象の投稿タイプ
        'desc'      => '既存の投稿記事を選択します。',
      ],
      [
        'key'       => 'demo_page_select',
        'label'     => '関連固定ページ',
        'type'      => 'post',
        'post_type' => 'page', // 固定ページを選択
      ],

      // --- リピーターフィールド ---
      [
        'key'        => 'demo_repeater',
        'label'      => 'リピーター (複合テスト)',
        'type'       => 'repeater',
        'desc'       => '以下のセットを好きなだけ増やせます。',
        'sub_fields' => [
          [
            'key'   => 'sub_title',
            'label' => '見出し',
            'type'  => 'text',
          ],
          [
            'key'   => 'sub_image',
            'label' => '写真',
            'type'  => 'image',
          ],
          // リピーター内でも変数を再利用可能
          $common_radio, 
          $common_check,
          [
            'key'       => 'sub_link_post',
            'label'     => 'リンク先記事',
            'type'      => 'post',
            'post_type' => 'post',
          ],
          [
            'key'   => 'sub_url',
            'label' => '外部リンク',
            'type'  => 'url',
          ],
        ],
      ],

    ],
  ];

  return $configs;
});
?>
<?php
function my_pagination() {
  global $paged, $wp_query;
  if (empty($paged)) $paged = 1;

  $pages = $wp_query->max_num_pages;
  if (!$pages) $pages = 1;

  // ページ数が2ぺージ以上の場合のみ、ページネーションを表示
  if (1 !== $pages) {
    $url;
    $html = '<div class="pagination_1">';

    // 1ページ目でなければ、「前のページ」リンクを表示
    if ($paged > 1) {
      $url = esc_url(get_pagenum_link($paged - 1));
      $html .= "<a class='prev' href='{$url}'></a>";
    }

    $html .= "<div class='main'><p><span>{$paged}</span>/{$pages}</p><select>";
    for ($i = 1; $i <= $pages; $i++) {
      $selected = ($i === $paged) ? ' selected' : '';
      $url = esc_url(get_pagenum_link($i));
      $html .= "<option value='{$url}'{$selected}>{$i}</option>";
    }
    $html .= '</select></div>';

    // 最終ページでなければ、「次のページ」リンクを表示
    if ($paged < $pages) {
      $url = esc_url(get_pagenum_link($paged + 1));
      $html .= "<a class='next' href='{$url}'></a>";
    }

    $html .= '</div>';
    echo $html;
  }
}
?>
