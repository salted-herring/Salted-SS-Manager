<?php

class Repo extends DataObject {
	protected static $db = array(
		'Title'				=>	'Varchar(256)',
		'Repo'				=>	'Text',
		'RepoDirPath'		=>	'Text'
	);

	protected static $has_one = array(
		'Site'				=>	'Site'
	);

	protected static $belongs_many_many = array(
		'Environments'		=>	'Environment'
	);

	protected static $summary_fields = array(
		'Title',
		'Repo',
		'RepoDirPath'
	);


	public function FirstRepo() {
		return $this->Site()->Repos()->sort(array('ID' => 'ASC'))->first()->Repo;
	}

	public function FirstRepoPath() {
		return $this->Site()->Repos()->sort(array('ID' => 'ASC'))->first()->RepoDirPath;
	}

	public function getCMSFields() {
		$fields = parent::getCMSFields();

		if (!empty($this->SiteID) && (empty($this->Repo) || empty($this->RepoDirPath))) {
			if (empty($this->Repo) && !empty($this->Site()->Repos()->count())) {
				$Repo = $this->Site()->Repos()->sort(array('ID' => 'ASC'))->first()->Repo;
				$fields->fieldByName('Root.Main.Repo')->setValue($Repo);
			}

			if (empty($this->RepoDirPath) && !empty($this->Site()->Repos()->count())) {
				$RepoDirPath = $this->Site()->Repos()->sort(array('ID' => 'ASC'))->first()->RepoDirPath;
				$fields->fieldByName('Root.Main.RepoDirPath')->setValue($RepoDirPath);
			}
		}

		return $fields;
	}
}