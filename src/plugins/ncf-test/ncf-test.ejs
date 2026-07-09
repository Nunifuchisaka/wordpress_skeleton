<?php
/**
 * Plugin Name: NCF Test
 * Description: Nunifuchisaka Custom Fields（NCF）の保存・サニタイズ・描画を実際のWordPress環境上で検証する開発用テストランナー
 * Version: 1.0.0
 * Author: Nunifuchisaka
 * License: GPL2
 */

namespace NCF_Test;

if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

/**
 * テスト実行本体
 *
 * テスト用の投稿（下書き）を作成し、編集画面からの保存リクエストを再現して
 * NCFの公開メソッドを直接呼び出して検証する。終了後にテスト投稿は削除する。
 * テーマ側が ncf_register_fields 等に登録したフックは実行中だけ退避し、
 * テストが定義したフィールドだけで決定的に動くようにしている。
 */
class Runner {

  // NCF本体のnonce定義と一致させる（変更されたらテストが失敗して検知できる）
  const NONCE_ACTION = 'ncf_nonce_action';
  const NONCE_FIELD  = 'ncf_nonce_action_field';

  // 実行中だけ退避するNCFのフック
  const ISOLATED_HOOKS = [ 'ncf_register_fields', 'ncf_after_save', 'ncf_show_output_code' ];

  private $results        = [];
  private $group          = '';
  private $fixtures       = [];
  private $filter_backups = [];
  private $ncf;

  /**
   * 全テストを実行して結果の配列を返す
   */
  public function run() {
    $this->ncf = new \NCF\Custom_Fields();
    $this->isolate_hooks();

    try {
      $this->test_meta_box_registration();
      $this->test_sanitize_text_types();
      $this->test_sanitize_choice_types();
      $this->test_sanitize_id_types();
      $this->test_sanitize_callback();
      $this->test_repeater_save();
      $this->test_delete_and_keep();
      $this->test_save_guards();
      $this->test_after_save_action();
      $this->test_render_meta_box();
      $this->test_output_code_visibility();
    } catch ( \Throwable $e ) {
      $this->group = '実行エラー';
      $this->fail( 'テストが例外で中断しました', $e->getMessage() . ' @ ' . $e->getFile() . ':' . $e->getLine() );
    } finally {
      $this->cleanup();
      $this->restore_hooks();
    }

    return $this->results;
  }

  // ---------------------------------------------------------------
  // テストケース
  // ---------------------------------------------------------------

  /**
   * ncf_register_fields で登録した設定がメタボックスとして登録されること
   */
  private function test_meta_box_registration() {
    $this->group = 'メタボックス登録';

    global $wp_meta_boxes;
    $backup = $wp_meta_boxes;

    $configs = [
      'ncf_test_reg_box' => [
        'title'   => 'NCFテストボックス',
        'screen'  => 'post',
        'context' => 'side',
        'fields'  => [ [ 'key' => 'reg_check', 'label' => '確認', 'type' => 'text' ] ],
      ],
    ];
    $filter = function () use ( $configs ) {
      return $configs;
    };
    add_filter( 'ncf_register_fields', $filter, 99 );
    $this->ncf->register_meta_boxes();
    remove_filter( 'ncf_register_fields', $filter, 99 );

    $box = $wp_meta_boxes['post']['side']['default']['ncf_test_reg_box'] ?? null;
    $this->assert_true( is_array( $box ), '指定したscreen/contextにメタボックスが登録される' );
    $this->assert_same( 'NCFテストボックス', $box['title'] ?? null, 'メタボックスのタイトルが設定通りになる' );

    $wp_meta_boxes = $backup;
  }

