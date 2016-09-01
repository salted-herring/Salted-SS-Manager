(function($){
	$.entwine('ss', function($) {
		var socket = null;
		$('#action_test').entwine({
			onmatch: function(e) {
				socket = io('//'+location.hostname+':10086', {reconnection: false});
				socket.on('connect', function () {
					//socket.send('hi');
					socket.emit('environment', environment);
					socket.on('message', function (msg) {
						console.log(msg);
					});
				}).on('disconnect', function(data) {
					alert('connection lost');
				});
			},
			onclick: function(e) {
				e.preventDefault();
				/*socket.emit('download_backup', {
					environment_id: 'enviro-' + environment.id, 
					src: '/home/saltydev/domains/dev-sh.saltydev.com/robots.txt', 
					dest: '/Users/leo/Sites/SDM/htdocs/assets/salted-herring/dev/robots.txt'
				});*/

				socket.emit('ssh', {
					environment_id: 'enviro-' + environment.id, 
					cmd: 'backup'
				});
				/*socket.on('message', function(data){
					console.log(data);
				});*/
				return false;
			}
		});
	});
}(jQuery));
/*
socket = io('http://io.digital.base2.co.nz:13337',{reconnection:false});
socket.emit('me',refined_data);
if ($('body').hasClass('page-node-83')) {
        socket.on('list',function(data) {
                //trace(data);
                $('#lst-device').html('');
                data.forEach(function(deviceData) {
                        $('#lst-device').append(deviceItem(deviceData));
                });
        });
}
socket.on('order update',function() {
        if ($('body').hasClass('page-node-82')) {
                frontOrderUpdate();
        }

        if ($('body').hasClass('page-node-84')) {
                kitchenOrderUpdate();
        }
});
*/