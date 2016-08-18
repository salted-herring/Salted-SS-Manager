<?php

class URLExtension extends DataExtension {
	public static $db = array(
		'URLSegment' => 'Varchar(255)'
	);
	
	public static $indexes = array(
		"URLSegment" => true
	);
	
	public function __construct() {
		parent::__construct();
	}
	
	public function getCMSFields() {	 
		$fields = parent::getCMSFields();
		return $fields;
	}
	
	public function updateCMSFields(FieldList $fields) {
		$fields->removeFieldsFromTab("Root.Main", array(
			'URLSegment'
		));
	}
	
	public function Link($action = null) {
		return Controller::join_links('/', $this->owner->URLSegment);
	}
	
	//Set URLSegment to be unique on write
	public function onBeforeWrite() {		
		// If there is no URLSegment set, generate one from Title
		if ((!$this->owner->URLSegment || $this->owner->URLSegment == 'new-page')) 
		{
			$name = $this->owner->Title ? $this->owner->Title : 'new-page';
			$filter = URLSegmentFilter::create();
			$this->owner->URLSegment = $filter->filter($name);
			
			// Fallback to generic page name if path is empty (= no valid, convertable characters)
			if(!$this->owner->URLSegment || $this->owner->URLSegment == '-' || $this->owner->URLSegment == '-1') {
				$this->owner->URLSegment = "page-$this->owner->ID";
			}
		} elseif ($this->owner->isChanged('URLSegment')) {
			// Make sure the URLSegment is valid for use in a URL
			$segment = preg_replace('/[^A-Za-z0-9]+/', '-', $this->owner->URLSegment);

			// If after sanitising there is no URLSegment, give it a reasonable default
			if(!$segment) {
				$segment = "page-$this->owner->ID";
			}
			$this->owner->URLSegment = $segment;
		}

		// Ensure that this object has a non-conflicting URLSegment value.
		$count = 2;
		while($this->LookForExistingURLSegment($this->owner->URLSegment)) 
		{
			$this->owner->URLSegment = preg_replace('/-[0-9]+$/', null, $this->owner->URLSegment) . '-' . $count;
			$count++;
		}

		parent::onBeforeWrite();
	}

	//Test whether the URLSegment exists already on another Product
	public function LookForExistingURLSegment($URLSegment) {
		return (DataObject::get_one(get_class($this->owner), "URLSegment = '" . $URLSegment ."' AND " 
					. get_class($this->owner) . ".ID != " . $this->owner->ID));
	}
}