  /**
   * テキスト系フィールド型のサニタイズ
   */
  private function test_sanitize_text_types() {
    $this->group = 'サニタイズ（テキスト系）';
    $post_id = $this->make_post();

    $fields = [
      [ 'key' => 't_text',      'label' => 'text',     'type' => 'text' ],
      [ 'key' => 't_text_arr',  'label' => 'text',     'type' => 'text' ],
      [ 'key' => 't_textarea',  'label' => 'textarea', 'type' => 'textarea' ],
      [ 'key' => 't_num',       'label' => 'number',   'type' => 'number' ],
      [ 'key' => 't_num_bad',   'label' => 'number',   'type' => 'number' ],
      [ 'key' => 't_num_arr',   'label' => 'number',   'type' => 'number' ],
      [ 'key' => 't_url',       'label' => 'url',      'type' => 'url' ],
      [ 'key' => 't_url_bad',   'label' => 'url',      'type' => 'url' ],
      [ 'key' => 't_email',     'label' => 'email',    'type' => 'email' ],
      [ 'key' => 't_email_bad', 'label' => 'email',    'type' => 'email' ],
      [ 'key' => 't_date',      'label' => 'date',     'type' => 'date' ],
      [ 'key' => 't_date_bad',  'label' => 'date',     'type' => 'date' ],
      [ 'key' => 't_color',     'label' => 'color',    'type' => 'color' ],
      [ 'key' => 't_color_bad', 'label' => 'color',    'type' => 'color' ],
    ];
    $this->simulate_save( $post_id, $this->box( $fields ), [
      'ncf_t_text'      => '<b>Hello</b> World',
      'ncf_t_text_arr'  => [ 'Hello' ],
      'ncf_t_textarea'  => "line1\nline2<script>alert(1)</script>",
      'ncf_t_num'       => '42',
      'ncf_t_num_bad'   => 'abc',
      'ncf_t_num_arr'   => [ '42' ],
      'ncf_t_url'       => 'https://example.com/path?a=1&b=2',
      'ncf_t_url_bad'   => 'javascript:alert(1)',
      'ncf_t_email'     => 'foo@example.com',
      'ncf_t_email_bad' => 'not-an-email',
      'ncf_t_date'      => '2026-07-06',
      'ncf_t_date_bad'  => '2026/07/06',
      'ncf_t_color'     => '#ff8800',
      'ncf_t_color_bad' => 'red',
    ] );

    $this->assert_same( 'Hello World', $this->meta( $post_id, 't_text' ), 'text: HTMLタグが除去される' );
    $this->assert_same( '', $this->meta( $post_id, 't_text_arr' ), 'text: 配列POSTは保存されない' );
    $this->assert_same( "line1\nline2", $this->meta( $post_id, 't_textarea' ), 'textarea: 改行を保持しつつscriptが除去される' );
    $this->assert_same( '42', $this->meta( $post_id, 't_num' ), 'number: 数値が保存される' );
    $this->assert_same( '', $this->meta( $post_id, 't_num_bad' ), 'number: 数値以外は空で保存される' );
    $this->assert_same( '', $this->meta( $post_id, 't_num_arr' ), 'number: 配列POSTは保存されない' );
    $this->assert_same( 'https://example.com/path?a=1&b=2', $this->meta( $post_id, 't_url' ), 'url: 正常なURLはそのまま保存される' );
    $this->assert_not_contains( 'javascript:', $this->meta( $post_id, 't_url_bad' ), 'url: javascript:スキームが除去される' );
    $this->assert_same( 'foo@example.com', $this->meta( $post_id, 't_email' ), 'email: 正常なメールアドレスが保存される' );
    $this->assert_same( '', $this->meta( $post_id, 't_email_bad' ), 'email: 不正な値は空で保存される' );
    $this->assert_same( '2026-07-06', $this->meta( $post_id, 't_date' ), 'date: YYYY-MM-DD形式が保存される' );
    $this->assert_same( '', $this->meta( $post_id, 't_date_bad' ), 'date: 形式外の値は空で保存される' );
    $this->assert_same( '#ff8800', $this->meta( $post_id, 't_color' ), 'color: #rrggbb形式が保存される' );
    $this->assert_same( '', $this->meta( $post_id, 't_color_bad' ), 'color: 形式外の値は空で保存される' );
  }

