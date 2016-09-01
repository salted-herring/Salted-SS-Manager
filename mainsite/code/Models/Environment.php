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
		'Site'					=>	'Site',
		'htaccess'				=>	'HtAccess'
	);

	protected static $has_many = array(
		'AssetsAndDatabase'		=>	'File'
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

		$server = $this->Server();

		if ($server->count() > 0) {

			$server = $server->first();


			$enviro = array(
				'id'			=>	$this->ID,
				'name'			=>	$this->Title,
				'path'			=>	$this->EnvironmentDirectory,
				'web_root'		=>	$this->Directory,
				'branch'		=>	$this->BoundBranch,
				'sql_dump_dir'	=>	$this->Site()->SqlDumpDirectory,
				'sql_host'		=>	$this->DBServer,
				'sql_table'		=>	$this->DBName,
				'sql_user'		=>	$this->DBUser,
				'sql_pass'		=>	$this->DBPass,
				'server_addr'	=>	$server->ServerAddress,
				'server_user'	=>	$server->DeployUser,
				'server_pass'	=>	$server->DeployPass
			);

			$fields->addFieldsToTab(
				'Root.Main', 
				LiteralField::create(
					'EnvironmentScript', 
					"<script type=\"text/javascript\">var environment = ". json_encode($enviro) .";</script>"
				)
			);
		}

		Requirements::combine_files(
            'socket.js',
            array(
                'themes/default/js/socket.io.js',
                'mainsite/js/cms.scripts.js'
			)
		);

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
	}
}