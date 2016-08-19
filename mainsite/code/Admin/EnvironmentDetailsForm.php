<?php

use SaltedHerring\Debugger as Debugger;

class EnvironmentDetailsForm extends GridFieldDetailForm {
	
}

class EnvironmentDetailsForm_ItemRequest extends GridFieldDetailForm_ItemRequest {
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
			$cmd = DeployScripts::RepoInit($repo->RepoDirPath, $repo->Repo, 'develop');
			if ($server->RequireSudo) {
				$cmds = explode(';', rtrim($cmd, ';'));
				foreach ($cmds as &$line) {
					if (!SaltedHerring\Utilities::startsWith($line, 'cd ')) {
						$line = DeployScripts::sudo($server->DeployPass) . $line;
					}
				}

				$cmd = implode(';', $cmds) . ';';
			}

			$postback = $ssh->exec($cmd, true);
			Debugger::inspect($postback);
			//$ssh->disconnect();
		}

		$form->sessionMessage('Block published', 'good', false);
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