  /**
   * 選択系フィールド型（options定義外の値を保存しないこと）
   */
  private function test_sanitize_choice_types() {
    $this->group = 'サニタイズ（選択系）';
    $post_id = $this->make_post();
    $options = [ 'red' => '赤', 'green' => '緑', 'blue' => '青' ];

    $fields = [
      [ 'key' => 'c_select',     'label' => 'select',   'type' => 'select',   'options' => $options ],
      [ 'key' => 'c_select_bad', 'label' => 'select',   'type' => 'select',   'options' => $options ],
      [ 'key' => 'c_radio',      'label' => 'radio',    'type' => 'radio',    'options' => $options ],
      [ 'key' => 'c_radio_bad',  'label' => 'radio',    'type' => 'radio',    'options' => $options ],
      [ 'key' => 'c_check',      'label' => 'checkbox', 'type' => 'checkbox', 'options' => $options ],
      [ 'key' => 'c_select_arr', 'label' => 'select',   'type' => 'select',   'options' => $options ],
      [ 'key' => 'c_radio_arr',  'label' => 'radio',    'type' => 'radio',    'options' => $options ],
    ];
    $this->simulate_save( $post_id, $this->box( $fields ), [
      'ncf_c_select'     => 'green',
      'ncf_c_select_bad' => 'purple',
      'ncf_c_radio'      => 'blue',
      'ncf_c_radio_bad'  => 'purple',
      'ncf_c_check'      => [ 'red', 'purple', 'blue' ],
      'ncf_c_select_arr' => [ 'green' ],
      'ncf_c_radio_arr'  => [ 'blue' ],
    ] );

    $this->assert_same( 'green', $this->meta( $post_id, 'c_select' ), 'select: options内の値が保存される' );
    $this->assert_same( '', $this->meta( $post_id, 'c_select_bad' ), 'select: options外の値は空で保存される' );
    $this->assert_same( 'blue', $this->meta( $post_id, 'c_radio' ), 'radio: options内の値が保存される' );
    $this->assert_same( '', $this->meta( $post_id, 'c_radio_bad' ), 'radio: options外の値は空で保存される' );
    $this->assert_same( [ 'red', 'blue' ], $this->meta( $post_id, 'c_check' ), 'checkbox: options内の値だけが配列で保存される' );
    $this->assert_same( '', $this->meta( $post_id, 'c_select_arr' ), 'select: 配列POSTは保存されない' );
    $this->assert_same( '', $this->meta( $post_id, 'c_radio_arr' ), 'radio: 配列POSTは保存されない' );
  }

  /**
   * ID系フィールド型（image / post）
   */
  private function test_sanitize_id_types() {
    $this->group = 'サニタイズ（ID系）';
    $post_id   = $this->make_post();
    $target_id = $this->make_post( [ 'post_title' => 'NCF Test 選択対象', 'post_status' => 'publish' ] );
    $page_id   = $this->make_post( [ 'post_title' => 'NCF Test 固定ページ', 'post_type' => 'page' ] );
    $image_id  = $this->make_attachment( 'NCF Test 画像', 'image/jpeg' );
    $pdf_id    = $this->make_attachment( 'NCF Test PDF', 'application/pdf' );

    $fields = [
      [ 'key' => 'i_image',       'label' => 'image', 'type' => 'image' ],
      [ 'key' => 'i_image_empty', 'label' => 'image', 'type' => 'image' ],
      [ 'key' => 'i_image_arr',   'label' => 'image', 'type' => 'image' ],
      [ 'key' => 'i_image_post',  'label' => 'image', 'type' => 'image' ],
      [ 'key' => 'i_image_pdf',   'label' => 'image', 'type' => 'image' ],
      [ 'key' => 'i_image_none',  'label' => 'image', 'type' => 'image' ],
      [ 'key' => 'i_post',        'label' => 'post',  'type' => 'post' ],
      [ 'key' => 'i_post_wrong',  'label' => 'post',  'type' => 'post' ],
      [ 'key' => 'i_post_none',   'label' => 'post',  'type' => 'post' ],
      [ 'key' => 'i_post_arr',    'label' => 'post',  'type' => 'post' ],
    ];
    $this->simulate_save( $post_id, $this->box( $fields ), [
      'ncf_i_image'       => (string) $image_id,
      'ncf_i_image_empty' => '',
      'ncf_i_image_arr'   => [ (string) $image_id ],
      'ncf_i_image_post'  => (string) $target_id,
      'ncf_i_image_pdf'   => (string) $pdf_id,
      'ncf_i_image_none'  => '999999999',
      'ncf_i_post'        => (string) $target_id,
      'ncf_i_post_wrong'  => (string) $page_id,
      'ncf_i_post_none'   => '999999999',
      'ncf_i_post_arr'    => [ (string) $target_id ],
    ] );

    $this->assert_same( (string) $image_id, $this->meta( $post_id, 'i_image' ), 'image: 実在する画像添付ファイルIDが保存される' );
    $this->assert_same( '', $this->meta( $post_id, 'i_image_empty' ), 'image: 空値は空のまま保存される' );
    $this->assert_same( '', $this->meta( $post_id, 'i_image_arr' ), 'image: 配列POSTは保存されない' );
    $this->assert_same( '', $this->meta( $post_id, 'i_image_post' ), 'image: 通常投稿IDは保存されない' );
    $this->assert_same( '', $this->meta( $post_id, 'i_image_pdf' ), 'image: 画像ではない添付ファイルIDは保存されない' );
    $this->assert_same( '', $this->meta( $post_id, 'i_image_none' ), 'image: 実在しないIDは保存されない' );
    $this->assert_same( (string) $target_id, $this->meta( $post_id, 'i_post' ), 'post: 実在する対象post_typeの投稿IDが保存される' );
    $this->assert_same( '', $this->meta( $post_id, 'i_post_wrong' ), 'post: 対象外post_typeのIDは空で保存される' );
    $this->assert_same( '', $this->meta( $post_id, 'i_post_none' ), 'post: 実在しないIDは空で保存される' );
    $this->assert_same( '', $this->meta( $post_id, 'i_post_arr' ), 'post: 配列POSTは保存されない' );
  }

