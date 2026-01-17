const fs = require( 'fs' );
const path = require( 'path' );
const archiver = require( 'archiver' );

const root = path.resolve( __dirname, '..' );
const dist = path.join( root, 'dist' );
const releaseDir = path.join( root, 'release' );
const outFile = path.join( releaseDir, 'shahi-legalflowsuite.zip' );

if ( ! fs.existsSync( dist )) {
	console.error( 'dist/ not found — run build first' );
	process.exit( 1 );
}

fs.mkdirSync( releaseDir, { recursive: true } );

const output = fs.createWriteStream( outFile );
const archive = archiver( 'zip', { zlib: { level: 9 } } );

output.on(
	'close',
	function () {
		console.log( archive.pointer() + ' total bytes' );
		console.log( 'Archive created at: ' + outFile );
	}
);

archive.on(
	'error',
	function (err) {
		throw err; }
);

archive.pipe( output );
archive.directory( dist + '/', false );
archive.finalize();
