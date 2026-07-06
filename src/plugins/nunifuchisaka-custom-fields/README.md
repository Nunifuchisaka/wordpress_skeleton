# Nunifuchisaka Custom Fields

投稿編集画面にカスタムフィールド（メタボックス）を追加する軽量プラグイン。
フィールド定義はプラグイン側に持たず、テーマ側からフィルターフックで登録する設計です。

## 基本的な使い方

テーマの`functions.php`（このスケルトンでは`src/theme/functions/`配下のパーシャル）で、`ncf_register_fields`フィルターにフィールド定義の配列を返します。

```php
add_filter( 'ncf_register_fields', function( $configs ) {
  $configs['my_meta_box'] = [
    'title'  => '記事の追加情報',
    'screen' => 'post', // 対象のpost_type（配列で複数指定も可）
    'fields' => [
      [
        'key'   => 'subtitle',
        'label' => 'サブタイトル',
        'type'  => 'text',
        'desc'  => '記事タイトルの下に表示されます',
      ],
      [
        'key'   => 'main_visual',
        'label' => 'メインビジュアル',
        'type'  => 'image',
      ],
    ],
  ];
  return $configs;
} );
```

これだけで対象の投稿編集画面にメタボックスが表示され、保存・サニタイズまで自動で行われます。

## メタボックスの設定

`$configs`のキーがメタボックスのID、値が設定配列です。

| キー | 既定値 | 説明 |
| --- | --- | --- |
| `title` | `Custom Fields` | メタボックスのタイトル |
| `screen` | `post` | 対象のpost_type。`[ 'post', 'page' ]`のように配列も可 |
| `context` | `advanced` | 表示位置（`normal` / `side` / `advanced`） |
| `priority` | `default` | 表示優先度 |
| `fields` | `[]` | フィールド定義の配列（下記） |

## フィールド定義

`fields`の各要素は以下のキーを持つ配列です。

| キー | 必須 | 説明 |
| --- | --- | --- |
| `key` | ○ | メタキーの元になる識別子。実際のメタキーは`ncf_`プレフィックス付き（例: `key`が`subtitle`なら`ncf_subtitle`） |
| `label` | ○ | 編集画面に表示するラベル |
| `type` | - | フィールド型（既定: `text`）。下記一覧参照 |
| `desc` | - | フィールド下に表示する説明文 |
| `options` | - | `select` / `radio` / `checkbox`用の選択肢（`値 => ラベル`の連想配列） |
| `post_type` | - | `post`型用。選択対象のpost_type（既定: `post`） |
| `posts_per_page` | - | `post`型用。選択肢に出す最大件数（既定: 50） |
| `sub_fields` | - | `repeater`型用。行内のフィールド定義の配列 |
| `sanitize_callback` | - | 保存時のサニタイズを差し替えるコールバック。`function( $value, $field )`で呼ばれる |

## フィールド型一覧

| type | 入力UI | 保存される値 |
| --- | --- | --- |
| `text` | テキスト入力 | 文字列 |
| `textarea` | 複数行テキスト | 文字列（改行保持） |
| `select` | セレクトボックス | 選択した値 |
| `radio` | ラジオボタン | 選択した値 |
| `checkbox` | チェックボックス（複数可） | 選択した値の配列 |
| `image` | メディアライブラリから選択 | 添付ファイルID（int） |
| `post` | 投稿を選択するセレクト | 投稿ID（int） |
| `number` | 数値入力 | 数値 |
| `url` | URL入力 | `esc_url_raw`済みの文字列 |
| `email` | メール入力 | `sanitize_email`済みの文字列 |
| `date` | 日付ピッカー（ブラウザ標準） | `YYYY-MM-DD`形式の文字列（形式外の値は空で保存） |
| `color` | WordPressカラーピッカー | `#rrggbb`形式の文字列 |
| `repeater` | 行の追加・削除・ドラッグ並べ替え | 行（連想配列）の配列 |

## リピーター

