<?php

class Server extends DataObject {
	protected static $db = array(
		'Title'				=>	'Varchar(48)',
		'ServerAddress'		=>	'Varchar(256)',
		'Port'				=>	'Int',
		'FingerPrint'		=>	'Text',
		'DeployUser'		=>	'Varchar(256)',
		'DeployPass'		=>	'Varchar(256)',
	);

	protected static $default_sort = array(
		'Title'				=>	'ASC'
	);

	protected static $has_one = array(
		'Site'				=>	'Site'
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

	public function onBeforeWrite() {
		parent::onBeforeWrite();
		if (!empty($this->ServerAddress) && !empty($this->Port)) {
			if ($connection = ssh2_connect($this->ServerAddress, $this->Port)) {
				$fingerprint = ssh2_fingerprint($connection,
	               SSH2_FINGERPRINT_MD5 | SSH2_FINGERPRINT_HEX);
				$this->FingerPrint = $fingerprint;
			}
		}
	}
}