<?php

use SaltedHerring\Debugger as Debugger;

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
				$ssh = new SSHConnector($server->ServerAddress, $server->Port, $server->FingerPrint, $server->DeployUser, $server->DeployPass);
				$con = $ssh->connect();
				$cmd = DeployScripts::repoExists($repo->RepoDirPath);
				$postback = $ssh->exec($cmd);
				if (!$postback) {
					$label = 'Setup Repo';
				} else {
					$this->repo_ready = true;
				}
				$actions->push(FormAction::create('SetupRepo', $label));
			}

			
			
			$form->setActions($actions);
		}
		return $form;
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

	//, $server->RequireSudo ? $server->DeployPass : null
	
}

/*
echo 'Toh8haisav' | sudo -S mkdir -p /var/www/staging.umbrellar.com/gitpo;
echo 'Toh8haisav' | sudo -S cd /var/www/staging.umbrellar.com/gitpo;
echo 'Toh8haisav' | sudo -S git init;echo 'Toh8haisav' | sudo -S git remote add origin git@github.com:salted-herring/umbrellar.git;
echo 'Toh8haisav' | sudo -S git fetch --all;
echo 'Toh8haisav' | sudo -S git checkout develop;
echo 'Toh8haisav' | sudo -S composer update;
echo 'Toh8haisav' | sudo -S cd themes/default;
echo 'Toh8haisav' | sudo -S bower update;
*/