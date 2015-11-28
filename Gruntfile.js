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
			dest: 'src/',
			glotpress: {
				url: 'http://translate.marketpress.com',
				slug: 'plugins/multilingualpress'
			},
			images: {
				src: 'resources/images/',
				dest: 'src/assets/images/'
			},
			languages: {
				dest: 'src/languages/',
				dir: 'languages/'
			},
			plugin: {
				file: 'multilingual-press.php',
				issues: 'https://github.com/inpsyde/multilingual-press/issues',
				name: 'MultilingualPress',
				textdomain: 'multilingualpress'
			},
			scripts: {
				src: 'resources/js/',
				dest: 'src/assets/js/'
			},
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

		// Run the sass task if there are new .scss files (even in subfolders).
		delegate: {
			sass: {
				src: [ '<%= config.styles.src %>**/*.scss' ]
			}
		},

		glotpress_download: {
			languages: {
				options: {
					url: '<%= config.glotpress.url %>',
					slug: '<%= config.glotpress.slug %>',
					domainPath: '<%= config.languages.dest %>',
					textdomain: '<%= config.plugin.textdomain %>'
				}
			}
		},

		// TODO: Somehow make "newer:imagemin:*" work...?! Currently, always all images get minified.
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
			scripts: {
				src: [ '<%= config.scripts.src %>**/*.js' ]
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
			scripts: {
				src: [ '<%= config.scripts.src %>**/*.js' ]
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
			src: {
				src: [ '<%= config.scripts.src %>**/*.js' ]
			},
			dest: {
				src: [ '<%= config.scripts.dest %>**/*.js' ]
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
				src: [ '<%= config.scripts.dest %>*.js' ],
				dest: '<%= config.scripts.dest %>'
			},
			styles: {
				src: [ '<%= config.styles.dest %>*.css' ],
				dest: '<%= config.styles.dest %>'
			}
		},

		makepot: {
			pot: {
				options: {
					cwd: '<%= config.dest %>',
					domainPath: '<%= config.languages.dir %>',
					mainFile: '<%= config.plugin.file %>',
					potComments: 'Copyright (C) {{year}} <%= config.plugin.name %>\nThis file is distributed under the same license as the <%= config.plugin.name %> package.',
					potFilename: '<%= config.plugin.textdomain %>.pot',
					potHeaders: {
						poedit: true,
						'report-msgid-bugs-to': '<%= config.plugin.issues %>',
						'x-poedit-keywordslist': true
					},
					processPot: function( pot ) {
						var exclude = [
							'Plugin Name of the plugin/theme',
							'Plugin URI of the plugin/theme',
							'Author of the plugin/theme',
							'Author URI of the plugin/theme'
						];

						var translation;
						for ( translation in pot.translations[ '' ] ) {
							if ( ! pot.translations[ '' ].hasOwnProperty( translation ) ) {
								continue;
							}

							if ( 'undefined' === typeof pot.translations[ '' ][ translation ].comments.extracted ) {
								continue;
							}

							// Skip translations with the above defined meta comments.
							if ( exclude.indexOf( pot.translations[ '' ][ translation ].comments.extracted ) >= 0 ) {
								delete pot.translations[ '' ][ translation ];
							}
						}

						return pot;
					}
				}
			}
		},

		phplint: {
			options: {
				phpArgs: {
					'-lf': null
				}
			},
			file: {
				src: [ '<%= config.plugin.file %>' ]
			},
			src: {
				src: [ '<%= config.dest %>**/*.php' ]
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
			check: {
				options: {
					check: true
				},
				src: [ '<%= config.styles.src %>*.scss' ]
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
					'newer:imagemin:assets'
				]
			},

			configs: {
				files: [ '.{jscs,jshint}rc' ],
				tasks: [
					'jsonlint:configs'
				]
			},

			grunt: {
				files: [ 'Gruntfile.js' ],
				tasks: [
					'jscs:grunt',
					'jshint:grunt',
					'lineending:grunt'
				]
			},

			images: {
				files: [ '<%= config.images.src %>**/*.{gif,jpeg,jpg,png}' ],
				tasks: [
					'newer:imagemin:images'
				]
			},

			json: {
				files: [ '*.json' ],
				tasks: [
					'jsonlint:json'
				]
			},

			php: {
				files: [
					'<%= config.plugin.file %>',
					'<%= config.dest %>**/*.php',
					'<%= config.tests.phpunit %>**/*.php'
				],
				tasks: [
					'phplint'
				]
			},

			scripts: {
				files: [ '<%= config.scripts.src %>**/*.js' ],
				tasks: [
					'jsvalidate:src',
					'jshint:force',
					'jscs:force',
					'newer:concat',
					'newer:lineending:scripts',
					'newer:uglify',
					'jsvalidate:dest'
				]
			},

			styles: {
				files: [ '<%= config.styles.src %>**/*.scss' ],
				tasks: [
					'newer:delegate:sass:styles',
					'newer:postcss',
					'newer:lineending:styles',
					'newer:cssmin'
				]
			}
		}
	};

	// Add development target for JSCS.
	configObject.jscs.force = _.merge(
		{},
		configObject.jscs.scripts,
		{
			options: {
				force: true
			}
		}
	);

	// Add development target for JSHint.
	configObject.jshint.force = _.merge(
		{},
		configObject.jshint.scripts,
		{
			options: {
				devel: true,
				force: true
			}
		}
	);

	require( 'load-grunt-tasks' )( grunt );

	grunt.initConfig( configObject );

	grunt.registerTask( 'assets', configObject.watch.assets.tasks );

	grunt.registerTask( 'configs', configObject.watch.configs.tasks );

	grunt.registerTask( 'grunt', configObject.watch.grunt.tasks );

	grunt.registerTask( 'images', configObject.watch.images.tasks );

	grunt.registerTask( 'json', configObject.watch.json.tasks );

	grunt.registerTask( 'languages', [
		'makepot',
		'glotpress_download'
	] );

	grunt.registerTask( 'php', configObject.watch.php.tasks );

	grunt.registerTask( 'scripts', [
		'jsvalidate:src',
		'jshint:scripts',
		'jscs:scripts',
		'newer:concat',
		'newer:lineending:scripts',
		'newer:uglify',
		'jsvalidate:dest'
	] );

	grunt.registerTask( 'forcescripts', configObject.watch.scripts.tasks );

	grunt.registerTask( 'styles', configObject.watch.styles.tasks );

	grunt.registerTask( 'lint', [
		'jshint',
		'jsonlint',
		'sass:check',
		'phplint'
	] );

	grunt.registerTask( 'precommit', [
		'assets',
		'configs',
		'grunt',
		'images',
		'json',
		'languages',
		'php',
		'scripts',
		'styles'
	] );

	grunt.registerTask( 'default', [
		'configs',
		'grunt',
		'json',
		'php',
		'forcescripts',
		'styles'
	] );

	// Delegation task for grunt-newer to check files different from the individual task's files.
	grunt.registerTask( 'delegate', function() {
		grunt.task.run( this.args.join( ':' ) );
	} );
};
