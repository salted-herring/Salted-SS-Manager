<?php
use SaltedHerring\Debugger as Debugger;
use SaltedHerring\Utilities as Utilities;
class DeployScripts {

	public static function sudo($pass) {
		return "echo '" . $pass . "' | sudo -S ";
	}

	public static function RepoInit($path, $git_path, $branch, $sudo = null) {
		if (!empty($sudo)) {
			$cmd = self::sudo($sudo['pass']);
			$cmd .= self::mkdir($path);
			$cmd .= self::sudo($sudo['pass']);
			$cmd .= self::chown($sudo['user'], $path);
		} else {
			$cmd = self::mkdir($path);
		}

		$cmd .= 'cd ' . $path . ';';
		$cmd .= self::gitInit();
		$cmd .= self::gitRemoteAdd($git_path);
		$cmd .= self::gitFetchAll();
		$cmd .= self::gitCheckout($branch);
		$cmd .= self::gitSubmoduleInit();
		$cmd .= self::gitSubmoduleUpdate();
		if (!empty($sudo)) {
			$cmd .= self::sudo($sudo['pass']);
		}
		$cmd .= self::composerUpdate();
		$cmd .= 'cd themes/default;';
		$cmd .= self::bowerUpdate();
		return $cmd;
	}

	public static function DumpDB($path, $env, $site_name, $host, $table, $user, $pass, $deploy_user, $sudo = null, $cus_name = null) {
		if (!empty($sudo)) {
			$cmd = self::sudo($sudo['pass']);
			$cmd .= 'mkdir -p ' . $path . ';';
		} else {
			$cmd = 'mkdir -p ' . $path . ';';
		}
		if (!empty($sudo)) {
			$cmd .= self::sudo($sudo['pass']);
		}
		$cmd .= self::chown($deploy_user, $path);
		if (empty($cus_name)) {
			$cmd .= 'mysqldump -h ' . $host . ' -u ' . $user . ' -p\''. $pass .'\' ' . $table . ' > ' . $path . '/' . $env . '_' . Utilities::sanitiseClassName($site_name) . '-$(date "+%b_%d_%Y_%H_%M_%S").sql;';
		} else {
			$cmd .= 'mysqldump -h ' . $host . ' -u ' . $user . ' -p\''. $pass .'\' ' . $table . ' > ' . $path . '/' . $cus_name . ';';
		}
		return $cmd;
	}

	public static function sake() {
		return 'sake dev/build;';
	}

	public static function Deploy($site, $environment, $server, $updateComposer = false, $updateBower = false) {

		$sudo = array();
		if ($server->RequireSudo) {
			$sudo['user'] = $server->DeployUser;
			$sudo['pass'] = $server->DeployPass;
		}
		$branch = $environment->BoundBranch;
		$repo_dir = $environment->Repo()->first()->RepoDirPath;
		$db_dir = rtrim($environment->EnvironmentDirectory, '/')  . '/' . $site->SqlDumpDirectory;
		$db_host = $environment->DBServer;
		$db_table = $environment->DBName;
		$db_user = $environment->DBUser;
		$db_pass = $environment->DBPass;
		$root = $environment->Directory;
		$www_user = $server->wwwUser;

		$htaccess = !empty($environment->htaccessID) ? $environment->htaccess()->Content : null;

		$cmd = 'cd ' . $repo_dir . ';';
		$cmd .= self::RepoUpdate($branch, $sudo, $updateComposer, $updateBower);
		$cmd .= self::DumpDB($db_dir, $branch, $site->Title, $db_host, $db_table, $db_user, $db_pass, $server->DeployUser, $sudo);

		$cmd .= 'cd ' . $environment->EnvironmentDirectory . ';';

		if (!empty($sudo)) {
			$cmd .= self::sudo($sudo['pass']);
		}
		$cmd .= 'mkdir -p ' . $root . '_versions;';

		$ver = $root . '_versions/' . $root . '_' . date("Y_m_d_H_i_s"); /////////////////// make folder name

		if (!empty($sudo)) {
			$cmd .= self::sudo($sudo['pass']);
		}
		$cmd .= self::cp($repo_dir, $ver);
		$cmd .= 'cd ' . $ver . ';';
		if (!empty($sudo)) {
			$cmd .= self::sudo($sudo['pass']);
		}
		$cmd .= 'rm -rf assets;';

		if (!empty($sudo)) {
			$cmd .= self::sudo($sudo['pass']);
		}
		$cmd .= 'ln -s ../../assets .;';

		if (!empty($sudo)) {
			$cmd .= self::sudo($sudo['pass']);
		}
		$cmd .= self::rm('.git*');

		if (!empty($htaccess)) {
			if (!empty($sudo)) {
				$cmd .= self::sudo($sudo['pass']);
			}
			$cmd .= self::rm('.htaccess');
			
			$htaccess = str_replace('"', '\"', $htaccess);
			$htaccess = str_replace('$', '\$', $htaccess);
			$htaccess = str_replace("'", "\'", $htaccess);
			$cmd .= self::writefile($htaccess, '.htaccess', $sudo);
		}

		$cmd .= 'cd ..;';
		if (!empty($sudo)) {
			$cmd .= self::sudo($sudo['pass']);
		}
		$cmd .= self::chown($www_user, $environment->EnvironmentDirectory . '/' . $ver);

		$cmd .= 'cd ' . $environment->EnvironmentDirectory . ';';
		if (!empty($sudo)) {
			$cmd .= self::sudo($sudo['pass']);
		}
		$cmd .= self::rm($root, $sudo);
		if (!empty($sudo)) {
			$cmd .= self::sudo($sudo['pass']);
		}
		$cmd .= 'ln -s ' . $ver . ' ' . $root . ';';

		if (!empty($sudo)) {
			$cmd .= self::sudo($sudo['pass']);
		}
		$cmd .= self::chown($www_user, $root);
		$cmd .= 'cd ' . $root . ';';
		if (!empty($sudo)) {
			$cmd .= self::sudo($sudo['pass']);
		}
		$cmd .= self::sake();

		return $cmd;
	}