  /**
   * sanitize_callback による差し替え
   */
  private function test_sanitize_callback() {
    $this->group = 'sanitize_callback';
    $post_id = $this->make_post();

    $fields = [
      [
        'key'               => 'cb_field',
        'label'             => 'callback',
        'type'              => 'text',
        'sanitize_callback' => function ( $value, $field ) {
          return 'CB:' . strtoupper( sanitize_text_field( $value ) ) . ':' . $field['key'];
        },
      ],
    ];
    $this->simulate_save( $post_id, $this->box( $fields ), [ 'ncf_cb_field' => 'abc' ] );

    $this->assert_same( 'CB:ABC:cb_field', $this->meta( $post_id, 'cb_field' ), 'sanitize_callbackが値とフィールド定義を受け取って適用される' );
  }

  /**
   * リピーターの保存（テンプレート行・空行のスキップ、未定義キーの除去、サブフィールドのサニタイズ）
   */
  private function test_repeater_save() {
    $this->group = 'リピーター';
    $post_id = $this->make_post();

    $fields = [
      [
        'key'        => 'rep',
        'label'      => 'リピーター',
        'type'       => 'repeater',
        'sub_fields' => [
          [ 'key' => 'name', 'label' => '名前', 'type' => 'text' ],
          [ 'key' => 'num',  'label' => '数',   'type' => 'number' ],
        ],
      ],
    ];
    $this->simulate_save( $post_id, $this->box( $fields ), [
      'ncf_rep' => [
        '{index}' => [ 'name' => 'テンプレート行', 'num' => '9' ],
        0         => [ 'name' => 'Alice', 'num' => '1', 'evil' => 'x' ],
        1         => [ 'name' => '', 'num' => '' ],
        2         => [ 'name' => '<b>Bob</b>', 'num' => 'abc' ],
      ],
    ] );

    $expected = [
      [ 'name' => 'Alice', 'num' => 1 ],
      [ 'name' => 'Bob', 'num' => '' ],
    ];
    $this->assert_same( $expected, $this->meta( $post_id, 'rep' ), 'テンプレート行と空行はスキップ、未定義キーは除去、サブフィールドはサニタイズされる' );
  }

