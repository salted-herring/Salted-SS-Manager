<?php
use SaltedHerring\Debugger as Debugger;
use SaltedHerring\Utilities as Utilities;

class EnvironmentDetailsForm extends GridFieldDetailForm {
	
}

class EnvironmentDetailsForm_ItemRequest extends GridFieldDetailForm_ItemRequest {

	private $repo_ready = false;

	private static $allowed_actions = array(
		'edit',
		'view',
		'ItemEditForm',
		'SetupRepo'
	);
		
	public function ItemEditForm() {
		$form = parent::ItemEditForm();
		if ($form instanceof Form) {
			$actions = $form->Actions();
			$record = $this->record;
			$server = $record->Server()->first();
			$repo = $record->Repo()->first();
			if (!empty($server) && !empty($repo)) {
				$label = 'Re-setup Repo';

				$actions->push(FormAction::create('SetupRepo', $label));
				$actions->push(FormAction::create('CreateScripts', 'Create Deployment script'));
				$actions->push(FormAction::create('Backup', 'Backup'));
			}


			$actions->push(FormAction::create('test', 'test'));
			
			$form->setActions($actions);
		}
		return $form;
	}

	public function test($data, $form) {

	}

	public function Backup($data, $form) {
		$environment = $this->Record;
		$server = $environment->Server()->first();
		if ($server->RequireSudo) {
			$sudo = array('user' => $server->DeployUser, 'pass' => $server->DeployPass);
		}

		$sql_path = $environment->Site()->SqlDumpDirectory;
		$sql_fn = 'backup_dump.sql';
		$cmd = DeployScripts::rm($environment->EnvironmentDirectory . '/ss_asset_db.tgz;', !empty($sudo) ? $sudo : null);
		$cmd .= DeployScripts::DumpDB($sql_path, $environment->BoundBranch, $environment->Site()->Title, $environment->DBServer, $environment->DBName, $environment->DBUser, $environment->DBPass, $server->DeployUser, !empty($sudo) ? $sudo : null, $sql_fn);
		$cmd .= DeployScripts::tar($environment->EnvironmentDirectory, $sql_path .'/' . $sql_fn);
$folder = Folder::find_or_make(Utilities::sanitiseClassName($server->Site()->Title) . '/' . Utilities::sanitiseClassName($environment->Title));
		
		Debugger::inspect($cmd);

		$remote = $server->DeployUser . '@' . $server->ServerAddress . ':' . $environment->EnvironmentDirectory . '/ss_asset_db.tgz';
		$local = $folder->getFullPath() . date("Y_m_d_H_i_s") . '_ss_asset_db.tgz';

		$local_cmd = DeployScripts::scp($remote, $local, $server->DeployPass);

		Debugger::inspect($local_cmd);

		$ssh = new SSHConnector($server->ServerAddress, $server->Port, $server->FingerPrint, $server->DeployUser, $server->DeployPass);
		$ssh->connect();
		$postback = $ssh->exec($cmd, true);

		//Debugger::inspect($postback);
		if ($postback) {
			$form->sessionMessage('Assets and DB has been backed up', 'good', false);
		} else {
			$form->sessionMessage('Fail to backup', 'bad', false);
		}


		

		$controller = Controller::curr();
		return $this->edit($controller->getRequest());

	}

	public function CreateScripts($data, $form) {
		$record = $this->Record;
		$server = $record->Server()->first();
		if ($server->RequireSudo) {
			$sudo = array('user' => $server->DeployUser, 'pass' => $server->DeployPass);
		}
		$script = Director::baseFolder() . '/script-template.sh';
		$script = file_get_contents($script);
		$script = $this->processScript($script, $record);
		$cmd = DeployScripts::sudo($server->DeployPass);
		$cmd .= DeployScripts::rm($record->EnvironmentDirectory . '/deploy-script.sh', !empty($sudo) ? $server->DeployPass : null);
		$cmd .= DeployScripts::writefile($script, $record->EnvironmentDirectory . '/deploy-script.sh', empty($sudo) ? null : $sudo);

		$ssh = new SSHConnector($server->ServerAddress, $server->Port, $server->FingerPrint, $server->DeployUser, $server->DeployPass);
		$ssh->connect();
		$postback = $ssh->exec($cmd, true);
		//Debugger::inspect($postback);
		if ($postback) {
			$form->sessionMessage('Script created', 'good', false);
		} else {
			$form->sessionMessage('Fail to create Script', 'bad', false);
		}

		$controller = Controller::curr();
		return $this->edit($controller->getRequest());
	}

	public function SetupRepo($data, $form) {
		$record = $this->Record;
		$server = $record->Server()->first();
		$repo = $record->Repo()->first();

		if (!empty($server) && !empty($repo)) {
			$ssh = new SSHConnector($server->ServerAddress, $server->Port, $server->FingerPrint, $server->DeployUser, $server->DeployPass);
			$ssh->connect();
			
			$sudo = array();
			if ($server->RequireSudo) {
				$sudo['user'] = $server->DeployUser;
				$sudo['pass'] = $server->DeployPass;
			}

			$cmd = DeployScripts::RepoInit($repo->RepoDirPath, $repo->Repo, 'master', $sudo);

			if ($this->repo_ready) {
				$cmd = DeployScripts::rm($repo->RepoDirPath, $server->DeployPass) . $cmd;
			}

			//Debugger::inspect($cmd);

			$postback = $ssh->exec($cmd, true);
			//Debugger::inspect($postback);
			if ($postback) {
				$form->sessionMessage('Repo created', 'good', false);
			} else {
				$form->sessionMessage('Fail to create repo', 'bad', false);
			}
			//$ssh->disconnect();
		}
		
		$controller = Controller::curr();
		return $this->edit($controller->getRequest());
	}

	private function processScript($script, $environment) {
		$script = str_replace('"', '\"', $script);
		//$script = str_replace('%', '\%', $script);

		$site = $environment->Site();
		$repo = $environment->Repo()->first();
		$server = $environment->Server()->first();

		$env_dir = $environment->EnvironmentDirectory;
		$root_dir = $environment->Directory;
		$sql_dir = $site->SqlDumpDirectory;
		$repo_dir = $repo->RepoDirPath;
		$versions_dir = $env_dir . '/' . $root_dir . '_versions';
		$sql_host = $environment->DBServer;
		$sql_table = $environment->DBName;
		$sql_user = $environment->DBUser;
		$sql_pass = $environment->DBPass;
		$www_user = $server->wwwUser;

		$script = str_replace('$REP_SITE_ROOT', $env_dir, $script);
		$script = str_replace('$REP_HTDOCS_DIR', $root_dir, $script);
		$script = str_replace('$REP_SQL_DIR', $sql_dir, $script);
		$script = str_replace('$REP_REPO_DIR', $repo_dir, $script);
		$script = str_replace('$REP_VERSIONS_DIR', $versions_dir, $script);
		$script = str_replace('$REP_MYSQL_HOST', $sql_host, $script);
		$script = str_replace('$REP_MYSQL_USER', $sql_user, $script);
		$script = str_replace('$REP_MYSQL_PASS', $sql_pass, $script);
		$script = str_replace('$REP_MYSQL_TABLE', $sql_table, $script);
		$script = str_replace('$REP_WWWUSER', $www_user, $script);

		if ($server->RequireSudo) {
			$script = str_replace('#root_required ', '', $script);
		}

		$script = str_replace('$', '\$', $script);

		return $script;
	}
}