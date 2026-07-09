const SRC_DIR = './src',
      DIST_DIR = './dist',
      DIST_UNCOMPRESSED_DIR = './dist_uncompressed';

const BROWSER_SYNC_CONFIG = {
  proxy: 'http://localhost:8081',
  host: 'localhost',
};

const IMAGE_OPTIMIZATION_CONFIG = {
  IMG_TO_WEBP_SRC_DIR: './img2webp',
  WEBP_QUALITY: 90,
};

// --- 以下、webpackの動作設定 ---
const path = require('path'),
      glob = require('glob'),
      RemoveEmptyScriptsPlugin = require('webpack-remove-empty-scripts'),
      BrowserSyncPlugin = require('browser-sync-webpack-plugin'),
      CopyPlugin = require('copy-webpack-plugin'),
      TerserPlugin = require('terser-webpack-plugin'),
      HtmlWebpackPlugin = require('html-webpack-plugin'),
      htmlMinifier = require('html-minifier-terser'),
      MiniCssExtractPlugin = require('mini-css-extract-plugin'),
      sharp = require('sharp'),
      postcss = require('postcss'),
      cssnano = require('cssnano'),
      StylelintPlugin = require('stylelint-webpack-plugin'),
      ESLintPlugin = require('eslint-webpack-plugin'),
      SRC_PATH = path.resolve(__dirname, SRC_DIR),
      DIST_PATH = path.resolve(__dirname, DIST_DIR),
      DIST_UNCOMPRESSED_PATH = path.resolve(__dirname, DIST_UNCOMPRESSED_DIR),
      THEME_SRC_PATH = path.resolve(SRC_PATH, 'theme'),
      PLUGINS_SRC_PATH = path.resolve(SRC_PATH, 'plugins'),
      // globのバージョンにより末尾スラッシュの有無が変わるため、あれば取り除く
      PLUGIN_DIRS = glob.sync('*/', { cwd: PLUGINS_SRC_PATH }).map(dir => dir.replace(/[\\/]+$/, ''));

/**
 * ① 非圧縮版ビルド (出力先: dist_uncompressed)
 */
const createConfig_development = ({ outputPath }) => {

  const config = {
    name: 'development',
    mode: 'development',
    devtool: false,
    entry: {},
    output: {
      path: outputPath,
      filename: '[name].js',
      assetModuleFilename: 'assets/[name][ext][query]',
      // mini-css-extract-plugin が出力CSSの各モジュール先頭に付けるパス情報コメントを抑制する
      pathinfo: false,
    },
    module: {
      rules: [
        {
          test: /\.(jpg|jpeg|png|webp|svg|gif|eot|ttf|woff)$/i,
          type: 'asset/inline',
          exclude: [
            /node_modules/,
            path.resolve(__dirname, IMAGE_OPTIMIZATION_CONFIG.IMG_TO_WEBP_SRC_DIR),
          ],
        },
      ]
    },
    plugins: [
      new RemoveEmptyScriptsPlugin(),
    ],
    watch: true,
    target: ['web'],
  };

  // CSS (SCSS -> CSS) for theme
  glob.sync('**/*.scss', { cwd: THEME_SRC_PATH, ignore: '**/_*.scss' }).forEach(key => {
    config.entry[path.join('theme', key.replace('.scss', '.css'))] = path.resolve(THEME_SRC_PATH, key);
  });

  // CSS (SCSS -> CSS) for plugins
  PLUGIN_DIRS.forEach(pluginDir => {
    const pluginSrcPath = path.resolve(PLUGINS_SRC_PATH, pluginDir);
    glob.sync('**/*.scss', { cwd: pluginSrcPath, ignore: '**/_*.scss' }).forEach(key => {
        config.entry[path.join('plugins', pluginDir, key.replace('.scss', '.css'))] = path.resolve(pluginSrcPath, key);
    });
  });

  config.module.rules.push({
    test: /\.scss$/,
    use: [
      MiniCssExtractPlugin.loader,
      {
        loader: 'css-loader',
        options: {
          importLoaders: 3,
          url: {
            filter: (url, resourcePath) => {
              if (/(--pc|--sp|--exc)\.(jpg|jpeg|png|webp|svg|gif)(\?\d+)?$/i.test(url)) {
                return false;
              }
              if (url.startsWith('/')) {
                return false;
              }
              return true;
            },
          },
        }
      },
      'postcss-loader',
      {
        loader: 'resolve-url-loader',
        options: {
          sourceMap: true
        }
      },
      {
        loader: 'sass-loader',
        options: {
          sourceMap: true,
          implementation: require('sass'),
          sassOptions: {
            outputStyle: 'expanded'
          }
        }
      }
    ],
  });
  config.plugins.push(
    new MiniCssExtractPlugin({
      filename: '[name]'
    }),
    new StylelintPlugin({
      files: [`${SRC_DIR}/**/*.scss`],
      fix: true
    })
  );

  return config;
}

/**
 * ② 圧縮版ビルド (出力先: dist)
 */
