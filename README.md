# WordPressスケルトン

WordPressのテーマ・プラグインをモダンなビルドパイプライン（webpack + SCSS + EJS + ES6）で開発するための雛形。開発環境はDockerで動かします。

## 必要なもの

- Node.js / npm
- Docker（Docker Compose）

## セットアップと開発

```sh
npm install        # 初回のみ
npm start          # webpackをwatchモードで起動（開発中は起動しっぱなしにする）
docker compose up  # WordPress + MariaDBを起動
```

`http://localhost:8081` でWordPressが動きます。初回はWordPressのインストール画面が表示されるので、セットアップ後に管理画面の「外観 > テーマ」でテーマ（hoge）を、「プラグイン」で必要なプラグインを有効化してください。

`src/` を編集するとwebpackが `dist/` にビルドし、Dockerのバインドマウント経由でWordPressに反映されます。BrowserSyncによるライブリロード付きです。

## データベース（記事）の管理

記事や設定はDBに保存されるためgit管理外ですが、ダンプをリポジトリにコミットして共有できます。

```sh
npm run db:export  # DBを db/wordpress_db.sql に書き出す（コミット用）
npm run db:import  # db/wordpress_db.sql からDBを復元する（全テーブルを作り直す）
```

`db/wordpress_db.sql` にはレイアウト確認用のダミー記事（NCFの全フィールド入力済み）入りのダンプをコミットしてあります。ダミー記事が参照する画像ファイルも `htdocs/wp-content/uploads/` 配下にコミット済みです。記事を追加・変更してリポジトリに残したいときは `npm run db:export` してからコミットしてください（画像を追加した場合は `.gitignore` のuploads除外も確認）。

## ディレクトリ構成

| パス | 内容 |
| --- | --- |
| `src/theme/` | テーマのソース（EJS → PHP、SCSS → CSS、ES6 → JS） |
| `src/plugins/` | プラグインのソース。配下のディレクトリはwebpackが自動検出する |
| `dist/` | ビルド成果物。`dist/theme` と `dist/plugins/*` がWordPressにマウントされる |
| `dist_uncompressed/` | 非圧縮の中間ビルド（手動編集しない） |
| `htdocs/` | WordPress本体（初回起動時に自動ダウンロード、git管理外） |
| `img2webp/` | ここに置いた画像はビルド時にWebPへ変換される |

`_` で始まるファイル（`.ejs` / `.scss`）はパーシャルで、単体ではビルド出力になりません。

## 同梱プラグイン

- **[Nunifuchisaka Custom Fields](src/plugins/nunifuchisaka-custom-fields/README.md)（NCF）** — 軽量カスタムフィールドプラグイン。フィールド定義はテーマ側（`src/theme/functions/_ncf.ejs`）から`ncf_register_fields`フィルターで登録する
- **[NCF Test](src/plugins/ncf-test/README.md)** — NCFを実WordPress環境上で検証するテストランナー。管理画面の「ツール > NCF Test」からワンクリックで実行できる
- **hoge** — 最小構成のサンプルプラグイン

新しいプラグインを追加するときは、`src/plugins/` にディレクトリを作り、`docker-compose.yml` にマウント行を1行追加します。

## 補足

- Lint（Stylelint / ESLint）はビルド中に自動実行されます。単体のnpmスクリプトはありません
- 改行コードは`.gitattributes`でLFに統一しています
- 開発者向けの詳細（webpack設定の構造、Docker配線の注意点など）は [CLAUDE.md](CLAUDE.md) を参照