`sub_fields`に定義したフィールドのセットを、行として何件でも追加できます。行はハンドル（≡）のドラッグで並べ替え可能。全サブフィールドが空の行は保存時に自動でスキップされます。

```php
[
  'key'        => 'members',
  'label'      => 'メンバー',
  'type'       => 'repeater',
  'sub_fields' => [
    [ 'key' => 'name',  'label' => '名前',   'type' => 'text' ],
    [ 'key' => 'photo', 'label' => '写真',   'type' => 'image' ],
    [ 'key' => 'sns',   'label' => 'SNS',    'type' => 'url' ],
  ],
],
```

※ リピーターの入れ子（repeaterの中にrepeater）には対応していません。

## テンプレートでの値の取得

メタキーは`ncf_` + `key`です。`get_post_meta`で取得します。

```php
// text系
$subtitle = get_post_meta( get_the_ID(), 'ncf_subtitle', true );
echo esc_html( $subtitle );

// image（添付ファイルIDが入っている）
$img_id = get_post_meta( get_the_ID(), 'ncf_main_visual', true );
if ( $img_id ) {
  echo wp_get_attachment_image( $img_id, 'full' );
}

// repeater（行の配列）
$members = get_post_meta( get_the_ID(), 'ncf_members', true );
if ( ! empty( $members ) && is_array( $members ) ) {
  foreach ( $members as $row ) {
    echo esc_html( $row['name'] ?? '' );
  }
}
```

### 出力コードの自動生成

編集画面のメタボックス下部にある「▶ まとめて出力コードを取得」を開くと、そのメタボックスの全フィールドを出力するPHPコードが生成されます。「コードをコピー」でクリップボードにコピーし、テンプレートに貼り付けて調整してください。

リピーターの生成コードは`foreach ( $rows as $i => $row )`の形になっており、`$i`（0始まりの行番号）を奇数/偶数のスタイル分岐やid属性の付与にそのまま使えます。

この欄は開発者向けツールなので、本番環境などクライアントに見せたくない場合は`ncf_show_output_code`フィルターで非表示にできます（下記フック参照）。

## フック

| フック | 種別 | 説明 |
| --- | --- | --- |
| `ncf_register_fields` | filter | フィールド定義を登録する（前述） |
| `ncf_show_output_code` | filter | `false`を返すと「まとめて出力コードを取得」欄を非表示にする。`function( $show, $post )`で呼ばれる |
| `ncf_after_save` | action | NCFの保存処理が完了した直後に発火。`function( $post_id, $saved )`。`$saved`は保存したメタキーと値の連想配列（削除されたキーは`null`）。キャッシュの破棄や外部連携などに |

```php
// 例1: 本番環境では出力コード欄を隠す
if ( wp_get_environment_type() === 'production' ) {
  add_filter( 'ncf_show_output_code', '__return_false' );
}

// 例2: NCFのフィールドが保存されたらキャッシュを消す
add_action( 'ncf_after_save', function( $post_id, $saved ) {
  if ( array_key_exists( 'ncf_demo_repeater', $saved ) ) {
    delete_transient( 'my_repeater_cache_' . $post_id );
  }
}, 10, 2 );
```

## 補足

- 保存時はフィールド型に応じたサニタイズが自動で適用されます。独自の加工が必要な場合のみ`sanitize_callback`を指定してください。
- `select` / `radio` / `checkbox`は`options`に定義された値のみ、`post`は実在する対象post_typeの投稿IDのみ保存されます（定義外の値は空になります）。
- `post`型は軽量化のため選択肢を既定50件に絞っていますが、保存済みの投稿は件数から漏れていても選択肢に含まれるため、再保存で選択が消えることはありません。件数を増やす場合は`posts_per_page`を指定します。
- WYSIWYG（リッチエディタ）型は未対応です。長文はtextareaを使うか、本文側で管理してください。
- このスケルトンでは`src/plugins/nunifuchisaka-custom-fields/`がソースで、webpackビルドで`dist/plugins/nunifuchisaka-custom-fields/`に出力されたものがDocker経由でWordPressにマウントされます。有効化は管理画面の「プラグイン」から行ってください。
