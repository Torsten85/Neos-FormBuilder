module.exports = function (grunt) {

  grunt.initConfig({
    sass: {
      dist: {
        options: {
          style: 'expanded',
          sourcemap: 'none'
        },
        files: {
          '../Resources/Public/Styles/style.css': '../Resources/Private/Styles/style.scss'
        }
      }
    },

    babel: {
      options: {
        presets: ['../../../Scripts/node_modules/babel-preset-es2015'],
        minified: true,
        compact: true,
        comments: false
      },
      dist: {
        files: {
          '../Resources/Private/JavaScript/recaptcha.build.js': '../Resources/Private/JavaScript/recaptcha.js'
        }
      }
    }
  });

  grunt.loadNpmTasks('grunt-contrib-sass');
  grunt.loadNpmTasks('grunt-babel');

  grunt.registerTask('default', ['sass', 'babel']);
};