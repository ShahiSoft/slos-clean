const fs = require('fs-extra');
const path = require('path');

const root = path.resolve(__dirname, '..');
const dist = path.join(root, 'dist');
const pluginDir = path.join(dist, 'shahi-legalflowsuite');

async function copy() {
	// Ensure dist exists and is clean
	await fs.remove(dist);
	await fs.mkdirp(pluginDir);

	// Copy plugin files following WordPress plugin directory guidelines
	// https://developer.wordpress.org/plugins/wordpress-org/detailed-plugin-guidelines/

	// Core plugin files
	const coreFiles = [
		'shahi-legalflowsuite.php',
		'uninstall.php',
		'readme.txt',
		'LICENSE.txt',
		'wpml-config.xml',
	];

	for (const file of coreFiles) {
		const src = path.join(root, file);
		if (await fs.pathExists(src)) {
			await fs.copy(src, path.join(pluginDir, file));
			console.log(`✓ Copied ${file}`);
		}
	}

	// Plugin directories
	const directories = [
		'config',
		'includes',
		'templates',
		'languages',
		'assets/css',
		'assets/images',
		'assets/js',
	];

	for (const dir of directories) {
		const src = path.join(root, dir);
		if (await fs.pathExists(src)) {
			await fs.copy(src, path.join(pluginDir, dir), {
				filter: (src) => {
					// Exclude development files
					return (
						!src.includes('.map') &&
						!src.includes('.scss') &&
						!src.includes('node_modules')
					);
				},
			});
			console.log(`✓ Copied ${dir}/`);
		}
	}

	// Copy vendor dependencies (required for plugin functionality)
	// Exclude dev dependencies
	const vendorSrc = path.join(root, 'vendor');
	if (await fs.pathExists(vendorSrc)) {
		await fs.copy(vendorSrc, path.join(pluginDir, 'vendor'), {
			filter: (src) => {
				const relativePath = path.relative(vendorSrc, src);
				// Exclude PHPUnit, PHPCS, and other dev dependencies
				return (
					!relativePath.includes('phpunit') &&
					!relativePath.includes('squizlabs') &&
					!relativePath.includes('wp-coding-standards') &&
					!relativePath.includes('phpcsstandards') &&
					!relativePath.includes('dealerdirect') &&
					!relativePath.includes('wp-phpunit') &&
					!relativePath.includes('mockery') &&
					!relativePath.includes('sebastian') &&
					!relativePath.includes('phar-io') &&
					!relativePath.includes('theseer') &&
					!relativePath.includes('hamcrest') &&
					!relativePath.includes('myclabs') &&
					!relativePath.includes('nikic') &&
					!relativePath.includes('doctrine/instantiator')
				);
			},
		});
		console.log('✓ Copied vendor/ (production dependencies only)');
	}

	console.log(
		'\n✅ Distribution package prepared in dist/shahi-legalflowsuite/'
	);
	console.log('📦 Ready for packaging');
}

copy().catch((err) => {
	console.error('❌ Error:', err);
	process.exit(1);
});
