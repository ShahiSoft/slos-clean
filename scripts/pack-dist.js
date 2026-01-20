const fs = require('fs');
const path = require('path');
const archiver = require('archiver');

const root = path.resolve(__dirname, '..');
const dist = path.join(root, 'dist');
const pluginDir = path.join(dist, 'shahi-legalflowsuite');
const releaseDir = path.join(root, 'release');
const outFile = path.join(releaseDir, 'shahi-legalflowsuite.zip');

if (!fs.existsSync(pluginDir)) {
	console.error(
		'❌ dist/shahi-legalflowsuite/ not found — run npm run build first'
	);
	process.exit(1);
}

fs.mkdirSync(releaseDir, { recursive: true });

// Remove old zip if exists
if (fs.existsSync(outFile)) {
	fs.unlinkSync(outFile);
	console.log('🗑️  Removed old zip file');
}

const output = fs.createWriteStream(outFile);
const archive = archiver('zip', { zlib: { level: 9 } });

output.on('close', function () {
	const sizeInMB = (archive.pointer() / 1024 / 1024).toFixed(2);
	console.log('\n✅ Archive created successfully!');
	console.log(`📦 Size: ${sizeInMB} MB (${archive.pointer()} bytes)`);
	console.log(`📁 Location: ${outFile}`);
	console.log('\n🎉 Ready for WordPress plugin directory submission!');
});

archive.on('warning', function (err) {
	if (err.code === 'ENOENT') {
		console.warn('⚠️  Warning:', err);
	} else {
		throw err;
	}
});

archive.on('error', function (err) {
	console.error('❌ Archive error:', err);
	throw err;
});

console.log('📦 Creating WordPress plugin package...');
archive.pipe(output);

// Archive the plugin directory with proper structure
// This creates: shahi-legalflowsuite.zip/shahi-legalflowsuite/[files]
archive.directory(pluginDir, 'shahi-legalflowsuite');

archive.finalize();
