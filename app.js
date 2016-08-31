var _dict = [];
var io = require('socket.io')(13337);
var getClients = function() {
	var clients = [];
	io.sockets.sockets.forEach(function(oSocket) {
		clients.push({id: oSocket.id, info: _dict[oSocket.id]});
	});
	return clients;
};
io.on('connection', function (socket) {
	var device, osString = socket.handshake.headers['user-agent'];
	if (osString.indexOf('iPhone') >= 0) {
		device = 'iPhone';
	}else if (osString.indexOf('Windows') >= 0) {
		device = 'PC';
	}else if (osString.indexOf('iPad') >= 0) {
		device = 'iPad';
	}else{
		
		console.log(osString);
	}
	
	var socketID = socket.id;
	if (_dict[socketID] === undefined) {
		_dict[socketID] = {
			user: null,
			device: device,
			loc: socket.handshake.headers.referer
		};
	}	
	
	socket.on('me',function(who) {
		if (who.uid == 0) {
			socket.disconnect();
			delete _dict[socketID];
		}else{
			//socket.broadcast.emit('device_list',who,socket.handshake.headers);
			console.log(who);
			_dict[socketID].user = who;
			io.emit('list',getClients());
		}
	});
	
	socket.on('order update',function() {
		io.emit('order update');
	});
	
	
	
	//io.emit('annoucement', socket.handshake.headers);

  socket.on('private message', function (from, msg) {
    console.log('I received a private message by ', from, ' saying ', msg);
    socket.broadcast.emit('bc',from,msg);
  });

  socket.on('disconnect', function () {
    //io.emit('user disconnected');
	delete _dict[socketID];
	io.emit('list',getClients());
  });
});

