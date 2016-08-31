(function($){
	$.entwine('ss', function($) {
		var socket = null;
		$('#action_test').entwine({
			onmatch: function(e) {
				socket = io('http://deploy.local:10086');
				socket.on('connect', function () {
					//socket.send('hi');
					socket.emit('environment', environment);
					socket.on('message', function (msg) {
					  // my msg
					});
				});
			},
			onclick: function(e) {
				e.preventDefault();
				socket.emit('ssh', 'download', ['enviro-' + environment.id, '/home/saltydev/domains/dev-sh.saltydev.com/robots.txt', '/Users/leo/Sites/SDM/htdocs/assets/salted-herring/dev/robots.txt'], function(data) {
					console.log(data);
				});
				/*socket.on('message', function(data){
					console.log(data);
				});*/
				return false;
			}
		});
	});
}(jQuery));
