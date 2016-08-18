<?php

class DatabaseSetting extends DataExtension {
	protected static $db = array(
		'DBServer'			=>	'Varchar(256)',
		'DBName'			=>	'Varchar(256)',
		'DBUser'			=>	'Varchar(256)',
		'DBPass'			=>	'Varchar(256)'
	);
}