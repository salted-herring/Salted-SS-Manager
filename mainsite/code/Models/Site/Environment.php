<?php use SaltedHerring\Debugger as Debugger;

class Environment extends DataObject {
	protected static $db = array(
		'Title'					=>	'Varchar(48)',
		'EnvironmentDirectory'	=>	'Text',
		'Directory'				=>	'Varchar(256)',
		'BoundBranch'			=>	'Varchar(256)'		
	);

	protected static $default_sort = array(
		'Title'				=>	'ASC'
	);

	protected static $has_one = array(
		'Site'					=>	'Site',
		'Server'				=>	'Server'
	);

	protected static $extensions = array(
		'DatabaseSetting'
	);

	public function getCMSFields() {
		$fields = parent::getCMSFields();
		$fields->removeByName('SiteID');
		$fields->removeByName('ServerID');
		$fields->fieldByName('Root.Main.Directory')->setTitle('Directory for www-root');
		if (!empty($this->SiteID)) {
			$fields->addFieldsToTab('Root.Main', DropdownField::create('ServerID', 'Server', $this->Site()->Servers()->map('ID', 'Title'))->setEmptyString('- select one -'));
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
		$ssh = new SSHConnector($this->Server()->ServerAddress, $this->Server()->Port, $this->Server()->FingerPrint, $this->Server()->DeployUser, $this->Server()->DeployPass);
		$ssh->connect();
		$postback = $ssh->exec('pwd');
		//$ssh->disconnect();
		Debugger::inspect($postback);
	}
}