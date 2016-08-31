/*var sys = require('sys')
var exec = require('child_process').exec;
function puts(error, stdout, stderr) { sys.puts(stdout) }
exec("ls -la", puts);
process.argv.forEach(function (val, index, array) {
  console.log(index + ': ' + val);
});
*/
var _banches			=	[];
var scp 				=	require('scp2');
var banch 				=	function(id) {
	this.id 			=	id;
	this.environments  	=	[];

	return this;
};

var environment 		=	function(environment) {
	this.id				=	environment.id;
	this.name			=	environment.name;
	this.path			=	environment.path;
	this.web_root		=	environment.web_root;
	this.branch			=	environment.branch;
	this.sql_host		=	environment.sql_host;
	this.sql_table		=	environment.sql_table;
	this.sql_user		=	environment.sql_user;
	this.sql_pass		=	environment.sql_pass;
	this.server_addr	=	environment.server_addr;
	this.server_user	=	environment.server_user;
	this.server_pass	=	environment.server_pass;
	this.client			=	new scp.Client({
								host: environment.server_addr,
							    username: environment.server_user,
							    password: environment.server_pass,
							});
	this.clientCallback	=	null;

	var _self			=	this;

	this.client.on('error', function(err) {
		console.log('got event error', err);
	});

	this.client.on('ready', function() {
		trace('been here');
		_self.clientCallback('downloaded');
	});

	this.client.on('transfer', function(buffer, uploaded, total){
		trace(buffer);
		trace(uploaded);
		trace(total);
	});

	this.download		=	function(src, dest, fn) {
		_self.clientCallback = fn;
		_self.client.download(src, dest, function(err) {
			if (err && fn) {
			    fn(err);
			}
		});
	};

	this.scp 			=	function(src, dest) {
		trace(src);
		trace(dest);
		_self.client.scp({
		    host: _self.server_addr,
		    username: _self.server_user,
		    password: _self.server_pass,
		    path: src
		}, dest, function(err) {
			trace(err);
		});

		_self.client.on('connect', function(msg){
			trace(msg);
		});
	};

	

	return this;
};

function trace(el) {
	console.log(el);
}

var client = require('scp2');
var io = require('socket.io')(10086);

io.on('connection', function (socket) {
	//console.log(socket);
	var socketID = socket.id,
		curSocket = null;

	if (_banches[socketID] === undefined) {
		_banches[socketID] = new banch(socketID);
		curSocket		=	_banches[socketID];
	}	

	socket.on('message', function (data) { 
		console.log(data);
	});
	socket.on('environment', function(prEnvironment) {
		if (curSocket.environments['enviro-' + prEnvironment.id] === undefined) {
			curSocket.environments['enviro-' + prEnvironment.id] = new environment(prEnvironment);
		}

		trace(curSocket);
	});
	socket.on('ssh', function(command, params, fn) {
		// trace('params from client:');
		// trace(params);
		curSocket.environments[params[0]][command](params[1], params[2], fn ? fn : null);
	});


	
	socket.on('disconnect', function () {
		trace('dicconnected - ' + socketID);
		delete _banches[socketID];
	});
});

io.on('error', function (err) {
	trace(err);
});










