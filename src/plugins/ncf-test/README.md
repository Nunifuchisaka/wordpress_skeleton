# NCF Test

[Nunifuchisaka Custom Fields](../nunifuchisaka-custom-fields/README.md)（NCF）の動作を、実際のWordPress環境上で検証する開発用テストランナープラグイン。

PHPUnitなどの外部テスト基盤を使わず、WordPressの管理画面からワンクリックで実行できるのが特徴です。テスト用の投稿（下書き）を一時的に作成し、編集画面からの保存リクエストを再現してNCFの保存・サニタイズ・描画を検証したあと、テスト投稿を自動で削除します。

## 使い方

1. `npm start` と `docker compose up` で開発環境を起動する
2. 管理画面の「プラグイン」で **Nunifuchisaka Custom Fields** と **NCF Test** の両方を有効化する
3. 「ツール > NCF Test」を開き、「テストを実行」ボタンを押す
4. 結果がPASS/FAILの一覧で表示される

## テスト対象

| グループ | 内容 |
| --- | --- |
| メタボックス登録 | `ncf_register_fields`で登録した設定が指定のscreen/contextに登録されること |
| サニタイズ（テキスト系） | text / textarea / number / url / email / date / color の各型で、正常値が保存され不正値が除去・空化されること |
| サニタイズ（選択系） | select / radio / checkbox で`options`定義外の値が保存されないこと |
| サニタイズ（ID系） | image / post で、実在しないIDや対象外post_typeのIDが保存されないこと |
| sanitize_callback | 独自コールバックが値とフィールド定義を受け取って適用されること |
| リピーター | テンプレート行（`{index}`）と空行のスキップ、`sub_fields`未定義キーの除去、サブフィールドのサニタイズ |
| 未送信フィールドの扱い | checkbox / image は削除され、text等は保持されること |
| 保存ガード | nonceなし・不正nonce・対象外post_typeでは保存されないこと |
| ncf_after_save | 保存完了時に1回発火し、`$saved`に保存値（削除キーはnull）が渡されること |
| 描画 | nonceフィールド、name/id/for属性、リピーターのテンプレート行、出力コード欄などのHTML出力 |
| ncf_show_output_code | `false`を返すと出力コード欄が非表示になること |

## テストの追加方法

`src/plugins/ncf-test/ncf-test.ejs` の `Runner` クラスに `test_xxx()` メソッドを追加し、`run()` から呼び出します。

- `$this->make_post()` — テスト用投稿を作成（終了後に自動削除）
- `$this->simulate_save( $post_id, $configs, $post_data )` — 編集画面からの保存を再現してNCFの保存処理を実行
- `$this->render( $post_id, $fields )` — メタボックスの描画HTMLを取得
- `$this->assert_same()` / `assert_true()` / `assert_contains()` / `assert_not_contains()` — アサーション

実行中は`ncf_register_fields`等のフックが退避されるため、テーマ側の登録内容に影響されずテストが定義したフィールドだけで検証されます。

## 補足

- 開発用ツールです。本番環境で有効化する想定はありません。
- NCF本体のnonce名（`ncf_nonce_action`）を`Runner`クラスの定数として持っています。NCF側で変更した場合は保存系テストが失敗するので、定数を合わせて更新してください。
- このスケルトンでは`src/plugins/ncf-test/`がソースで、webpackビルドで`dist/plugins/ncf-test/`に出力されたものがDocker経由でWordPressにマウントされます。
