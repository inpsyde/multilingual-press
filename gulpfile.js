const gulp = require( 'gulp' );

const autoprefixer = require( 'autoprefixer' );
const babel = require( 'gulp-babel' );
const childProcess = require( 'child_process' );
const cssnano = require( 'cssnano' );
const eslint = require( 'gulp-eslint' );
const exec = require( 'gulp-exec' );
const imagemin = require( 'gulp-imagemin' );
const jsonlint = require( 'gulp-jsonlint' );
const newer = require( 'gulp-newer' );
const phplint = require( 'phplint' ).lint;
const postcss = require( 'gulp-postcss' );
const rename = require( 'gulp-rename' );
const sass = require( 'gulp-sass' );
const uglify = require( 'gulp-uglify' );
const zip = require( 'gulp-zip' );

const config = {
	assets: {
		src: 'resources/assets/',
		dest: 'svn-assets/'
	},

	images: {
		src: 'resources/images/',
		dest: 'assets/images/'
	},

	name: 'MultilingualPress',

	scripts: {
		src: 'resources/js/',
		dest: 'assets/js/'
	},

	src: 'src/',

	styles: {
		src: 'resources/scss/',
		dest: 'assets/css/'
	},

	tests: {
		js: 'tests/js/',
		php: 'tests/php/'
	}
};

gulp.task( 'assets', () => {
	const dest = config.assets.dest;

	return gulp.src( `${config.assets.src}*.{gif,jpeg,jpg,png}` )
		.pipe( newer( dest ) )
		.pipe( imagemin( {
			optimizationLevel: 7
		} ) )
		.pipe( gulp.dest( dest ) );
} );

gulp.task( 'lint-configs', () => {
	return gulp.src( [
			'*.json',
			'.*rc',
		] )
		.pipe( newer( {
			dest: '*.json',
			extra: '.*rc'
		} ) )
		.pipe( jsonlint() )
		.pipe( jsonlint.reporter() );
} );

gulp.task( 'lint-javascript-tests', [
	'lint-configs',
], () => {
	const src = `${config.tests.js}**/*.js`;

	return gulp.src( src )
		.pipe( newer( src ) )
		.pipe( eslint( {
			rules: {
				'no-native-reassign': 0
			}
		} ) )
		.pipe( eslint.format() );
} );

gulp.task( 'lint-php', ( cb ) => {
	const src = [
		'*.php',
		`${config.src}**/*.php`,
		`${config.tests.php}**/*.php`,
	];

	phplint( src, { limit: 10 }, ( err ) => {
		cb( err );
		err && process.exit( 1 );
	} );
} );

gulp.task( 'lint-scripts', [
	'lint-configs',
], () => {
	const src = `${config.scripts.src}*.js`;

	return gulp.src( src )
		.pipe( newer( src ) )
		.pipe( eslint() )
		.pipe( eslint.format() );
} );

gulp.task( 'images', () => {
	const dest = config.images.dest;

	return gulp.src( `${config.images.src}**/*.{gif,jpeg,jpg,png}` )
		.pipe( newer( dest ) )
		.pipe( imagemin( {
			optimizationLevel: 7
		} ) )
		.pipe( gulp.dest( dest ) );
} );

gulp.task( 'phpunit', [
	'lint-php',
], ( cb ) => {
	childProcess.exec( '"./vendor/bin/phpunit"', ( err, stdout, sterr ) => {
		stdout && console.log( stdout );
		sterr && console.log( sterr );
		cb( err );
	} );
} );

gulp.task( 'scripts', [
	'lint-configs',
	'lint-scripts',
], () => {
	const dest = config.scripts.dest;

	return gulp.src( `${config.scripts.src}*.js` )
		.pipe( newer( dest ) )
		.pipe( babel() )
		.pipe( gulp.dest( dest ) )
		.pipe( rename( {
			suffix: '.min'
		} ) )
		.pipe( uglify( {
			output: {
				ascii_only: true
			}
		} ) )
		.pipe( gulp.dest( dest ) );
} );

gulp.task( 'styles', () => {
	const dest = config.styles.dest;

	return gulp.src( `${config.styles.src}**/*.scss` )
		.pipe( newer( {
			dest,
			ext: '.css'
		} ) )
		.pipe( sass( {
			indentType: 'tab',
			indentWidth: 1,
			outputStyle: 'expanded'
		} ).on( 'error', sass.logError ) )
		.pipe( postcss( [
			autoprefixer( {
				cascade: false
			} ),
		] ) )
		.pipe( gulp.dest( dest ) )
		.pipe( rename( {
			suffix: '.min'
		} ) )
		.pipe( postcss( [
			cssnano(),
		] ) )
		.pipe( gulp.dest( dest ) );
} );

// TODO: Make tape great again! I don't quite like this as it is right now... :(
gulp.task( 'tape', [
	'lint-configs',
	'lint-javascript-tests',
], () => {
	return gulp.src( `${config.tests.js}**/*Test.js`, {
			read: false
		} )
		.pipe( exec( '"./node_modules/.bin/babel-node" --plugins rewire <%= file.path %>' ) )
		.pipe( exec.reporter() );
} );

gulp.task( 'zip', [
	'pre-commit',
], () => {
	return gulp.src( [
			'*.{php,txt}',
			'assets/**',
			`${config.src}**/*.php`,
		], {
			base: '.'
		} )
		.pipe( rename( ( path ) => {
			path.dirname = `multilingual-press/${path.dirname}`
		} ) )
		.pipe( zip( `${config.name}.zip` ) )
		.pipe( gulp.dest( '.' ) );
} );

gulp.task( 'ci', [
	'lint-scripts',
	'phpunit',
	'tape',
] );

gulp.task( 'develop', [
	'lint-javascript-tests',
	'lint-php',
	'images',
	'scripts',
	'styles',
] );

gulp.task( 'pre-commit', [
	'ci',
	'assets',
	'images',
	'scripts',
	'styles',
] );

gulp.task( 'release', [
	'zip',
] );

gulp.task( 'default', [ 'develop' ] );