  /**
   * 送信されなかったフィールドの扱い（checkbox等は削除、text等は保持）
   */
  private function test_delete_and_keep() {
    $this->group = '未送信フィールドの扱い';
    $post_id = $this->make_post();

    update_post_meta( $post_id, 'ncf_d_check', [ 'red' ] );
    update_post_meta( $post_id, 'ncf_d_image', 12 );
    update_post_meta( $post_id, 'ncf_d_text', 'keep me' );

    $fields = [
      [ 'key' => 'd_check', 'label' => 'checkbox', 'type' => 'checkbox', 'options' => [ 'red' => '赤' ] ],
      [ 'key' => 'd_image', 'label' => 'image',    'type' => 'image' ],
      [ 'key' => 'd_text',  'label' => 'text',     'type' => 'text' ],
    ];
    // どのフィールドも$_POSTに含めずに保存する
    $this->simulate_save( $post_id, $this->box( $fields ), [] );

    $this->assert_same( '', $this->meta( $post_id, 'd_check' ), 'checkbox: 全て外して保存するとメタが削除される' );
    $this->assert_same( '', $this->meta( $post_id, 'd_image' ), 'image: 削除して保存するとメタが削除される' );
    $this->assert_same( 'keep me', $this->meta( $post_id, 'd_text' ), 'text: 未送信でも既存のメタは削除されない' );
  }

  /**
   * 保存処理のガード（nonce・対象post_type）
   */
  private function test_save_guards() {
    $this->group = '保存ガード';
    $post_id = $this->make_post();

    update_post_meta( $post_id, 'ncf_g_text', 'before' );
    $fields = [ [ 'key' => 'g_text', 'label' => 'text', 'type' => 'text' ] ];

    // nonceなし
    $this->simulate_save( $post_id, $this->box( $fields ), [ 'ncf_g_text' => 'after' ], false );
    $this->assert_same( 'before', $this->meta( $post_id, 'g_text' ), 'nonceがない場合は保存されない' );

    // 不正なnonce
    $this->simulate_save( $post_id, $this->box( $fields ), [ 'ncf_g_text' => 'after' ], 'invalid-nonce' );
    $this->assert_same( 'before', $this->meta( $post_id, 'g_text' ), 'nonceが不正な場合は保存されない' );

    // 対象外のpost_type（screen: page のフィールドをpostに保存）
    $this->simulate_save( $post_id, $this->box( $fields, [ 'screen' => 'page' ] ), [ 'ncf_g_text' => 'after' ] );
    $this->assert_same( 'before', $this->meta( $post_id, 'g_text' ), '対象外post_typeのフィールドは保存も削除もされない' );
  }

  /**
   * ncf_after_save アクションの発火と引数
   */
  private function test_after_save_action() {
    $this->group = 'ncf_after_save';
    $post_id = $this->make_post();

    update_post_meta( $post_id, 'ncf_a_check', [ 'red' ] );

    $calls = [];
    $hook  = function ( $saved_post_id, $saved ) use ( &$calls ) {
      $calls[] = [ $saved_post_id, $saved ];
    };
    add_action( 'ncf_after_save', $hook, 10, 2 );

    $fields = [
      [ 'key' => 'a_text',  'label' => 'text',     'type' => 'text' ],
      [ 'key' => 'a_check', 'label' => 'checkbox', 'type' => 'checkbox', 'options' => [ 'red' => '赤' ] ],
    ];
    $this->simulate_save( $post_id, $this->box( $fields ), [ 'ncf_a_text' => 'hello' ] );

    remove_action( 'ncf_after_save', $hook, 10 );

    $this->assert_same( 1, count( $calls ), '保存完了時に1回だけ発火する' );
    $saved = $calls[0][1] ?? [];
    $this->assert_same( $post_id, $calls[0][0] ?? null, '第1引数に投稿IDが渡される' );
    $this->assert_same( 'hello', $saved['ncf_a_text'] ?? null, '$savedに保存したメタキーと値が含まれる' );
    $this->assert_true( array_key_exists( 'ncf_a_check', $saved ) && null === $saved['ncf_a_check'], '削除されたキーは$savedにnullで含まれる' );
  }

