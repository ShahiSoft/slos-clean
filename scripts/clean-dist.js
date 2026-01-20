const fs   = require( 'fs-extra' );
const path = require( 'path' );

const root    = path.resolve( __dirname, '..' );
const dist    = path.join( root, 'dist' );
const release = path.join( root, 'release' );

async function clean() {
	await fs.remove( dist );
	await fs.remove( release );
	console.log( 'Cleaned dist/ and release/' );
}

clean().catch( err => { console.error( err ); process.exit( 1 ); } );
