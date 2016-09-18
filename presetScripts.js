/*rm -rf ~/domains/dev-sh.saltydev.com/ss_asset_db.tgz;
mkdir -p sql-dumps;
chown -R saltydev:saltydev sql-dumps;
mysqldump -h localhost -u saltydev -p'JtfbVzt9BPX2iHnN' dev_sh > sql-dumps/backup_dump.sql;
cd ~/domains/dev-sh.saltydev.com;
tar -zcvf ss_asset_db.tgz assets sql-dumps/backup_dump.sql;*/

exports.rm = function(path) {
	return 'rm -rf ' + path + ';';
};

exports.mkdir = function(dir_name) {
	return 'mkdir -p ' + dir_name + ';';
};

exports.chown = function(user, dir_path) {
	return 'chown -R ' + user + ':' + user + ' ' + dir_path + ';';
};

exports.mysqldump = function(host, table, user, pass, out_path) {
	var rpass = pass.replace(/'/gi, '\'');
	return "mysqldump -h " + host + " -u " + user + " -p'" + rpass + "' " + table + ' > ' + out_path + ';';
};

exports.cd = function(path) {
	return 'cd ' + path + ';';
};

exports.tar = function(things, file_name) {
	file_name = !file_name ? 'ss_asset_db.tgz' : file_name;
	var things = things.join(' ');
	return 'tar -zcvf ' + file_name + ' ' + things + ';';
}

exports.git = function(cmd) {
	return 'git ' + cmd + ';'
}

exports.composerUpdate = function() {
	return 'composer update;';
}

exports.bowerUpdate = function(isRoot) {
	return 'bower update --allow-root;';
}

exports.sudo = function(pass) {
	return "echo '" + pass + "' | sudo -S ";
}