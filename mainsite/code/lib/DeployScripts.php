<?php

class DeployScripts {

	public static function sudo($pass) {
		return "echo '" . $pass . "' | sudo -S ";
	}

	public static function RepoInit($path, $git_path, $branch) {
		$cmd = self::mkdir($path);
		$cmd .= 'cd ' . $path . ';';
		$cmd .= self::gitInit();
		$cmd .= self::gitRemoteAdd($git_path);
		$cmd .= self::gitFetchAll();
		$cmd .= self::gitCheckout($branch);
		$cmd .= self::composerUpdate();
		$cmd .= 'cd themes/default;';
		$cmd .= self::bowerUpdate();
		return $cmd;
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

	public static function composerUpdate() {
		return 'composer update;';
	}

	public static function bowerUpdate($allow_root = false) {
		return 'bower update' . ($allow_root ? ' --allow-root;' : ';');
	}
}