	public static function writefile($content, $filename, $sudo = null) {
		$contents = explode("\n", $content);
		$cmd = '';
		$n = 0;
		foreach ($contents as $line) {
			if (!empty($sudo)) {
				$cmd .= self::sudo($sudo['pass']);
			}
			if ($n == 0) {
				$cmd .= 'echo "' . trim($line) . '" > ' . $filename . ';';
			} else {
				$cmd .= 'echo "' . trim($line) . '" >> ' . $filename . ';';
			}
			$n++;
		}

		return $cmd;

	}

	public static function RepoUpdate($branch, $sudo = null, $updateComposer = false, $updateBower = false) {
		$cmd = 'git checkout composer.lock;';
		$cmd .= self::gitCheckout($branch);
		$cmd .= 'git checkout composer.lock;';
		$cmd .= self::gitPull($branch);

		if ($updateComposer) {
			if (!empty($sudo)) {
				$cmd .= self::sudo($sudo['pass']);
			}
			$cmd .= self::composerUpdate();
		}

		if ($updateBower) {
			$cmd .= 'cd themes/default;';			
			$cmd .= self::bowerUpdate();
		}

		return $cmd;
	}

	public static function scp($src, $dest, $pass = null) {
		if (!empty($pass)) {
			return "sshpass -p '" . $pass . "' scp " . $src . ' ' . $dest . ';';
		}

		return 'scp ' . $src . ' ' . $dest . ';';
	}

	public static function tar($environment_dir, $sql_path) {
		return 'cd ' . $environment_dir . ';tar -zcvf ss_asset_db.tgz assets ' . $sql_path . ';';
	}

	public static function gitPull($remote = 'master', $local = 'origin') {
		return 'git pull ' . $local . ' ' . $remote . ';';
	}

	public static function mv($from, $to) {
		return 'mv ' . $from . ' ' . $to . ';';
	}

	public static function cp($source, $destination) {
		return 'cp -rfp ' . $source . ' ' . $destination . ';';
	}

	public static function chown($user, $path) {
		return 'chown -R ' . $user . ':' . $user . ' ' . $path . ';';
	}

	public static function rm($path, $sudo_pass = null) {
		return (!empty($require_sudo) ? self::sudo($sudo_pass) : '') . 'rm -rf ' . $path . ';';
	}

	public static function repoExists($path) {
		return 'ls -al ' . $path . '/.git;';
	}

	public static function mkdir($path) {
		return 'mkdir -p ' . $path . ';';
	}

	public static function gitInit() {
		return 'git init;';
	}

	public static function gitRemoteAdd($git_path, $local = 'origin') {
		return 'git remote add ' . $local . ' ' . $git_path . ';';
	}

	public static function gitFetchAll() {
		return 'git fetch --all;';
	}

	public static function gitCheckout($branch = 'master') {
		return 'git checkout ' . $branch . ';';
	}

	public static function gitSubmoduleInit() {
		return 'git submodule init;';
	}

	public static function gitSubmoduleUpdate() {
		return 'git submodule update;';
	}

	public static function composerUpdate() {
		return 'composer update;';
	}

	public static function bowerUpdate($allow_root = false) {
		return 'bower update' . ($allow_root ? ' --allow-root;' : ';');
	}
}