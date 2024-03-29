/*

TO DO

1) Reduce CSS duplication
	 - Ideally just a single build - global.scss turns into /build/global.css
	 - Can Autoprefixer output minified?
	 - If it can, is it as good as cssmin?
	 - Could Sass be used again to minify instead?
	 - If it can, is it as good as cssmin?

2) Better JS dependency management
	 - Require js?
	 - Can it be like the Asset Pipeline where you just do //= require "whatever.js"

3) Is HTML minification worth it?

4) Set up a Jasmine test just to try it.

5) Can this Gruntfile.js be abstracted into smaller parts?
	 - https://github.com/cowboy/wesbos/commit/5a2980a7818957cbaeedcd7552af9ce54e05e3fb

	 */

	 module.exports = function(grunt) {

	 	grunt.initConfig({

	 		pkg: grunt.file.readJSON('package.json'),

	 		less: {
	 			dist: {
	 				options: {
	 					paths: ["assets/less/", "/Users/stic/Sites/bootstrap/framework/less/"]
	 				},
	 				files: {
	 					'assets/css/main.css': 'assets/less/main.less'
	 				}
	 			}
	 		},

	 		autoprefixer: {
	 			options: {
	 				browsers: ['last 4 versions']
				// browsers: ['> 0%']
			},
			multiple_files: {
				expand: true,
				flatten: true,
				src: 'assets/css/*.css',
				dest: 'assets/css/'
			}
		},

		csscomb: {
			options: {
				config: 'assets/css/csscomb.json'
			},
			dist: {
				files: {
					'assets/css/main.css': 'assets/css/main.css',
				}
			},
		},

		cssmin: {
			combine: {
				files: {
					'www/css/main.min.css': [
					'assets/css/main.css',
					'assets/css/icheck-default.css',
					'assets/css/icheck-large.css',
					'assets/css/jquery.custombox.css'
					]
				}
			}
		},

		//	Spojení souborů do jednoho
		concat: {
			dist: {
				files: {
					'www/js/common.min.js':[
							// 'libs/js/vendor/jquery-ui-1.10.3.datepicker.js',
							// 'libs/js/vendor/typeahead.js',
							// 'libs/js/vendor/imagesloaded.pkgd.js',
							'libs/js/vendor/tagmanager.js',
							'libs/js/vendor/jquery.ias.js',
							'libs/js/vendor/jquery.idTabs.min.js',
							'libs/js/vendor/masonry.pkgd.min.js',
							'libs/js/vendor/jquery.custombox.js',
							'libs/js/vendor/typeahead.min.js',
							'libs/js/vendor/jquery.icheck.min.js',
							'libs/js/vendor/jquery.customSelect.min.js',
							'libs/js/vendor/moment.min.js',
							'libs/js/vendor/pikaday.js',
							'vendor/tacoberu/nette-form-controls/assets/js/jquery.filePreuploader.js'
							],
					'www/js/nette.min.js': [
							'libs/js/nette/netteForms.js',
							'libs/vojtechDobes/nette.ajax.js/nette.ajax.js',
							'libs/vojtechDobes/history/history.ajax.js'
							],
					'www/js/main.min.js': [
							'assets/js/main.js'
							]
						}
					}
				},

				uglify: {
					build: {
						files: {
							'www/js/common.min.js': [
									'www/js/common.min.js'
									],
							'www/js/main.min.js': [
									'www/js/main.min.js'
									],
							'www/js/nette.min.js': [
									'www/js/nette.min.js'
									]
						}
					}
				},

				imagemin: {
					dynamic: {
						files: [{
							expand: true,
							cwd: 'assets/img/',
							src: ['*.{png,jpg,gif}'],
							dest: 'www/img/'
						}],
						options: {
							cache: false
						}
					}
				},

				php: {
					options: {
						hostname: '0.0.0.0',
						port: 8080,
						keepalive: true,
				// open: true,
				base: 'www/',
				livereload: true,
			},
			watch: {
				options: {
					livereload: true
				}
			}
		},

		watch: {
			options: {
				livereload: true
			},
			scripts: {
				files: ['assets/js/*.js'],
				tasks: ['concat'],
				options: {
					spawn: false,
					livereload: true
				}
			},
			css: {
				files: ['assets/less/*.less'],
				tasks: ['less', 'autoprefixer', 'csscomb', 'cssmin'],
				options: {
					spawn: false,
					livereload: true
				}
			},
			images: {
				files: ['assets/img/*.{png,jpg,gif}'],
				tasks: ['imagemin'],
				options: {
					spawn: false,
					livereload: true
				}
			}
		},

		concurrent: {
			target: {
				tasks: ['php:watch', 'watch'],
				options: {
					logConcurrentOutput: true
				}
			}
		}

	});


	require('load-grunt-tasks')(grunt);

	// Default Task is basically a rebuild
	grunt.registerTask('default', ['concat', 'uglify', 'less', 'autoprefixer', 'csscomb', 'cssmin', 'imagemin']);
	grunt.registerTask('phpwatch', ['concurrent:target']);

};