  /**
   * メタボックスの描画HTML
   */
  private function test_render_meta_box() {
    $this->group = '描画';
    $post_id = $this->make_post();
    update_post_meta( $post_id, 'ncf_r_text', 'こんにちは' );

    $html = $this->render( $post_id, [
      [ 'key' => 'r_text', 'label' => 'テキスト', 'type' => 'text', 'desc' => '説明文です' ],
      [ 'key' => 'r_img',  'label' => '画像',     'type' => 'image' ],
      [ 'key' => 'r_post', 'label' => '投稿',     'type' => 'post' ],
      [ 'key' => 'main-visual', 'label' => '安全な変数名', 'type' => 'text' ],
      [
        'key'        => 'r_rep',
        'label'      => 'リピーター',
        'type'       => 'repeater',
        'sub_fields' => [
          [ 'key' => 'name', 'label' => '名前', 'type' => 'text' ],
          [ 'key' => 'related-post', 'label' => '関連記事', 'type' => 'post' ],
        ],
      ],
    ] );

    $this->assert_contains( 'name="' . self::NONCE_FIELD . '"', $html, 'nonceフィールドが出力される' );
    $this->assert_contains( 'name="ncf_r_text"', $html, 'メタキー（ncf_プレフィックス付き）がname属性になる' );
    $this->assert_contains( 'value="こんにちは"', $html, '保存済みの値が初期値として出力される' );
    $this->assert_contains( 'id="ncf_field_ncf_r_text"', $html, '入力欄にid属性が付与される' );
    $this->assert_contains( 'for="ncf_field_ncf_r_text"', $html, 'labelのfor属性が入力欄のidと一致する' );
    $this->assert_contains( '説明文です', $html, 'descが説明文として出力される' );
    $this->assert_contains( 'data-parent="ncf_r_rep"', $html, 'リピーターのラッパーが出力される' );
    $this->assert_contains( '[{index}]', $html, 'リピーターのテンプレート行（{index}プレースホルダー）が出力される' );
    $this->assert_contains( 'ncf-add-row', $html, 'リピーターの行追加ボタンが出力される' );
    $this->assert_contains( 'ncf-code-details', $html, '出力コード欄が既定で表示される' );
    $this->assert_contains( '$r_rep_rows', $html, '出力コードにリピーターのforeachコードが含まれる' );
    $this->assert_contains( '$main_visual = get_post_meta', $html, '出力コードではハイフン付きkeyが安全なPHP変数名に変換される' );
    $this->assert_not_contains( '$main-visual', $html, '出力コードに不正なPHP変数名が含まれない' );
    $this->assert_contains( 'esc_url( get_permalink', $html, 'post型の出力コードはURLをエスケープする' );
    $this->assert_contains( 'esc_html( get_the_title', $html, 'post型の出力コードはタイトルをエスケープする' );
  }

  /**
   * ncf_show_output_code フィルターによる出力コード欄の非表示
   */
  private function test_output_code_visibility() {
    $this->group = 'ncf_show_output_code';
    $post_id = $this->make_post();
    $fields  = [ [ 'key' => 'v_text', 'label' => 'テキスト', 'type' => 'text' ] ];

    add_filter( 'ncf_show_output_code', '__return_false' );
    $html = $this->render( $post_id, $fields );
    remove_filter( 'ncf_show_output_code', '__return_false' );

    $this->assert_not_contains( 'ncf-code-details', $html, 'falseを返すと出力コード欄が表示されない' );
  }

  // ---------------------------------------------------------------
  // ヘルパー
  // ---------------------------------------------------------------

  /**
   * テスト用のメタボックス設定をひとつ作る
   */
  private function box( array $fields, array $overrides = [] ) {
    return [
      'ncf_test_box' => array_merge( [
        'title'  => 'NCF Test',
        'screen' => 'post',
        'fields' => $fields,
      ], $overrides ),
    ];
  }

  /**
   * テスト用の投稿を作成する（終了後にcleanupで削除される）
   */
  private function make_post( array $args = [] ) {
    $post_id = wp_insert_post( wp_parse_args( $args, [
      'post_title'  => 'NCF Test Fixture',
      'post_type'   => 'post',
      'post_status' => 'draft',
    ] ) );
    if ( $post_id && ! is_wp_error( $post_id ) ) {
      $this->fixtures[] = $post_id;
    }
    return $post_id;
  }

