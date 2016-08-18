module.exports = function(grunt) {
	
	grunt.initConfig({
		compass: {
			dist: {
				options: {
					sassDir: 'scss',
					cssDir: 'css',
					environment: 'production',
					outputStyle: 'compressed'
				}
			}
		},
		
		requirejs: {
			compile: {
				options: {
					baseUrl: 'js',
					dir: 'build',
					keepBuildDir: true,
					mainConfigFile: 'js/config.js',
					fileExclusionRegExp: /^\.|css|scss|fonts|img|templates|build|\.(jpg|jpeg|gif|css|pdf|png|php|html|ico|md|json)+$|readme|r.js|app.build.js|config.js|\.svn|docs|examples|test/,
					optimizeCss: 'none',
	
					modules: [{"name":"pagetypes\/blogentry"},{"name":"pagetypes\/blogholder"},{"name":"pagetypes\/default"},{"name":"pagetypes\/newsarticle"},{"name":"pagetypes\/newsholder"},{"name":"pagetypes\/page"},{"name":"pagetypes\/userdefinedform"}] // add modules here. e.g. : {name: 'pagetypes/pagename'}
				}
			}
		},
		
		/*
imageoptim: {
			files: ['img'],
			options: {
				jpegMini: false,
				imageAlpha: true,
				quitAfter: false
			}
		},
*/
		
		watch: {
			css: {
				files: 'scss/*.scss',
				tasks: ['compass:dist'],
				options: {
					event: ['all'],
					livereload: true,
				},
			},
			sprites: {
				files: 'img/sprites/*.png',
				tasks: ['compass:dist'],
				options: {
					event: ['all'],
					livereload: true,
				}
			},
			js: {
				files: ['js/pagetypes/*.js'],
				task: ['jshint']
			}
		}
	});
	
	grunt.loadNpmTasks('grunt-contrib-compass');
	grunt.loadNpmTasks('grunt-contrib-requirejs');
	grunt.loadNpmTasks('grunt-contrib-watch');
/* 	grunt.loadNpmTasks('grunt-imageoptim'); */
	grunt.loadNpmTasks('grunt-contrib-jshint');
	
	grunt.registerTask('default', ['compass:dist']);
	grunt.registerTask('production', ['compass:dist', 'requirejs:compile']);//, 'imageoptim']);
}