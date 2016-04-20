'use strict';

module.exports = function( grunt ) {

	grunt.initConfig( {
		config: {
			assets: {
				src: 'resources/assets/',
				dest: 'assets/'
			},

			images: {
				src: 'resources/images/',
				dest: 'src/assets/images/'
			},

			name: 'MultilingualPress',

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
				js: 'tests/js/',
				php: 'tests/php/'
			}
		},

		/**
		 * @see {@link https://github.com/jmreidy/grunt-browserify grunt-browserify}
		 * @see {@link https://github.com/substack/node-browserify browserify}
		 */
		browserify: {
			babelify: {
				options: {
					// transform: [
					// 	/**
					// 	 * @see {@link https://github.com/babel/babelify babelify}
					// 	 * @see {@link https://github.com/thlorenz/browserify-shim browserify-shim}
					// 	 */
					// 	[ 'babelify']
					// ],
					external: [ 'jquery', 'backbone', 'underscore' ]
				},
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
			styles: {
				options: {
					compatibility: 'ie8'
				},
				expand: true,
				cwd: '<%= config.styles.dest %>',
				src: [ '*.css', '!*.min.css' ],
				dest: '<%= config.styles.dest %>',
				ext: '.min.css'
			}
		},

		/**
		 * @see {@link https://github.com/tfrommen/grunt-delegate grunt-delegate}
		 */
		delegate: {
			babelify: {
				src: [
					'.babelrc',
					'<%= config.scripts.src %>**/*.js'
				],
				task: 'browserify:babelify'
			},

			'imagemin-images': {
				src: [ '<%= config.images.src %>**/*.{gif,jpeg,jpg,png}' ],
				task: 'imagemin:images'
			},

			'sass-convert': {
				src: [ '<%= config.styles.src %>**/*.scss' ],
				task: 'sass:convert'
			}
		},

		/**
		 * @see {@link https://github.com/sindresorhus/grunt-eslint grunt-eslint}
		 * @see {@link https://github.com/eslint/eslint ESLint}
		 */
		eslint: {
			gruntfile: {
				src: [ 'Gruntfile.js' ]
			},

			src: {
				src: [ '<%= config.scripts.src %>**/*.js' ]
			},

			tests: {
				options: {
					rules: {
						"no-native-reassign": 0
					}
				},
				src: [ '<%= config.tests.js %>**/*.js' ]
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

			dest: {
				src: [ '<%= config.scripts.dest %>*.js' ]
			},

			gruntfile: {
				src: [ 'Gruntfile.js' ]
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

			github: {
				src: [ '.github/*' ]
			},

			root: {
				src: [ '*' ]
			},

			scripts: {
				src: [
					'<%= config.scripts.src %>**/*.js',
					'<%= config.scripts.dest %>*.js'
				]
			},

			src: {
				src: [
					'<%= config.src %>*',
					'<%= config.src %>inc/**/*'
				]
			},

			styles: {
				src: [
					'<%= config.styles.src %>**/*.scss',
					'<%= config.styles.dest %>*.css'
				]
			},

			tests: {
				src: [
					'<%= config.tests.js %>**/*.js',
					'<%= config.tests.php %>**/*.php'
				]
			}
		},

		/**
		 * @see {@link https://github.com/jgable/grunt-phplint grunt-phplint}
		 */
		phplint: {
			root: {
				src: [ '*.php' ]
			},

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
			styles: {
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
				unixNewlines: true,
				noCache: true
			},

			check: {
				options: {
					check: true
				},
				src: '<%= config.styles.src %>*.scss'
			},

			convert: {
				options: {
					sourcemap: 'none',
					style: 'expanded'
				},
				expand: true,
				cwd: '<%= config.styles.src %>',
				src: [ '*.scss' ],
				dest: '<%= config.styles.dest %>',
				ext: '.css'
			}
		},

		/**
		 * @see {@link https://github.com/sindresorhus/grunt-shell grunt-shell}
		 */
		shell: {
			phpunit: {
				command: 'phpunit'
			},

			// DO NOT run this directly. Run "$ grunt tape" instead.
			tape: {
				command: function( file ) {
					/**
					 * @see {@link https://github.com/babel/babel/tree/master/packages/babel-cli babel-cli}
					 * @see {@link https://github.com/substack/faucet faucet}
					 */
					return '"./node_modules/.bin/babel-node" ' + file + ' | "./node_modules/.bin/faucet"';
				}
			}
		},

		/**
		 * @see {@link https://github.com/gruntjs/grunt-contrib-uglify grunt-contrib-uglify}
		 * @see {@link https://github.com/mishoo/UglifyJS UglifyJS}
		 */
		uglify: {
			scripts: {
				options: {
					ASCIIOnly: true,
					preserveComments: false
				},
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

			configs: {
				files: [ '.*rc' ],
				tasks: [
					'newer:jsonlint:configs'
				]
			},

			gruntfile: {
				files: [ 'Gruntfile.js' ],
				tasks: [
					'eslint:gruntfile',
					'jsvalidate:gruntfile'
				]
			},

			json: {
				files: [ '*.json' ],
				tasks: [
					'newer:jsonlint:json'
				]
			},

			php: {
				files: [
					'*.php',
					'<%= config.src %>**/*.php',
					'<%= config.tests.php %>**/*.php'
				],
				tasks: [
					'newer:phplint'
				]
			},

			scripts: {
				files: [
					'.eslintrc',
					'<%= config.scripts.src %>**/*.js'
				],
				tasks: [
					'newer:eslint:src',
					'newer:delegate:babelify',
					'changed:jsvalidate:dest',
					'changed:uglify'
				]
			},

			styles: {
				files: [ '<%= config.styles.src %>**/*.scss' ],
				tasks: [
					'newer:delegate:sass-convert',
					'changed:postcss',
					'changed:cssmin'
				]
			}
		},

		/**
		 * @see {@link https://github.com/twolfson/grunt-zip grunt-zip}
		 */
		zip: {
			release: {
				src: '<%= config.src %>**/*',
				dest: '<%= config.name %>.zip',
				router: function( filepath ) {
					// Rename "src/" to "multilingual-press/".
					return 'multilingual-press' + filepath.substr( filepath.indexOf( '/' ) );
				}
			}
		}
	} );

	/**
	 * @see {@link https://github.com/sindresorhus/load-grunt-tasks load-grunt-tasks}
	 */
	require( 'load-grunt-tasks' )( grunt );

	// JavaScript tests (babel-node -> tape) task.
	grunt.registerTask( 'tape', function() {
		grunt.file.expand( grunt.template.process( '<%= config.tests.js %>**/*Test.js' ) ).forEach( function( file ) {
			grunt.task.run( 'shell:tape:' + file );
		} );
	} );

	grunt.registerTask( 'common', [
		'jsonlint',
		'phplint',
		'shell:phpunit',
		'eslint',
		'tape'
	] );

	grunt.registerTask( 'ci', [
		'common',
		'jsvalidate',
		'sass:check'
	] );

	grunt.registerTask( 'develop', [
		'newer:delegate:imagemin-images',
		'newer:jsonlint',
		'newer:phplint:src',
		'newer:eslint',
		'newer:delegate:babelify',
		'newer:jsvalidate',
		'newer:delegate:sass-convert',
		'newer:postcss',
		'newer:lineending',
		'changed:uglify',
		'changed:cssmin'
	] );

	grunt.registerTask( 'pre-commit', [
		'imagemin',
		'common',
		'browserify:babelify',
		'jsvalidate',
		'sass:convert',
		'postcss',
		'lineending',
		'uglify',
		'cssmin'
	] );

	grunt.registerTask( 'release', [
		'pre-commit',
		'zip:release'
	] );

	grunt.registerTask( 'default', 'develop' );

};
