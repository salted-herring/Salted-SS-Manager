<?php

class Server extends DataObject {
	protected static $db = array(
		'Title'				=>	'Varchar(48)',
		'ServerAddress'		=>	'Varchar(256)',
		'Port'				=>	'Int',
		'wwwUser'			=>	'Varchar(128)',
		'DeployUser'		=>	'Varchar(256)',
		'DeployPass'		=>	'Varchar(256)',
		'RequireSudo'		=>	'Boolean',
		'PrivateKeyPath'	=>	'Text'
	);

	protected static $default_sort = array(
		'Title'				=>	'ASC'
	);

	protected static $has_one = array(
		'Site'				=>	'Site',
		
	);
	
	protected static $belongs_many_many = array(
		'Environments'		=>	'Environment'
	);

	protected static $defaults = array(
		'Port'				=>	22
	);

	protected static $summary_fields = array(
		'Title',
		'ServerAddress',
		'Port',
		'DeployUser',
		'DeployPass'
	);

	public function getCMSFields() {
		$fields = parent::getCMSFields();
		//SaltedHerring\Debugger::inspect($this->getPrivateKeyPath());
		return $fields;
	}

	public function onBeforeWrite() {
		parent::onBeforeWrite();
	}
}