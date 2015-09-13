module.exports = function( grunt ) {

	var globalConfig = {
		file       : 'multilingual-press.php',
		images     : 'src/assets/images/',
		images_src : 'resources/images/',
		languages  : 'languages/',
		name       : 'MultilingualPress',
		path       : require( 'path' ),
		scripts    : 'src/assets/js/',
		scripts_src: 'resources/js/',
		styles     : 'src/assets/css/',
		styles_src : 'resources/scss/',
		textdomain : 'multilingualpress'
	};

	grunt.initConfig( {
		globalConfig: globalConfig,

		// https://github.com/nDmitry/grunt-autoprefixer
		autoprefixer: {
			options: {
				browsers: [
					'Android >= 2.1',
					'Chrome >= 21',
					'Explorer >= 7',
					'Firefox >= 17',
					'iOS >= 3',
					'Opera >= 12.1',
					'Safari >= 5.0'
				]
			},
			styles : {
				expand: true,
				cwd   : '<%= globalConfig.styles %>',
				dest  : '<%= globalConfig.styles %>',
				src   : [
					'*.css',
					'!*.min.css'
				]
			}
		},

		// https://github.com/gruntjs/grunt-contrib-concat
		concat: {
			options : {
				separator: '\n'
			},
			admin   : {
				src : [
					'<%= globalConfig.scripts_src %>admin.js',
					'<%= globalConfig.scripts_src %>admin/*.js'
				],
				dest: '<%= globalConfig.scripts %>admin.js'
			},
			frontend: {
				src : [
					'<%= globalConfig.scripts_src %>frontend.js',
					'<%= globalConfig.scripts_src %>frontend/*.js'
				],
				dest: '<%= globalConfig.scripts %>frontend.js'
			}
		},

		// https://github.com/gruntjs/grunt-contrib-cssmin
		cssmin: {
			styles: {
				options: {
					processImport: true
				},
				expand : true,
				cwd    : '<%= globalConfig.styles %>',
				dest   : '<%= globalConfig.styles %>',
				ext    : '.min.css',
				src    : [
					'*.css',
					'!*.min.css'
				]
			}
		},

		// https://github.com/markoheijnen/grunt-glotpress
		glotpress_download: {
			languages: {
				options: {
					domainPath: 'src/<%= globalConfig.languages %>',
					url       : 'http://translate.marketpress.com',
					slug      : 'plugins/multilingualpress',
					textdomain: '<%= globalConfig.textdomain %>'
				}
			}
		},

		// https://github.com/gruntjs/grunt-contrib-imagemin
		imagemin: {
			dynamic: {
				options: {
					optimizationLevel: 7
				},
				files  : [
					{
						expand: true,
						cwd   : '<%= globalConfig.images_src %>',
						dest  : '<%= globalConfig.images %>',
						src   : [ '**/*.{gif,jpeg,jpg,png}' ]
					}
				]
			}
		},

		// https://github.com/gruntjs/grunt-contrib-jshint
		jshint: {
			grunt  : {
				src: [ 'Gruntfile.js' ]
			},
			scripts: {
				expand: true,
				cwd   : '<%= globalConfig.scripts_src %>',
				src   : [
					'**/*.js',
					'!**/*.min.js'
				]
			}
		},

		// https://github.com/suisho/grunt-lineending
		lineending: {
			options: {
				eol      : 'lf',
				overwrite: true
			},
			scripts: {
				expand: true,
				cwd   : '<%= globalConfig.scripts %>',
				dest  : '<%= globalConfig.scripts %>',
				src   : [ '*.js' ]
			},
			styles : {
				expand: true,
				cwd   : '<%= globalConfig.styles %>',
				dest  : '<%= globalConfig.styles %>',
				src   : [ '*.css' ]
			}
		},

		// https://github.com/cedaro/grunt-wp-i18n
		makepot: {
			pot: {
				options: {
					cwd        : 'src',
					domainPath : '<%= globalConfig.languages %>',
					mainFile   : '<%= globalConfig.file %>',
					potComments: 'Copyright (C) {{year}} <%= globalConfig.name %>\nThis file is distributed under the same license as the <%= globalConfig.name %> package.',
					potFilename: '<%= globalConfig.textdomain %>.pot',
					potHeaders : {
						poedit                 : true,
						'report-msgid-bugs-to' : 'https://github.com/inpsyde/multilingual-press/issues',
						'x-poedit-keywordslist': true
					}
				}
			}
		},

		// https://github.com/gruntjs/grunt-contrib-sass
		sass: {
			styles: {
				expand : true,
				cwd    : '<%= globalConfig.styles_src %>',
				dest   : '<%= globalConfig.styles %>',
				ext    : '.css',
				options: {
					style      : 'expanded',
					lineNumbers: false,
					noCache    : true
				},
				src    : [ '*.scss' ]
			}
		},

		// https://github.com/gruntjs/grunt-contrib-uglify
		uglify: {
			scripts: {
				expand: true,
				cwd   : '<%= globalConfig.scripts %>',
				dest  : '<%= globalConfig.scripts %>',
				src   : [
					'*.js',
					'!*.min.js'
				],
				rename: function( destBase, destPath ) {
					// Fix for files with multiple dots
					destPath = destPath.replace( /(\.[^\/.]*)?$/, '.min.js' );

					return globalConfig.path.join( destBase || '', destPath );
				}
			}
		},

		// https://github.com/gruntjs/grunt-contrib-watch
		watch: {
			options: {
				dot     : true,
				spawn   : true,
				interval: 2000
			},
			grunt  : {
				files: 'Gruntfile.js',
				tasks: [ 'jshint:grunt' ]
			},
			images : {
				files: '<%= globalConfig.images_src %>**/*.{gif,jpeg,jpg,png}',
				tasks: [ 'imagemin' ]
			},
			scripts: {
				files: '<%= globalConfig.scripts_src %>**/*.js',
				tasks: [ 'jshint:scripts', 'concat', 'lineending:scripts', 'uglify' ]
			},
			styles : {
				files: [ '<%= globalConfig.scss_src %>**/*.scss' ],
				tasks: [ 'sass', 'autoprefixer', 'lineending:styles', 'cssmin' ]
			}
		}
	} );

	// https://github.com/sindresorhus/load-grunt-tasks
	require( 'load-grunt-tasks' )( grunt );

	grunt.registerTask( 'default', [ 'watch' ] );
	grunt.registerTask( 'grunt', [ 'jshint:grunt' ] );
	grunt.registerTask( 'images', [ 'imagemin' ] );
	grunt.registerTask( 'languages', [ 'makepot', 'glotpress_download' ] );
	grunt.registerTask( 'lineendings', [ 'lineending' ] );
	grunt.registerTask( 'production', [ 'images', 'languages', 'scripts', 'styles' ] );
	grunt.registerTask( 'scripts', [ 'jshint:scripts', 'concat', 'lineending:scripts', 'uglify' ] );
	grunt.registerTask( 'styles', [ 'sass', 'autoprefixer', 'lineending:styles', 'cssmin' ] );
	grunt.registerTask( 'test', [ 'jshint' ] );
};
