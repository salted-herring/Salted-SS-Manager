<?php use SaltedHerring\Debugger as Debugger; use SaltedHerring\Grid as Grid;

class Environment extends DataObject {
	protected static $db = array(
		'Title'					=>	'Varchar(48)',
		'EnvironmentDirectory'	=>	'Text',
		'Directory'				=>	'Varchar(256)',
		'BoundBranch'			=>	'Varchar(256)'		
	);
	
	protected static $defaults = array(
		'Directory'				=>	'htdocs'
	);
	
	protected static $summary_fields = array(
		'Title',
		'PathToRoot',
		'BoundBranch'
	);

	protected static $default_sort = array(
		'Title'					=>	'ASC'
	);

	protected static $has_one = array(
		'Site'					=>	'Site'
	);
	
	protected static $many_many = array(
		'Server'				=>	'Server',
		'Repo'					=>	'Repo'
	);
	
	protected static $extensions = array(
		'DatabaseSetting'
	);
	
	public function PathToRoot() {
		return rtrim($this->EnvironmentDirectory, '/')  . '/' . $this->Directory;
	}

	public function getCMSFields() {
		$fields = parent::getCMSFields();
		$fields->removeByName('SiteID');
		$fields->removeByName('Server');
		$fields->fieldByName('Root.Main.Directory')->setTitle('Directory for www-root');
		if (!empty($this->SiteID)) {
			$fields->addFieldsToTab('Root.Server', Grid::make('Server', 'Server', $this->Server(), false, 'GridFieldConfig_RelationEditor'));
		}

		return $fields;
	}

	public function onBeforeWrite() {
		parent::onBeforeWrite();
		if (!empty($this->SiteID)) {
			$this->DBServer		=	empty($this->DBServer) ? $this->Site()->DBServer : $this->DBServer;
			$this->DBName		=	empty($this->DBName) ? $this->Site()->DBName : $this->DBName;
			$this->DBUser		=	empty($this->DBUser) ? $this->Site()->DBUser : $this->DBUser;
			$this->DBPass		=	empty($this->DBPass) ? $this->Site()->DBPass : $this->DBPass;
		}

		//$host, $port, $server_fp, $user, $pass
		/*if ($server = $this->Server()->first()) {
			$ssh = new SSHConnector($server->ServerAddress, $server->Port, $server->FingerPrint, $server->DeployUser, $server->DeployPass);
			$ssh->connect();
			$postback = $ssh->exec('cd ' . $this->EnvironmentDirectory . '; ls -al;');
			Debugger::inspect($postback);
			$ssh->disconnect();
		}*/
	}
}