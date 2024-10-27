const DIST_DIR = './htdocs/wp-content/themes/my_theme',
			SRC_DIR = './src',
			path = require('path'),
			glob = require('glob'),
			DIST_PATH = path.resolve(__dirname, DIST_DIR),
			SRC_PATH = path.resolve(__dirname, SRC_DIR),
			RemoveEmptyScriptsPlugin = require('webpack-remove-empty-scripts'),
			MiniCssExtractPlugin = require('mini-css-extract-plugin'),
			TerserPlugin = require('terser-webpack-plugin'),
			config = {
				entry: {},
				plugins: [],
			};

module.exports = (env, argv) => {
	
	const minify = 'production' === argv.mode;
	
	//JS
	glob.sync('**/*.js', {
		cwd: SRC_DIR,
		ignore: '**/_*.js'
	}).forEach(key => {
		config.entry[key.replace('.js', '')] = path.resolve(SRC_DIR, key);
	});
	
	//SCSS
	glob.sync('**/*.scss', {
		cwd: SRC_DIR,
		ignore: '**/_*.scss',
	}).forEach(key => {
		const cssKey = key.replace('.scss', '.css');
		config.entry[cssKey] = path.resolve(SRC_DIR, key);
	});
	
	//pluginsを統合
	config.plugins.push(
		new MiniCssExtractPlugin({
			filename: '[name]',
		}),
		new RemoveEmptyScriptsPlugin(),
	);
	
	//configを統合
	return Object.assign(config, {
		output: {
			path: DIST_PATH,
			filename: '[name].js',
			assetModuleFilename: 'assets/[name][ext][query]',
		},
		optimization: {
			minimize: minify,
			minimizer: [
				new TerserPlugin({
					extractComments: false,
				})
			],
			splitChunks: {
				name: 'js/vendor',
				chunks: 'initial',
			}
		},
		module: {
			rules: [
				{
					test: /\.ejs$/i,
					use: [
						{
							loader: 'html-loader',
							options: {
								sources: {
									urlFilter: (attribute, value, resourcePath) => {
										return false;
									},
								},
								minimize: false,
							},
						},
						'ejs-plain-loader',
					]
				},
				{
					test: /\.scss$/,
					use: [
						MiniCssExtractPlugin.loader,
						{
							loader: 'css-loader',
							options: {
								//url: false,
								importLoaders: 2,
							}
						},
						'postcss-loader',
						{
							loader: 'sass-loader',
							options: {
								implementation: require('sass'),
								sassOptions: {
									includePaths: [
										path.resolve(__dirname, 'node_modules')
									],
									outputStyle: (minify)?'compressed':'expanded',
								}
							}
						}
					]
				}, {
					test: /\.(jpg|png|webp|svg|gif|eot|ttf|woff)$/i,
					type: 'asset',
					parser: {
						dataUrlCondition: {
							maxSize: 50 * 1024,
						},
					},
				}
			]
		},
		watch: true,
		watchOptions: {
			ignored: ['/node_modules', '/gitignore']
		},
		target: ['web'],
		resolve: {
			extensions: ['.ts', '.js']
		},
		stats: {
			errorDetails: true
		}
	});
	
};