  private function make_attachment( $title, $mime_type ) {
    $post_id = wp_insert_attachment( [
      'post_title'     => $title,
      'post_type'      => 'attachment',
      'post_status'    => 'inherit',
      'post_mime_type' => $mime_type,
    ] );
    if ( $post_id && ! is_wp_error( $post_id ) ) {
      $ext = ( 'image/jpeg' === $mime_type ) ? 'jpg' : 'pdf';
      update_attached_file( $post_id, 'ncf-test-' . $post_id . '.' . $ext );
      $this->fixtures[] = $post_id;
    }
    return $post_id;
  }

  /**
   * 編集画面からの保存リクエストを再現してNCFの保存処理を呼ぶ
   *
   * $post_data には「ブラウザが送信する値」を渡す（WordPressが$_POSTに
   * スラッシュを付与する挙動を wp_slash で再現している）。
   * $nonce は true=正規のnonce / false=nonceなし / 文字列=その値をそのまま使用。
   */
  private function simulate_save( $post_id, array $configs, array $post_data, $nonce = true ) {
    $filter = function () use ( $configs ) {
      return $configs;
    };
    add_filter( 'ncf_register_fields', $filter, 99 );

    $backup = $_POST;
    $_POST  = wp_slash( $post_data );
    if ( true === $nonce ) {
      $_POST[ self::NONCE_FIELD ] = wp_create_nonce( self::NONCE_ACTION );
    } elseif ( is_string( $nonce ) ) {
      $_POST[ self::NONCE_FIELD ] = $nonce;
    }

    try {
      $this->ncf->save_meta_data( $post_id );
    } finally {
      $_POST = $backup;
      remove_filter( 'ncf_register_fields', $filter, 99 );
    }
  }

  /**
   * メタボックスを描画してHTMLを返す
   */
  private function render( $post_id, array $fields ) {
    ob_start();
    $this->ncf->render_meta_box( get_post( $post_id ), [ 'args' => $fields ] );
    return ob_get_clean();
  }

  /**
   * ncf_プレフィックス付きでメタ値を取得する
   */
  private function meta( $post_id, $key ) {
    return get_post_meta( $post_id, 'ncf_' . $key, true );
  }

  /**
   * テーマ等が登録済みのNCFフックを実行中だけ退避する
   */
  private function isolate_hooks() {
    foreach ( self::ISOLATED_HOOKS as $hook ) {
      $this->filter_backups[ $hook ] = $GLOBALS['wp_filter'][ $hook ] ?? null;
      unset( $GLOBALS['wp_filter'][ $hook ] );
    }
  }

  private function restore_hooks() {
    foreach ( $this->filter_backups as $hook => $backup ) {
      if ( null === $backup ) {
        unset( $GLOBALS['wp_filter'][ $hook ] );
      } else {
        $GLOBALS['wp_filter'][ $hook ] = $backup;
      }
    }
    $this->filter_backups = [];
  }

  private function cleanup() {
    foreach ( $this->fixtures as $post_id ) {
      wp_delete_post( $post_id, true );
    }
    $this->fixtures = [];
  }

  // ---------------------------------------------------------------
  // アサーション
  // ---------------------------------------------------------------

  private function assert_same( $expected, $actual, $label ) {
    if ( $expected === $actual ) {
      $this->pass( $label );
    } else {
      $this->fail( $label, '期待値: ' . $this->export( $expected ) . ' / 実際: ' . $this->export( $actual ) );
    }
  }

  private function assert_true( $condition, $label ) {
    if ( $condition ) {
      $this->pass( $label );
    } else {
      $this->fail( $label, '条件がfalseでした' );
    }
  }

  private function assert_contains( $needle, $haystack, $label ) {
    if ( is_string( $haystack ) && false !== strpos( $haystack, $needle ) ) {
      $this->pass( $label );
    } else {
      $this->fail( $label, '「' . $needle . '」が出力に含まれていません' );
    }
  }

  private function assert_not_contains( $needle, $haystack, $label ) {
    if ( ! is_string( $haystack ) || false === strpos( $haystack, $needle ) ) {
      $this->pass( $label );
    } else {
      $this->fail( $label, '「' . $needle . '」が出力に含まれています' );
    }
  }

  private function pass( $label ) {
    $this->results[] = [ 'group' => $this->group, 'label' => $label, 'pass' => true, 'detail' => '' ];
  }

  private function fail( $label, $detail ) {
    $this->results[] = [ 'group' => $this->group, 'label' => $label, 'pass' => false, 'detail' => $detail ];
  }

