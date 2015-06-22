module.exports = function( grunt ) {

	var globalConfig = {
		path       : require( 'path' ),
		images_src : 'images/',
		images     : '../assets/images/',
		scripts_src: 'js/',
		scripts    : '../assets/js/',
		styles_src : 'scss/',
		styles     : '../assets/css/'
	};

	grunt.initConfig( {
		globalConfig: globalConfig,

		// https://github.com/gruntjs/grunt-contrib-compass
		compass     : {
			dist: {
				options: {
					sassDir    : '<%= globalConfig.styles_src %>',
					cssDir     : '<%= globalConfig.styles %>',
					imagesDir  : '<%= globalConfig.images %>',
					outputStyle: 'compressed'
				}
			}
		},

		// https://github.com/gruntjs/grunt-contrib-concat
		concat      : {
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
				src: [
					'<%= globalConfig.scripts_src %>frontend.js',
					'<%= globalConfig.scripts_src %>frontend/*.js'
				],
				dest: '<%= globalConfig.scripts %>frontend.js'
			}
		},

		// https://github.com/gruntjs/grunt-contrib-imagemin
		imagemin    : {
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
		jshint      : {
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
			styles: {
				expand: true,
				cwd   : '<%= globalConfig.styles %>',
				dest  : '<%= globalConfig.styles %>',
				src   : [ '*.css' ]
			}
		},

		// https://github.com/sindresorhus/grunt-shell
		shell       : {
			start: {
				command: [
					'cd ..',
					'git pull',
					'cd resources/',
					'grunt'
				].join( '&&' )
			}
		},

		// https://github.com/gruntjs/grunt-contrib-uglify
		uglify      : {
			scripts: {
				expand: true,
				cwd   : '<%= globalConfig.scripts %>',
				dest  : '<%= globalConfig.scripts %>',
				src   : [
					'*.js',
					'!*.min.js'
				],
				rename: function( destBase, destPath ) {
					// Fix for files with mulitple dots
					destPath = destPath.replace( /(\.[^\/.]*)?$/, '.min.js' );

					return globalConfig.path.join( destBase || '', destPath );
				}
			}
		},

		// https://github.com/gruntjs/grunt-contrib-watch
		watch       : {
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
				tasks: [ 'jshint:scripts', 'concat', 'uglify', 'lineending:scripts' ]
			},
			styles : {
				files: [ '<%= globalConfig.scss_src %>**/*.scss' ],
				tasks: [ 'compass', 'lineending:styles' ]
			}
		}
	} );

	// https://github.com/sindresorhus/load-grunt-tasks
	require( 'load-grunt-tasks' )( grunt );

	grunt.registerTask( 'default', [ 'watch' ] );
	grunt.registerTask( 'grunt', [ 'jshint:grunt' ] );
	grunt.registerTask( 'images', [ 'imagemin' ] );
	grunt.registerTask( 'lineendings', [ 'lineending' ] );
	grunt.registerTask( 'scripts', [ 'jshint:scripts', 'concat', 'uglify', 'lineending:scripts' ] );
	grunt.registerTask( 'start', [ 'shell:workflow' ] );
	grunt.registerTask( 'styles', [ 'compass', 'lineending:styles' ] );
	grunt.registerTask( 'test', [ 'jshint' ] );
};