const createConfig_production = ({ outputPath }) => {

  const config = {
    name: 'production',
    dependencies: ['development'],
    mode: 'production',
    entry: {},
    output: {
      path: outputPath,
      filename: '[name].js',
      assetModuleFilename: 'assets/[name][ext][query]',
    },
    module: {
      rules: []
    },
    plugins: [
      new RemoveEmptyScriptsPlugin(),
    ],
    optimization: {
      minimize: true,
      minimizer: [ new TerserPlugin({ extractComments: false }) ]
    },
    watch: true,
    watchOptions: {
      ignored: [
        '**/node_modules/**',
        '**/.DS_Store',
        '**/Thumbs.db',
       path.resolve(__dirname, IMAGE_OPTIMIZATION_CONFIG.IMG_TO_WEBP_SRC_DIR, '**/*.webp').split(path.sep).join('/'),
      ],
    },
    target: ['web'],
    resolve: { extensions: ['.js'] },
  };

  // JS for theme
  glob.sync('**/*.js', { cwd: THEME_SRC_PATH, ignore: '**/_*.js' }).forEach(key => {
    config.entry[path.join('theme', key.replace('.js', ''))] = path.resolve(THEME_SRC_PATH, key);
  });

  // JS for plugins
  PLUGIN_DIRS.forEach(pluginDir => {
    const pluginSrcPath = path.resolve(PLUGINS_SRC_PATH, pluginDir);
    glob.sync('**/*.js', { cwd: pluginSrcPath, ignore: '**/_*.js' }).forEach(key => {
        config.entry[path.join('plugins', pluginDir, key.replace('.js', ''))] = path.resolve(pluginSrcPath, key);
    });
  });

  config.module.rules.push({
    test: /\.js$/,
    exclude: /node_modules/,
    use: 'babel-loader'
  });
  config.plugins.push(
    new ESLintPlugin({
      extensions: ['js'],
      context: SRC_PATH,
    })
  );

  // EJS -> PHP for theme and plugins
  glob.sync('{theme,plugins}/**/*.ejs', { cwd: SRC_PATH, ignore: '**/_*.ejs' }).forEach(key => {
    config.plugins.push(
      new HtmlWebpackPlugin({
        template: path.resolve(SRC_PATH, key),
        filename: key.replace('.ejs', '.php'),
        inject: false,
        minify: false,
      })
    );
  });

  config.module.rules.push({
    test: /\.ejs$/i,
    use: [
      {
        loader: 'html-loader',
        options: {
          sources: false,
          minimize: false
        }
      },
      {
        loader: 'ejs-plain-loader'
      }
    ]
  });

  config.plugins.push(
    new CopyPlugin({
      patterns: [
        {
          from: path.resolve(__dirname, IMAGE_OPTIMIZATION_CONFIG.IMG_TO_WEBP_SRC_DIR, '**/*.{jpg,jpeg,png}'),
          to(pathData) {
            const sourceDir = path.dirname(pathData.absoluteFilename);
            const sourceName = path.parse(pathData.absoluteFilename).name;
            return path.join(sourceDir, `${sourceName}.webp`);
          },
          async transform(content) {
            return await sharp(content)
              .webp({ quality: IMAGE_OPTIMIZATION_CONFIG.WEBP_QUALITY })
              .toBuffer();
          },
          noErrorOnMissing: true,
        },
        {
          from: path.resolve(PLUGINS_SRC_PATH, '**/{readme.txt,LICENSE,LICENSE.txt}'),
          to(pathData) {
            const relativePath = path.relative(PLUGINS_SRC_PATH, pathData.absoluteFilename);
            return path.join(DIST_PATH, 'plugins', relativePath);
          },
          noErrorOnMissing: true,
        },
        {
          from: DIST_UNCOMPRESSED_PATH,
          to: DIST_PATH,
          globOptions: {
            ignore: ['**/*.js', '**/.DS_Store', '**/Thumbs.db'],
          },
          transform: async (content, absoluteFrom) => {
            if (absoluteFrom.endsWith('.html')) {
              return await htmlMinifier.minify(
                content.toString(), {
                collapseBooleanAttributes: true,
                collapseWhitespace: true,
                removeComments: true,
                removeRedundantAttributes: true,
                removeScriptTypeAttributes: true,
                removeStyleLinkTypeAttributes: true,
                useShortDoctype: true,
                minifyJS: true,
                minifyCSS: true,
                processScripts: ['application/ld+json'],
                includeAutoGeneratedTags: false,
              });
            }
            if (absoluteFrom.endsWith('.css')) {
              return (
                await postcss([cssnano({ preset: ['default', { discardComments: { removeAll: true } }] })]).process(content, { from: undefined })).css;
            }
            return content;
          },
        },
      ]
    }),
    new BrowserSyncPlugin({
      ...BROWSER_SYNC_CONFIG,
      files: [
        DIST_DIR + '/**/*.php',
        DIST_DIR + '/**/*.css',
        DIST_DIR + '/**/*.js'
      ],
      ghostMode: false,
    }, { reload: true })
  );

  return config;
};

module.exports = [
  createConfig_development({
    outputPath: DIST_UNCOMPRESSED_PATH,
  }),
  createConfig_production({
    outputPath: DIST_PATH,
  }),
];
