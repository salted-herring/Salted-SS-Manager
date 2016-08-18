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
		return $fields;
	}
	
	public function onAfterWrite() {
		parent::onAfterWrite();
		if ($env = Environment::get()->byID($this->Environment)) {
			$cmd = '';
			$sqlfolder = rtrim($env->EnvironmentDirectory, '/')  . '/' . $this->Site()->SqlDumpDirectory;
			$host = $env->DBServer;
			$table = $env->DBName;
			$user = $env->DBUser;
			$pass = $env->DBPass;
			
			if ($server = $env->Server()->first()) {
				$cmd .= $this->DumpDB($sqlfolder, Utilities::sanitiseClassName($env->Title), $host, $table, $user, $pass, $server->RequireSudo ? $server->DeployPass : null);
				$ssh = new SSHConnector($server->ServerAddress, $server->Port, $server->FingerPrint, $server->DeployUser, $server->DeployPass);
				$ssh->connect();
				
				$postback = $ssh->exec($cmd);
				Debugger::inspect($postback);
			}
		}	
	}
	
	private function DumpDB($path, $env, $host, $table, $user, $pass, $sudo_pass = null) {
		$prefix = !empty($sudo_pass) ? ("echo '" . $sudo_pass . "' | sudo -S ") : '';
		$cmd = $prefix . 'mkdir -p ' . $path . ';';
		$cmd .= $prefix . 'chmod -R 777 ' . $path . ';';
		$cmd .= $prefix . 'mysqldump -h ' . $host . ' -u ' . $user . ' -p\''. $pass .'\' ' . $table . ' > ' . $path . '/' . $env . '_' . Utilities::sanitiseClassName($this->Site()->Title) . '-$(date "+%b_%d_%Y_%H_%M_%S").sql;';
		//Debugger::inspect($cmd);
		return $cmd;
	}
}