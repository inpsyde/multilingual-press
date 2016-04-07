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

		browserify: {
			options: {
				transform: [
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
			'browserify': {
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
			}
		},

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
		exec: {
			testjs: {
				cmd: function( cwd ) {
					return 'babel-node ' + cwd;
				}
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

		jsonlint: {
			configs: {
				src: [ '.*rc' ]
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
				src: [ '<%= config.tests.php %>**/*.php' ]
			}
		},

		postcss: {
			options: {
				processors: [
					require( 'autoprefixer' )( {
						browsers: '> 1%, last 2 versions, IE 8',
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
		testjs: {
			all     : {
				files: [
					{
						expand: true,
						src   : '<%= config.tests.js %>**/*Test.js'
					}
				]
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
				spawn: false,
				interval: 2000
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
					'newer:jsonlint:configs'
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
					'newer:jsonlint:json'
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
					'newer:delegate:browserify',
					'newer:jsvalidate:dest',
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
			},

			travis: {
				files: [ '.travis.yml' ],
				tasks: [
					'travis-lint'
				]
			}
		}
	};

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

	grunt.registerTask( 'scripts', configObject.watch.scripts.tasks );

	grunt.registerTask( 'styles', configObject.watch.styles.tasks );

	grunt.registerTask( 'travis', configObject.watch.travis.tasks );

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
		'styles',
		'travis'
	] );

	grunt.registerTask( 'default', 'develop' );

	grunt.registerMultiTask( 'testjs', function() {
		for ( var file in this.files ) {
			if ( !this.files.hasOwnProperty( file ) ) {
				continue;
			}
			grunt.task.run( 'exec:testjs:' + this.files[ file ].src );
		}
	} );
};
