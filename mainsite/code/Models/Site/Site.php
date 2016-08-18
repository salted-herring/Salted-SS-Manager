<?php

class Site extends DataObject {
	protected static $db = array(
		'Title'				=>	'Varchar(256)'
	);

	protected static $default_sort = array(
		'Title'				=>	'ASC'
	);

	protected static $extensions = array(
		'DatabaseSetting'
	);

	protected static $has_many = array(
		'Environments'	=>	'Environment',
		'Servers'		=>	'Server'
	);
}