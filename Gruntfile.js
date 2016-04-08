'use strict';

module.exports = function( grunt ) {
	var configObject = {
		config: {
			assets: {
				src: 'resources/assets/',
				dest: 'assets/'
			},

			images: {
				src: 'resources/images/',
				dest: 'src/assets/images/'
			},

			scripts: {
				src: 'resources/js/',
				dest: 'src/assets/js/'
			},

			src: 'src/',

			styles: {
				src: 'resources/scss/',
				dest: 'src/assets/css/'
			},

			tests: {
				php: 'tests/php/',
				js: 'tests/js/'
			}
		},

		/**
		 * @see {@link https://github.com/jmreidy/grunt-browserify grunt-browserify}
		 * @see {@link https://github.com/substack/node-browserify browserify}
		 */
		browserify: {
			options: {
				transform: [
					/**
					 * @see {@link https://github.com/babel/babelify babelify}
					 */
					[ "babelify" ]
				]
			},

			scripts: {
				expand: true,
				cwd: '<%= config.scripts.src %>',
				src: [ '*.js' ],
				dest: '<%= config.scripts.dest %>'
			}
		},

		/**
		 * @see {@link https://github.com/gruntjs/grunt-contrib-cssmin grunt-contrib-cssmin}
		 * @see {@link https://github.com/jakubpawlowicz/clean-css clean-css}
		 */
		cssmin: {
			options: {
				compatibility: 'ie8'
			},

			styles: {
				expand: true,
				cwd: '<%= config.styles.dest %>',
				src: [ '*.css', '!*.min.css' ],
				dest: '<%= config.styles.dest %>',
				ext: '.min.css'
			}
		},

		// Allow grunt-newer to run tasks if files other than the individual src files have changed since the last run.
		delegate: {
			browserify: {
				task: 'browserify',
				src: [ '<%= config.scripts.src %>**/*.js' ]
			},

			'imagemin-assets': {
				task: 'imagemin:assets',
				src: [ '<%= config.assets.src %>*.{gif,jpeg,jpg,png}' ]
			},

			'imagemin-images': {
				task: 'imagemin:images',
				src: [ '<%= config.images.src %>**/*.{gif,jpeg,jpg,png}' ]
			},

			tests: {
				task: 'tests',
				src: [
					'<%= config.scripts.src %>**/*.js',
					'<%= config.tests.js %>**/*.js'
				]
			}
		},

		/**
		 * @see {@link https://github.com/sindresorhus/grunt-eslint grunt-eslint}
		 * @see {@link https://github.com/eslint/eslint ESLint}
		 */
		eslint: {
			grunt: {
				src: [ 'Gruntfile.js' ]
			},

			src: {
				expand: true,
				cwd: '<%= config.scripts.src %>',
				src: [ '**/*.js' ]
			}
		},

		/**
		 * @see {@link https://github.com/jharding/grunt-exec grunt-exec}
		 */
		exec: {
			// Don't run this directly. Run "$ grunt tests" instead.
			tests: {
				cmd: function( file ) {
					/**
					 * @see {@link https://github.com/babel/babel/tree/master/packages/babel-cli babel-cli}
					 * @see {@link https://github.com/substack/faucet faucet}
					 */
					return '"./node_modules/.bin/babel-node" ' + file + ' | "./node_modules/.bin/faucet"';
				}
			}
		},

		/**
		 * @see {@link https://github.com/gruntjs/grunt-contrib-imagemin grunt-contrib-imagemin}
		 * @see {@link https://github.com/imagemin/imagemin imagemin}
		 */
		imagemin: {
			options: {
				optimizationLevel: 7
			},

			assets: {
				expand: true,
				cwd: '<%= config.assets.src %>',
				src: [ '*.{gif,jpeg,jpg,png}' ],
				dest: '<%= config.assets.dest %>'
			},

			images: {
				expand: true,
				cwd: '<%= config.images.src %>',
				src: [ '**/*.{gif,jpeg,jpg,png}' ],
				dest: '<%= config.images.dest %>'
			}
		},

		/**
		 * @see {@link https://github.com/brandonramirez/grunt-jsonlint grunt-jsonlint}
		 * @see {@link https://github.com/zaach/jsonlint JSON Lint}
		 */
		jsonlint: {
			options: {
				format: true,
				indent: 2
			},

			configs: {
				src: [ '.*rc' ]
			},

			json: {
				src: [ '*.json' ]
			}
		},

		/**
		 * @see {@link https://github.com/ariya/grunt-jsvalidate grunt-jsvalidate}
		 * @see {@link https://github.com/jquery/esprima Esprima}
		 */
		jsvalidate: {
			options: {
				verbose: false
			},

			grunt: {
				src: [ 'Gruntfile.js' ]
			},

			dest: {
				src: [ '<%= config.scripts.dest %>*.js' ]
			}
		},

		/**
		 * @see {@link https://github.com/suisho/grunt-lineending grunt-lineending}
		 */
		lineending: {
			options: {
				eol: 'lf',
				overwrite: true
			},

			configs: {
				src: [ '.*rc' ]
			},

			grunt: {
				src: [ 'Gruntfile.js' ]
			},

			json: {
				src: [ '*.json' ]
			},

			scripts: {
				src: [ '<%= config.scripts.dest %>*.js' ]
			},

			styles: {
				src: [ '<%= config.styles.dest %>*.css' ]
			}
		},

		/**
		 * @see {@link https://github.com/jgable/grunt-phplint grunt-phplint}
		 */
		phplint: {
			src: {
				src: [ '<%= config.src %>**/*.php' ]
			},

			tests: {
				src: [ '<%= config.tests.php %>**/*.php' ]
			}
		},

		/**
		 * @see {@link https://github.com/nDmitry/grunt-postcss grunt-postcss}
		 * @see {@link https://github.com/postcss/postcss PostCSS}
		 */
		postcss: {
			options: {
				processors: [
					/**
					 * @see {@link https://github.com/postcss/autoprefixer Autoprefixer}
					 */
					require( 'autoprefixer' )( {
						browsers: '> 1%, last 2 versions, IE 8',
						cascade: false
					} )
				],
				failOnError: true
			},

			styles: {
				expand: true,
				cwd: '<%= config.styles.dest %>',
				src: [ '*.css', '!*.min.css' ],
				dest: '<%= config.styles.dest %>'
			}
		},

		/**
		 * @see {@link https://github.com/gruntjs/grunt-contrib-sass grunt-contrib-sass}
		 */
		sass: {
			options: {
				sourcemap: 'none',
				unixNewlines: true,
				style: 'expanded',
				noCache: true
			},

			check: {
				options: {
					check: true
				},
				src: '<%= config.styles.src %>*.scss'
			},

			convert: {
				expand: true,
				cwd: '<%= config.styles.src %>',
				src: [ '*.scss' ],
				dest: '<%= config.styles.dest %>',
				ext: '.css'
			}
		},

		/**
		 * @see {@link https://github.com/gruntjs/grunt-contrib-uglify grunt-contrib-uglify}
		 * @see {@link https://github.com/mishoo/UglifyJS UglifyJS}
		 */
		uglify: {
			options: {
				ASCIIOnly: true,
				preserveComments: false
			},

			scripts: {
				expand: true,
				cwd: '<%= config.scripts.dest %>',
				src: [ '*.js', '!*.min.js' ],
				dest: '<%= config.scripts.dest %>',
				ext: '.min.js'
			}
		},

		/**
		 * @see {@link https://github.com/gruntjs/grunt-contrib-watch grunt-contrib-watch}
		 */
		watch: {
			options: {
				spawn: false
			},

			assets: {
				files: [ '<%= config.assets.src %>*.{gif,jpeg,jpg,png}' ],
				tasks: [
					'newer:delegate:imagemin-assets'
				]
			},

			configs: {
				files: [ '.*rc' ],
				tasks: [
					'newer:jsonlint:configs',
					'changed:lineending:configs'
				]
			},

			grunt: {
				files: [ 'Gruntfile.js' ],
				tasks: [
					'newer:eslint:grunt',
					'newer:jsvalidate:grunt',
					'newer:lineending:grunt'
				]
			},

			images: {
				files: [ '<%= config.images.src %>**/*.{gif,jpeg,jpg,png}' ],
				tasks: [
					'newer:delegate:imagemin-images'
				]
			},

			json: {
				files: [ '*.json' ],
				tasks: [
					'newer:jsonlint:json',
					'changed:lineending:json'
				]
			},

			php: {
				files: [
					'<%= config.src %>**/*.php',
					'<%= config.tests.php %>**/*.php'
				],
				tasks: [
					'newer:phplint',
					'phpunit'
				]
			},

			scripts: {
				files: [ '<%= config.scripts.src %>**/*.js' ],
				tasks: [
					'newer:eslint:src',
					'newer:delegate:tests',
					'newer:delegate:browserify',
					'changed:jsvalidate:dest',
					'changed:lineending:scripts',
					'changed:uglify'
				]
			},

			styles: {
				files: [ '<%= config.styles.src %>**/*.scss' ],
				tasks: [
					'sass:convert',
					'changed:postcss',
					'changed:lineending:styles',
					'changed:cssmin'
				]
			}
		}
	};

	/**
	 * @see {@link https://github.com/sindresorhus/load-grunt-tasks load-grunt-tasks}
	 */
	require( 'load-grunt-tasks' )( grunt );

	grunt.initConfig( configObject );

	// Delegation task for grunt-newer to check files different from the individual task's files.
	grunt.registerMultiTask( 'delegate', function() {
		var data = this.data,
			task = this.target,
			target = Array.prototype.join.call( arguments, ':' );

		if ( data.task ) {
			task = data.task;
		} else if ( target ) {
			task = task + ':' + target;
		}

		grunt.task.run( task );
	} );

	// PHPUnit task.
	grunt.registerTask( 'phpunit', function() {
		grunt.util.spawn( {
			cmd: 'phpunit',
			opts: {
				stdio: 'inherit'
			}
		}, this.async() );
	} );

	// JavaScript tests (babel-node -> tape) task.
	grunt.registerTask( 'tests', function() {
		var files = grunt.file.expand( grunt.template.process( '<%= config.tests.js %>**/*Test.js' ) ),
			numFiles = files.length;

		for ( var i = 0; i < numFiles; ++i ) {
			grunt.task.run( 'exec:tests:' + files[ i ] );
		}
	} );

	grunt.registerTask( 'assets', configObject.watch.assets.tasks );

	grunt.registerTask( 'configs', configObject.watch.configs.tasks );

	grunt.registerTask( 'grunt', configObject.watch.grunt.tasks );

	grunt.registerTask( 'images', configObject.watch.images.tasks );

	grunt.registerTask( 'json', configObject.watch.json.tasks );

	grunt.registerTask( 'php', configObject.watch.php.tasks );

	grunt.registerTask( 'scripts', configObject.watch.scripts.tasks );

	grunt.registerTask( 'styles', configObject.watch.styles.tasks );

	grunt.registerTask( 'common', [
		'configs',
		'grunt',
		'json',
		'php'
	] );

	grunt.registerTask( 'ci', [
		'changed-clean',
		'newer-clean',
		'common',
		'eslint:src',
		'tests',
		'jsvalidate:dest',
		'sass:check'
	] );

	grunt.registerTask( 'develop', [
		'common',
		'scripts',
		'styles'
	] );

	grunt.registerTask( 'pre-commit', [
		'changed-clean',
		'newer-clean',
		'common',
		'assets',
		'images',
		'scripts',
		'styles'
	] );

	grunt.registerTask( 'default', 'develop' );
};
