const path = require( 'path' );
const { CleanWebpackPlugin } = require( 'clean-webpack-plugin' );
const MiniCssExtractPlugin = require( 'mini-css-extract-plugin' );
const CopyWebpackPlugin = require( 'copy-webpack-plugin' );
const CssMinimizerPlugin = require( 'css-minimizer-webpack-plugin' );
const TerserPlugin = require( 'terser-webpack-plugin' );

module.exports = (env, argv) => {
	const isProd = argv.mode === 'production';

	return {
		entry: {
			admin: './assets/js/admin.js',
			frontend: './assets/js/frontend.js'
		},
		output: {
			filename: 'assets/js/[name].min.js',
			path: path.resolve( __dirname, 'dist' ),
			clean: false,
		},
		module: {
			rules: [
			{
				test: /\.js$/,
				exclude: /node_modules/,
				use: {
					loader: 'babel-loader',
					options: {
						presets: ['@babel/preset-env']
					}
				}
			},
			{
				test: /\.s[ac]ss$/i,
				use: [MiniCssExtractPlugin.loader, 'css-loader', 'postcss-loader', 'sass-loader']
			}
			]
		},
		plugins: [
		new CleanWebpackPlugin(),
		new MiniCssExtractPlugin( { filename: 'assets/css/[name].min.css' } ),
		new CopyWebpackPlugin(
			{
				patterns: [
				{ from: './languages', to: 'languages' },
				{ from: './templates', to: 'templates' },
				{ from: './includes', to: 'includes' },
				{ from: './shahi-legalflowsuite.php', to: 'shahi-legalflowsuite.php' },
				{ from: './readme.txt', to: 'readme.txt' }
				]
			}
		)
	],
	optimization: {
		minimize: isProd,
		minimizer: [new TerserPlugin(), new CssMinimizerPlugin()]
		},
		resolve: {
			extensions: ['.js', '.jsx', '.json']
		}
	};
};
