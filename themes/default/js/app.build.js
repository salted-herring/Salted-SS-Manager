// !see https://github.com/jrburke/r.js/blob/master/build/example.build.js

({
	baseUrl: './',
	dir: '../build',
	keepBuildDir: true,
	mainConfigFile: 'config.js',
	fileExclusionRegExp: /^\.|css|scss|fonts|img|templates|build|\.(jpg|jpeg|gif|css|pdf|png|php|html|ico|md|json)+$|readme|r.js|app.build.js|.*\.config.js|\.svn|docs|examples|test/,
	optimizeCss: 'none',
/* 	optimize: 'none', */
	
	modules: [
		{
			name: 'pagetypes/default'
		}
	]
})
