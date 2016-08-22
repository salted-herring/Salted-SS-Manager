<?php

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

	public static function Deploy($repo_dir, $branch, $root, $www_user, $sudo = null, $updateComposer = false, $updateBower = false) {
		$cmd = 'cd ' . $repo_dir . ';';
		$cmd .= self::RepoUpdate($branch, $updateComposer, $updateBower);

		$repo_segments = explode('/', rtrim($repo_dir,'/'));
		$repo_segments = array_pop($repo_segments);
		$parent_dir = implode('/', $repo_segments);

		$cmd .= 'cd ' . $parent_dir . ';';

		if (!empty($sudo)) {
			$cmd .= self::sudo($sudo['pass']);
		}
		$cmd .= self::cp($repo_dir, $root . '_new');
		if (!empty($sudo)) {
			$cmd .= self::sudo($sudo['pass']);
		}
		$cmd .= self::rm($root . '_old');
		if (!empty($sudo)) {
			$cmd .= self::sudo($sudo['pass']);
		}
		$cmd .= self::mv($root, $root . '_old');
		if (!empty($sudo)) {
			$cmd .= self::sudo($sudo['pass']);
		}
		$cmd .= self::mv($root . '_new', $root);
		$cmd .= 'cd ' . $root . ';';
		if (!empty($sudo)) {
			$cmd .= self::sudo($sudo['pass']);
		}
		$cmd .= 'ln -s ../assets .;';
		if (!empty($sudo)) {
			$cmd .= self::sudo($sudo['pass']);
		}
		$cmd .= self::rm('.git*');
		if (!empty($sudo)) {
			$cmd .= self::sudo($sudo['pass']);
		}
		$cmd .= self::chown($www_user, $root);

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