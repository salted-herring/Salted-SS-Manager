<?php use SaltedHerring\Grid as Grid;

class Site extends DataObject {
	protected static $db = array(
		'Title'				=>	'Varchar(256)',
		'SqlDumpDirectory'	=>	'Varchar(256)'
	);
	
	protected static $defaults = array(
		'SqlDumpDirectory'	=>	'sql-dumps'
	);

	protected static $default_sort = array(
		'Title'				=>	'ASC'
	);

	protected static $extensions = array(
		'DatabaseSetting'
	);

	protected static $has_many = array(
		'Repos'			=>	'Repo',
		'Environments'	=>	'Environment',
		'Servers'		=>	'Server',
		'Deployments'	=>	'Deployment'
	);

	public function getCMSFields() {
		$fields = parent::getCMSFields();

		
		if (!empty($this->ID)) {
			$fields->addFieldToTab(
				'Root.Environments',
				$grid = Grid::make('Environments', 'Environments', $this->Environments(), false, 'GridFieldConfig_RelationEditor')
			);

			$grid->getConfig()
                 ->removeComponentsByType('GridFieldDetailForm')
                 ->addComponents(
                    new EnvironmentDetailsForm()
                 );
		}

		return $fields;
	}
}