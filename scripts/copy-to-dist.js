const fs = require('fs-extra');
const path = require('path');

const root = path.resolve(__dirname, '..');
const dist = path.join(root, 'dist');

async function copy() {
	// Ensure dist exists
	await fs.remove(dist);
	await fs.mkdirp(dist);

	// Copy plugin PHP and includes/templates/languages — respect WP rules (do not copy node_modules)
	const patterns = [
		'shahi-legalflowsuite.php',
		'config',
		'includes',
		'templates',
		'languages',
		'assets/css',
		'assets/images',
	];

	for (const p of patterns) {
		const src = path.join(root, p);
		if (await fs.pathExists(src)) {
			await fs.copy(src, path.join(dist, p));
		}
	}

	console.log('Files copied to dist/');
}

copy().catch((err) => {
	console.error(err);
	process.exit(1);
});