  private function export( $value ) {
    return wp_json_encode( $value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
  }
}

/**
 * 管理画面「ツール > NCF Test」
 */
class Admin_Page {

  public function __construct() {
    add_action( 'admin_menu', [ $this, 'add_page' ] );
  }

  public function add_page() {
    add_management_page( 'NCF Test', 'NCF Test', 'manage_options', 'ncf-test', [ $this, 'render_page' ] );
  }

  public function render_page() {
    $results = null;
    $error   = '';

    if ( isset( $_POST['ncf_test_run'] ) ) {
      check_admin_referer( 'ncf_test_run_action', 'ncf_test_run_nonce' );

      if ( ! class_exists( '\NCF\Custom_Fields' ) ) {
        $error = 'Nunifuchisaka Custom Fields が有効化されていません。プラグインを有効化してから実行してください。';
      } else {
        $runner  = new Runner();
        $results = $runner->run();
      }
    }
    ?>
    <div class="wrap ncf-test-wrap">
      <h1>NCF Test</h1>
      <p>
        Nunifuchisaka Custom Fields の保存・サニタイズ・描画を、このWordPress環境上で実際に動かして検証します。<br>
        テスト用の投稿（下書き）を一時的に作成し、終了後に自動で削除します。
      </p>

      <?php if ( $error ) : ?>
        <div class="notice notice-error"><p><?php echo esc_html( $error ); ?></p></div>
      <?php endif; ?>

      <form method="post">
        <?php wp_nonce_field( 'ncf_test_run_action', 'ncf_test_run_nonce' ); ?>
        <p><button type="submit" name="ncf_test_run" value="1" class="button button-primary">テストを実行</button></p>
      </form>

      <?php if ( is_array( $results ) ) : ?>
        <?php $this->render_results( $results ); ?>
      <?php endif; ?>
    </div>
    <?php
  }

  private function render_results( array $results ) {
    $total  = count( $results );
    $passed = count( array_filter( $results, function ( $r ) {
      return $r['pass'];
    } ) );
    $failed = $total - $passed;
    ?>
    <style>
      .ncf-test-summary { font-size: 14px; margin: 1em 0; }
      .ncf-test-summary .ncf-test-ok { color: #00a32a; font-weight: 700; }
      .ncf-test-summary .ncf-test-ng { color: #d63638; font-weight: 700; }
      .ncf-test-badge { display: inline-block; min-width: 3.5em; padding: 1px 8px; border-radius: 3px; color: #fff; font-size: 12px; text-align: center; }
      .ncf-test-badge-pass { background: #00a32a; }
      .ncf-test-badge-fail { background: #d63638; }
      .ncf-test-table td { vertical-align: top; }
      .ncf-test-table .ncf-test-detail { color: #d63638; }
    </style>
    <h2>結果</h2>
    <p class="ncf-test-summary">
      <?php if ( 0 === $failed ) : ?>
        <span class="ncf-test-ok">全 <?php echo esc_html( $total ); ?> 件成功 ✔</span>
      <?php else : ?>
        <span class="ncf-test-ng"><?php echo esc_html( $failed ); ?> 件失敗</span> / 全 <?php echo esc_html( $total ); ?> 件（成功 <?php echo esc_html( $passed ); ?> 件）
      <?php endif; ?>
    </p>
    <table class="widefat striped ncf-test-table">
      <thead>
        <tr>
          <th style="width:70px;">結果</th>
          <th style="width:220px;">グループ</th>
          <th>テスト</th>
          <th>詳細</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ( $results as $r ) : ?>
          <tr>
            <td>
              <?php if ( $r['pass'] ) : ?>
                <span class="ncf-test-badge ncf-test-badge-pass">PASS</span>
              <?php else : ?>
                <span class="ncf-test-badge ncf-test-badge-fail">FAIL</span>
              <?php endif; ?>
            </td>
            <td><?php echo esc_html( $r['group'] ); ?></td>
            <td><?php echo esc_html( $r['label'] ); ?></td>
            <td class="ncf-test-detail"><?php echo esc_html( $r['detail'] ); ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <?php
  }
}

new Admin_Page();
