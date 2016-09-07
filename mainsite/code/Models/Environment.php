<?php 
use SaltedHerring\Debugger as Debugger;
use SaltedHerring\Grid as Grid;
use SaltedHerring\Utilities as Utilities;


class Environment extends DataObject {
	protected static $db = array(
		'Title'					=>	'Varchar(48)',
		'EnvironmentDirectory'	=>	'Text',
		'Directory'				=>	'Varchar(256)',
		'RepoDir'				=>	'Varchar(256)',
		'BoundBranch'			=>	'Varchar(256)'
	);
	
	protected static $defaults = array(
		'Directory'				=>	'htdocs',
		'RepoDir'				=>	'repo'
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
		'AssetFolder'			=>	'Folder',
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
		$fields->removeByName('AssetFolder');
		$fields->fieldByName('Root.Main.Directory')->setTitle('Directory for www-root');
		if (!empty($this->SiteID)) {
			$fields->addFieldsToTab('Root.Server', Grid::make('Server', 'Server', $this->Server(), false, 'GridFieldConfig_RelationEditor'));
		}

		if (!empty($this->AssetFolderID)) {
			$fields->addFieldsToTab('Root.AssetsAndDatabase', LiteralField::create('AssetFolderPath', '<h2>Asset Folder</h2><p>' . $this->AssetFolder()->getFullPath() . '</p>'), 'AssetsAndDatabase');
		}

		$server = $this->Server();

		if ($server->count() > 0) {

			$server = $server->first();

			$enviro = array(
				'id'			=>	$this->ID,
				'name'			=>	$this->Title,
				'path'			=>	$this->EnvironmentDirectory,
				'web_root'		=>	$this->Directory,
				'repo_dir'		=>	$this->RepoDir,
				'branch'		=>	$this->BoundBranch,
				'sql_dump_dir'	=>	$this->Site()->SqlDumpDirectory,
				'sql_host'		=>	$this->DBServer,
				'sql_table'		=>	$this->DBName,
				'sql_user'		=>	$this->DBUser,
				'sql_pass'		=>	$this->DBPass,
				'server_addr'	=>	$server->ServerAddress,
				'server_port'	=>	$server->Port,
				'server_user'	=>	$server->DeployUser,
				'server_pass'	=>	$server->DeployPass,
				'asset_dir'		=>	rtrim($this->AssetFolder()->getFullPath(), '/'),
				'local_root'	=>	Director::baseFolder()
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
		
		if (!empty($this->EnvironmentDirectory)) {
			$this->EnvironmentDirectory = rtrim($this->EnvironmentDirectory, '/');
		}

		if (!empty($this->Title) && !empty($this->SiteID)) {
			$assetFolder = Folder::find_or_make(Utilities::sanitiseClassName($this->Site()->Title) . '/' . Utilities::sanitiseClassName($this->Title));
			$this->AssetFolderID = $assetFolder->ID;
		}

		if (!empty($this->SiteID)) {
			$this->DBServer		=	empty($this->DBServer) ? $this->Site()->DBServer : $this->DBServer;
			$this->DBName		=	empty($this->DBName) ? $this->Site()->DBName : $this->DBName;
			$this->DBUser		=	empty($this->DBUser) ? $this->Site()->DBUser : $this->DBUser;
			$this->DBPass		=	empty($this->DBPass) ? $this->Site()->DBPass : $this->DBPass;
		}
	}

	public function format() {
		$server = $this->Server()->first();
		$data = array(
					'id'			=>	$this->ID,
					'name'			=>	$this->Title,
					'path'			=>	$this->EnvironmentDirectory,
					'web_root'		=>	$this->Directory,
					'git'			=>	'',
					'repo_dir'		=>	$this->RepoDir,
					'branch'		=>	$this->BoundBranch,
					'sql_dump_dir'	=>	$this->Site()->SqlDumpDirectory,
					'sql_host'		=>	$this->DBServer,
					'sql_table'		=>	$this->DBName,
					'sql_user'		=>	$this->DBUser,
					'sql_pass'		=>	$this->DBPass,
					'server_addr'	=>	$server->ServerAddress,
					'server_port'	=>	$server->Port,
					'server_user'	=>	$server->DeployUser,
					'server_pass'	=>	$server->DeployPass,
					'asset_dir'		=>	rtrim($this->AssetFolder()->getFullPath(), '/')
				);

		if ($this->Repo()->first()) {

			$data['git']			=	$this->Repo()->first()->Repo;
		}

		return $data;
	}
}