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
          paths: ["less/", "/Users/stic/Sites/bootstrap/framework/less/"]
        },
        files: {
          'css/main.css': 'less/main.less'
        }
      }
    },

    autoprefixer: {
      options: {
        browsers: ['last 2 version']
      },
      multiple_files: {
        expand: true,
        flatten: true,
        src: 'css/*.css',
        dest: 'css/'
      }
    },

    cssmin: {
      combine: {
        files: {
          'css/main.css': ['css/main.css']
        }
      }
    },

    concat: {
      dist: {
        files: {
          'js/build/common.min.js':['js/vendor/jqueryui-1.10.3.js',
                                'js/vendor/tagmanager.js',
                                'js/vendor/typeahead.js',
                                'js/vendor/imagesloaded.pkgd.js',
                                'js/vendor/masonry.js',
                                'js/vendor/jquery.ias.js',
                                'js/jquery.filePreuploader.js'],
          'js/build/nette.min.js': ['js/vendor/netteForms.js',
                                'js/vendor/nette.ajax.js']
        }
      }
    },

    uglify: {
      build: {
        files: {
          'js/build/common.min.js': ['js/build/common.min.js'],
          'js/build/main.min.js': ['js/main.js'],
          'js/build/nette.min.js': ['js/build/nette.min.js']
        }
      }
    },

    imagemin: {
      dynamic: {
        files: [{
          expand: true,
          cwd: 'images/',
          src: ['**/*.{png,jpg,gif}'],
          dest: 'images/'
        }]
      }
    },

    watch: {
      options: {
        livereload: true
      },
      scripts: {
        files: ['js/*.js'],
        tasks: ['concat', 'uglify'],
        options: {
          spawn: false,
        }
      },
      css: {
        files: ['less/*.less'],
        tasks: ['less', 'autoprefixer', 'cssmin'],
        options: {
          spawn: false,
        }
      },
      images: {
        files: ['images/*.{png,jpg,gif}', 'images/*.{png,jpg,gif}'],
        tasks: ['imagemin'],
        options: {
          spawn: false,
        }
      }
    }

  });


  require('load-grunt-tasks')(grunt);

  // Default Task is basically a rebuild
  grunt.registerTask('default', ['concat', 'uglify', 'less', 'autoprefixer', 'cssmin', 'imagemin']);

};