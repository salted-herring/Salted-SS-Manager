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
var ssh 				=	require('ssh2').Client;
var scripts				=	require('./presetScripts.js');
var banch 				=	function(id) {
	this.id 			=	id;
	this.environments  	=	[];

	return this;
};


var environment 		=	function(socket, environment) {
	var _self			=	this;
	this.socket 		=	socket;
	this.id				=	environment.id;
	this.name			=	environment.name;
	this.path			=	environment.path;
	this.web_root		=	environment.web_root;
	this.branch			=	environment.branch;
	this.sql_dump_dir	=	environment.sql_dump_dir;
	this.sql_host		=	environment.sql_host;
	this.sql_table		=	environment.sql_table;
	this.sql_user		=	environment.sql_user;
	this.sql_pass		=	environment.sql_pass;
	this.server_addr	=	environment.server_addr;
	this.server_user	=	environment.server_user;
	this.server_pass	=	environment.server_pass;
	/*this.conn 			=	new ssh();
	this.shell 			=	null;

	this.conn.on('ready', function() {
		_self.socket.emit('message', 'server connected');
		_self.conn.shell(function(err, stream) {
			if (err) throw err;
			_self.shell = stream;
			stream.on('close', function() {
				console.log('Stream :: close');
				_self.conn.end();
			}).on('data', function(data) {
				//if (_self.last_cmd_len <= 0) {
				console.log('STDOUT: (' + data.length + ') ' + data);
				_self.socket.emit('message', data.toString());
				//} else {
					//_self.last_cmd_len--;
				//}
			}).stderr.on('data', function(data) {
				console.log('STDERR: ' + data);
			});
			//stream.end('ls -l\nexit\n');
		});
	}).connect({
		host: environment.server_addr,
	    username: environment.server_user,
	    password: environment.server_pass//,
		//privateKey: require('fs').readFileSync('/here/is/my/key')
	});*/

	this.run 			=	function(cmd, onDone, onFail) {
		var conn 		=	new ssh();
		conn.on('ready', function() {
			conn.exec(cmd ? cmd : 'uptime', function(err, stream) {
			    if (err) throw err;
			    stream.on('close', function(code, signal) {
			    	console.log('Stream :: close :: code: ' + code + ', signal: ' + signal);
			     	conn.end();
			    }).on('data', function(data) {
			    	console.log('STDOUT: ' + data);
			    	_self.socket.emit('message', data.toString());
			    	if (onDone) {
			    		onDone();
			    	}
			    }).stderr.on('data', function(data) {
			    	console.log('STDERR: ' + data);
			    });
			});
		}).connect({
			host: environment.server_addr,
		    username: environment.server_user,
		    password: environment.server_pass//,
			//privateKey: require('fs').readFileSync('/here/is/my/key')
		});
	};


	this.client 		=	function() {
		var client 		=	new scp.Client({
								host: environment.server_addr,
							    username: environment.server_user,
							    password: environment.server_pass,
							}),
			socket 		=	_self.socket;

		client.on('error', function(err) {
			trace('got event error', err);
			socket.emit('message', err);
		}).on('connect', function() { 
			trace('connecting...');
			socket.emit('message', 'connecting...');
		}).on('ready', function() {
			trace('ready');
			socket.emit('message', 'downloaded');
		}).on('transfer', function(buffer, uploaded, total){
			trace(buffer);
			trace(uploaded);
			trace(total);
		}).on('end', function() {
			socket.emit('message', 'connection closed');
		});

		return client;
	};

	this.download		=	function(src, dest) {
		var client 		=	new _self.client();
		client.download(src, dest, function(err) {
			if (err) {
				socket.emit('message', err);
			}

			client.close();
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
			curSocket.environments['enviro-' + prEnvironment.id] = new environment(socket, prEnvironment);
		}
	});

	socket.on('download_backup', function(data) {
		curSocket.environments[data.environment_id].download(data.src, data.dest);
	});

	socket.on('ssh', function(data) {
		// trace('params from client:');
		// trace(params);
		trace('socket receive commnad');
		var lcEnvironment	=	curSocket.environments[data.environment_id],
			cmd 			=	cmdmaker(data.cmd,lcEnvironment);
		lcEnvironment.run(cmd, function(){
			/*if (data.cmd == 'backup') {
				//src: '/home/saltydev/domains/dev-sh.saltydev.com/robots.txt', 
				//dest: '/Users/leo/Sites/SDM/htdocs/assets/salted-herring/dev/robots.txt'
				lcEnvironment.download()
			}*/
		});
		//lcEnvironment.last_cmd_len = cmd.length;

		//lcEnvironment.shell.write(cmd + '\n');
	});
	
	socket.on('disconnect', function () {
		trace('dicconnected - ' + socketID);
		
		delete _banches[socketID];
	});

	socket.on('error', function (err) {
		trace(err);
		socket.emit('message', err);
	});
});



function cmdmaker(prCmd, environment) {
	var cmd = 'ls -al';
	switch (prCmd) {
		case 'backup':
			var sqlfn = 'backup_dump_' + Date.now() + '.sql';
			cmd = scripts.rm(environment.path + '/ss_asset_db.tgz');
			cmd += scripts.cd(environment.path);
			cmd += scripts.mkdir(environment.sql_dump_dir);
			cmd += scripts.chown(environment.server_user, environment.sql_dump_dir);
			//host, table, user, pass, out_path
			cmd += scripts.mysqldump(environment.sql_host, environment.sql_table, environment.sql_user, environment.sql_pass, environment.sql_dump_dir + '/' + sqlfn);
			cmd += scripts.tar(
				[
					'assets',
					environment.sql_dump_dir + '/' + sqlfn
				]
			);

			break;
	}

	return cmd;
}

//ss_asset_db.tgz


/*rm -rf ~/domains/dev-sh.saltydev.com/ss_asset_db.tgz;
mkdir -p sql-dumps;
chown -R saltydev:saltydev sql-dumps;
mysqldump -h localhost -u saltydev -p'JtfbVzt9BPX2iHnN' dev_sh > sql-dumps/backup_dump.sql;
cd ~/domains/dev-sh.saltydev.com;
tar -zcvf ss_asset_db.tgz assets sql-dumps/backup_dump.sql;*/


