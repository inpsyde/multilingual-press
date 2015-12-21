/* jshint node:true */
module.exports = function( grunt ) {
	'use strict';

	var _ = require( 'lodash' ),
		autoprefixer = require( 'autoprefixer' );

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
				phpunit: 'tests/phpunit/'
			}
		},

		// Will soon be used for QUnit.
		clean: {},

		concat: {
			options: {
				separator: '\n'
			},
			admin: {
				src: [
					'<%= config.scripts.src %>admin.js',
					'<%= config.scripts.src %>admin/*.js'
				],
				dest: '<%= config.scripts.dest %>admin.js'
			},
			frontend: {
				src: [
					'<%= config.scripts.src %>frontend.js',
					'<%= config.scripts.src %>frontend/*.js'
				],
				dest: '<%= config.scripts.dest %>frontend.js'
			}
		},

		cssmin: {
			options: {
				compatibility: 'ie7'
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
			'imagemin-assets': {
				task: 'imagemin:assets',
				src: [ '<%= config.assets.src %>*.{gif,jpeg,jpg,png}' ]
			},
			'imagemin-images': {
				task: 'imagemin:images',
				src: [ '<%= config.images.src %>**/*.{gif,jpeg,jpg,png}' ]
			},
			sass: {
				src: [ '<%= config.styles.src %>**/*.scss' ]
			}
		},

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

		jscs: {
			options: {
				config: true
			},
			grunt: {
				src: [ 'Gruntfile.js' ]
			},
			src: {
				src: [ '<%= config.scripts.src %>**/*.js' ]
			},
			dest: {
				src: [ '<%= config.scripts.dest %>*.js' ]
			}
		},

		jshint: {
			options: {
				jshintrc: true,
				reporter: require( 'jshint-stylish' )
			},
			grunt: {
				src: [ 'Gruntfile.js' ]
			},
			src: {
				src: [ '<%= config.scripts.src %>**/*.js' ]
			},
			dest: {
				src: [ '<%= config.scripts.dest %>*.js' ]
			}
		},

		jsonlint: {
			configs: {
				src: [ '.{jscs,jshint}rc' ]
			},
			json: {
				src: [ '*.json' ]
			}
		},

		jsvalidate: {
			options: {
				globals: {},
				esprimaOptions: {},
				verbose: false
			},
			grunt: {
				src: [ 'Gruntfile.js' ]
			},
			src: {
				src: [ '<%= config.scripts.src %>**/*.js' ]
			},
			dest: {
				src: [ '<%= config.scripts.dest %>*.js' ]
			}
		},

		lineending: {
			options: {
				eol: 'lf',
				overwrite: true
			},
			grunt: {
				src: [ 'Gruntfile.js' ]
			},
			scripts: {
				src: [ '<%= config.scripts.dest %>*.js' ]
			},
			styles: {
				src: [ '<%= config.styles.dest %>*.css' ]
			}
		},

		phplint: {
			src: {
				src: [ '<%= config.src %>**/*.php' ]
			},
			tests: {
				src: [ '<%= config.tests.phpunit %>**/*.php' ]
			}
		},

		postcss: {
			options: {
				processors: [
					autoprefixer( {
						browsers: [
							'Android >= 2.1',
							'Chrome >= 21',
							'Edge >= 12',
							'Explorer >= 7',
							'Firefox >= 17',
							'iOS >= 3',
							'Opera >= 12.1',
							'Safari >= 6.0'
						],
						cascade: false
					} )
				]
			},
			styles: {
				expand: true,
				cwd: '<%= config.styles.dest %>',
				src: [ '*.css', '!*.min.css' ],
				dest: '<%= config.styles.dest %>'
			}
		},

		sass: {
			options: {
				style: 'expanded',
				noCache: true
			},
			styles: {
				expand: true,
				cwd: '<%= config.styles.src %>',
				src: [ '*.scss' ],
				dest: '<%= config.styles.dest %>',
				ext: '.css'
			}
		},

		uglify: {
			options: {
				ASCIIOnly: true
			},
			scripts: {
				expand: true,
				cwd: '<%= config.scripts.dest %>',
				src: [ '*.js', '!*.min.js' ],
				dest: '<%= config.scripts.dest %>',
				ext: '.min.js'
			}
		},

		watch: {
			options: {
				dot: true,
				spawn: true,
				interval: 2000
			},

			assets: {
				files: [ '<%= config.assets.src %>*.{gif,jpeg,jpg,png}' ],
				tasks: [
					'newer:delegate:imagemin-assets'
				]
			},

			configs: {
				files: [ '.{jscs,jshint}rc' ],
				tasks: [
					'newer:jsonlint:configs'
				]
			},

			grunt: {
				files: [ 'Gruntfile.js' ],
				tasks: [
					'jscs:grunt',
					'jshint:grunt',
					'lineending:grunt',
					'jsvalidate:grunt'
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
					'newer:jsonlint:json'
				]
			},

			php: {
				files: [
					'<%= config.src %>**/*.php',
					'<%= config.tests.phpunit %>**/*.php'
				],
				tasks: [
					'newer:phplint',
					'phpunit'
				]
			},

			scripts: {
				files: [ '<%= config.scripts.src %>**/*.js' ],
				tasks: [
					'newer:jsvalidate:src',
					'newer:jshint:force',
					'newer:jscs:force',
					'newer:concat',
					'newer:lineending:scripts',
					'newer:uglify',
					'newer:jsvalidate:dest'
				]
			},

			styles: {
				files: [ '<%= config.styles.src %>**/*.scss' ],
				tasks: [
					'newer:delegate:sass',
					'newer:postcss',
					'newer:lineending:styles',
					'newer:cssmin'
				]
			},

			travis: {
				files: [ '.travis.yml' ],
				tasks: [
					'travis-lint'
				]
			}
		}
	};

	// Add "force" target to JSCS for development.
	configObject.jscs.force = _.merge(
		{},
		configObject.jscs.src,
		{
			options: {
				force: true
			}
		}
	);

	// Add "force" target to JSHint for development.
	configObject.jshint.force = _.merge(
		{},
		configObject.jshint.src,
		{
			options: {
				devel: true,
				force: true
			}
		}
	);

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

	grunt.registerTask( 'assets', configObject.watch.assets.tasks );

	grunt.registerTask( 'configs', configObject.watch.configs.tasks );

	grunt.registerTask( 'grunt', configObject.watch.grunt.tasks );

	grunt.registerTask( 'images', configObject.watch.images.tasks );

	grunt.registerTask( 'json', configObject.watch.json.tasks );

	grunt.registerTask( 'php', configObject.watch.php.tasks );

	grunt.registerTask( 'scripts', [
		'newer:jsvalidate:src',
		'newer:jshint:src',
		'newer:jscs:src',
		'newer:concat',
		'newer:lineending:scripts',
		'newer:uglify',
		'newer:jsvalidate:dest',
		'newer:jshint:dest',
		'newer:jscs:dest'
	] );

	grunt.registerTask( 'force-scripts', configObject.watch.scripts.tasks );

	grunt.registerTask( 'styles', configObject.watch.styles.tasks );

	grunt.registerTask( 'travis', configObject.watch.travis.tasks );

	grunt.registerTask( 'common', [
		'configs',
		'grunt',
		'json',
		'php',
		'styles',
		'travis'
	] );

	grunt.registerTask( 'develop', [
		'common',
		'force-scripts'
	] );

	grunt.registerTask( 'pre-commit', [
		'newer-clean',
		'common',
		'assets',
		'images',
		'scripts'
	] );

	grunt.registerTask( 'default', 'develop' );
};
