<?php

class DeployLog extends DataObject {
	
	protected static $db = array(
		'Title'			=>	'Varchar(48)',
		'Content'		=>	'Text'
	);

	protected static $has_one = array(
		'Deployment'	=>	'Deployment'
	);

	protected static $default_sort = array(
		'ID'			=>	'DESC'
	);

	public function getCMSFields() {
		$fields = parent::getCMSFields();
		$fields->addFieldToTab('Root.Main', LiteralField::create('Content', '<div class="output">'. $this->Content .'</div>'));
		return $fields;
	}

	public function onBeforeWrite() {
		parent::onBeforeWrite();
		if (empty($this->Title)){
			$this->Title = date("Y-m-d H:i:s");
			$this->write();
		}
	}
}