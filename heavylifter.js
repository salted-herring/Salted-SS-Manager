/*var sys = require('sys')
var exec = require('child_process').exec;
function puts(error, stdout, stderr) { sys.puts(stdout) }
exec("ls -la", puts);
process.argv.forEach(function (val, index, array) {
  console.log(index + ': ' + val);
});
*/
var _benches			=	[];
var scp 				=	require('scp2').Client;
var fs 					= 	require('fs');
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
	this.server_port	=	environment.server_port;
	this.server_user	=	environment.server_user;
	this.server_pass	=	environment.server_pass;
	this.asset_dir		=	environment.asset_dir;

	this.run 			=	function(cmd, onDone, onFail) {
		var conn 		=	new ssh();
		conn.on('ready', function() {
			conn.exec(cmd ? cmd : 'uptime', function(err, stream) {
			    if (err) throw err;
			    var lastOutput = '';
			    stream.on('close', function(code, signal) {
			    	console.log('Stream :: close :: code: ' + code + ', signal: ' + signal);
			     	conn.end();
			     	if (onDone) {
			    		onDone(lastOutput);
			    	}
			    }).on('data', function(data) {
			    	console.log('STDOUT: ' + data);
			    	lastOutput = data.toString().trim();
			    	_self.socket.emit('message', data.toString());			    	
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

	this.download		=	function(src, dest, total) {

		var client 		=	new scp({
								host: environment.server_addr,
							    username: environment.server_user,
							    password: environment.server_pass,
							}),
			socket 		=	_self.socket;

		client.download = function(src, dest, callback) {
			var self = this;

		  	self.sftp(function(err,sftp){
			    if (err) {
			      return callback(err);
			    }

			    var sftp_readStream = sftp.createReadStream(src);
			    sftp_readStream.on('data', function(data){
			    	var i = sftp_readStream._readableState.pipes.bytesWritten;
			    	//trace(i + ':' + total + ' = ' + Math.floor((i / total)*100) + '%');
			    	//trace(sftp_readStream.sftp._state.pktBuf);
			    	socket.emit('transfer_progress', (i / total));
			    });
			    sftp_readStream.on('error', function(err){
			      callback(err);
			    });
			    sftp_readStream.pipe(fs.createWriteStream(dest))
			    .on('close',function(){
			      self.emit('read', src);
			      self.close();
			      callback(null);
			    })
			    .on('error', function(err){
			      callback(err);
			    });
		  	});
		};

		client.on('error', function(err) {
			trace('got event error', err);
			socket.emit('message', err.toString());
		}).on('connect', function() { 
			trace('connecting...');
			socket.emit('message', 'connecting...');
		}).on('ready', function() {
			trace('ready');
			socket.emit('message', 'downloaded');
		}).on('end', function() {
			socket.emit('message', 'connection closed');
		}).on('transfer', function(progress) {
			trace(Math.floor(progress*100) + '%');
		});

		client.download(src, dest, function(err) {
			if (err) {
				socket.emit('message', err.toString());
				trace(err);
			} else {
				socket.emit('message', 'done transferring');
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

	if (_benches[socketID] === undefined) {
		_benches[socketID] = new banch(socketID);
		curSocket		=	_benches[socketID];
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
		var lcEnvironment	=	curSocket.environments[data.environment_id],
			cmd 			=	cmdmaker(data.cmd,lcEnvironment),
			commondType		=	data.cmd;

		var filename = 'ss_asset_db.tgz';
		lcEnvironment.run(cmd, function(data){
			trace('packing done...');
			if (commondType == 'backup') {
				trace('checking realpath...');
				var filename = 'ss_asset_db.tgz';
				lcEnvironment.run('realpath ' + lcEnvironment.path + '/' + filename, function(remote_path) {
					remote_path = remote_path.trim();
					trace('remote: ' + remote_path);
					trace('local: ' + lcEnvironment.asset_dir + '/' + Date.now() + '_' + filename);
					trace('command: ' + 'wc -c ' + remote_path);
					lcEnvironment.run('wc -c ' + remote_path, function(data) {
						var total_size = data.split(' ')[0];
		     			total_size = parseInt(total_size);
						socket.emit('message', 'total to download: ' + total_size + ' byptes');
						if (remote_path.length > 0) {
							lcEnvironment.download(
								remote_path,
								lcEnvironment.asset_dir + '/' + Date.now() + '_' + filename,
								total_size
							);
							socket.emit('message', 'start downloading...');
						} else {
							socket.emit('message', 'remote path is incorrect');
						}
					});
				});
				
			}
		});
		//lcEnvironment.last_cmd_len = cmd.length;

		//lcEnvironment.shell.write(cmd + '\n');
	});
	
	socket.on('disconnect', function () {
		trace('dicconnected - ' + socketID);
		
		delete _benches[socketID];
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


