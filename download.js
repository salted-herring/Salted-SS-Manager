function trace(el) {
	console.log(el);
}
var scp 		=	require('scp2').Client,
	ssh			=	require('ssh2').Client,
	fs 			= 	require('fs'),
	client 		=	new scp({
						host: 'leochen.co.nz',
					    username: 'root',
					    password: 'Ikari_007%',
					});

client.on('error', function(err) {
	trace('got event error', err);
}).on('connect', function() { 
	trace('connecting...');
}).on('ready', function() {
	trace('ready');
}).on('end', function() {
	trace('connection end');
}).on('transfer', function(buffer, uploaded, total){
	trace(buffer);
	trace(uploaded);
	trace(total);
});

var src = '/root/9s.tgz',
	dest = '/Users/leochen/Sites/silverstripes/SDM/ssmanager/9s.tgz'

client.download = function(src, dest, callback) {
  var self = this;

  self.sftp(function(err,sftp){
    if (err) {
      return callback(err);
    }

    var sftp_readStream = sftp.createReadStream(src);
    sftp_readStream.on('data', function(data){
    	var i = sftp_readStream._readableState.pipes.bytesWritten;
    	trace(i + ':' + _total + ' = ' + Math.floor((i / _total)*100) + '%');
    	//trace(sftp_readStream.sftp._state.pktBuf);
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

var _total = 0;

function goget() {
	var conn 		=	new ssh();
	conn.on('ready', function() {
		conn.exec('wc -c ' + src, function(err, stream) {
		    if (err) throw err;
		    var lastOutput = '';
		    stream.on('close', function(code, signal) {
		    	console.log('Stream :: close :: code: ' + code + ', signal: ' + signal);
		     	conn.end();
		     	_total = lastOutput.split(' ')[0];
		     	_total = parseInt(_total);

		     	client.download(src, dest, function(err) {
					if (err) {
						trace(err);
					} else {
						trace('done');
					}
					client.close();
				});
		     	
		    }).on('data', function(data) {
		    	console.log('STDOUT: ' + data);
		    	lastOutput = data.toString().trim();		    	
		    }).stderr.on('data', function(data) {
		    	console.log('STDERR: ' + data);
		    });
		});
	}).connect({
		host: 'leochen.co.nz',
	    username: 'root',
	    password: 'Ikari_007%'
	});
}
goget();