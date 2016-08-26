<?php 
use SaltedHerring\Debugger as Debugger;
use SaltedHerring\Utilities as Utilities;

class Deployment extends DataObject {
	protected static $db = array(
		'Title'				=>	'Varchar(48)',
		'DeployType'		=>	'Enum("Repo Sync,Deployment")',
		'ComposerUpdate'	=>	'Boolean',
		'BowerUpdate'		=>	'Boolean',
		'Environment'		=>	'Int'
	);
	
	protected static $has_one = array(
		'Site'				=>	'Site'
	);
	
	public function getCMSFields() {
		$fields = parent::getCMSFields();
		$fields->removeByName('Root.Main.Environment');
		if (!empty($this->SiteID)) {
			$fields->addFieldToTab(
				'Root.Main',
				DropdownField::create('Environment', 'Deploy to', $this->Site()->Environments()->map('ID', 'Title'))->setEmptyString('- select one -'),
				'SiteID'
			);
		}

		if ($msg = Session::get('Message')) {
			$fields->addFieldToTab('Root.Main', LiteralField::create('message', '<div class="deployment-msg">'.$msg['Message'].'</div>'));
			Session::clear('Message');
		}

		return $fields;
	}
	
	public function onAfterWrite() {
		parent::onAfterWrite();
		if ($env = Environment::get()->byID($this->Environment)) {
			$cmd = '';
			$branch = $env->BoundBranch;
			$repo = $env->Repo()->first();
			$site = $env->Site();
			$msg = '';

			if ($server = $env->Server()->first()) {
				if ($this->DeployType == 'Repo Sync') {
					$cmd = DeployScripts::updateRepo($repo->RepoDirPath, $branch, $this->ComposerUpdate, $this->BowerUpdate);

				} else {
					$cmd = DeployScripts::Deploy($site, $env, $server, $this->ComposerUpdate, $this->BowerUpdate);
				}

				$ssh = new SSHConnector($server->ServerAddress, $server->Port, $server->FingerPrint, $server->DeployUser, $server->DeployPass);
				$ssh->connect();
				$msg = $ssh->exec($cmd, true);
				$msg = str_replace("\n", '<br />', $msg);
			}

			Session::set('Message', array(
	            'Message' => $msg
	        ));
		}	
	}
	
	
}