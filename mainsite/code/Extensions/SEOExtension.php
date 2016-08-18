<?php

/**
 * @file SEOExtensions.php
 *
 * Additional tags for SEO - keywords & robots tags.
 * */
class SEOExtension extends DataExtension {
	private static $db = array(
		'MetaKeywords'	=> 'Varchar(256)',
		'MetaRobots'	=> 'Varchar(128)'
	);
	
	private static $defaults = array(
		'MetaRobots' => 'INDEX, FOLLOW'
	);
	
	/**
	 * Specifies the target field to get the default values from (when a page is 
	 * saved without meta content being added)
	 * */
	public $defaultMetaTargets = array(
		'MetaDescription'		=> 'Content',
		'OGDescription'			=> 'Content',
		'TwitterDescription'	=> 'Content',
		
		'OGTitle'				=> 'Title',
		'TwitterTitle'			=> 'Title',
		
		'TwitterImage'			=> array(
			'Image'				=> false,
			'Cropped'			=> false
		),
		'OGImage'				=> array(
			'Image'				=> false,
			'Cropped'			=> false
		)
	);
	
	public function updateCMSFields(FieldList $fields) {
		$fields->insertBefore(new TextField('MetaKeywords'), 'MetaDescription');
		$fields->insertBefore(new TextField('MetaRobots'), 'MetaDescription');
	}
	
	
	public function onBeforeWrite() {
		parent::onBeforeWrite();
		
		$fields = $this->getTheField('defaultMetaTargets');
			
		/**
		 * Set up descriptions.
		 * */
		foreach($this->getValidKeys('/.*Description/', $fields) as $key) {
			/**
			 * If a relationship field is passed in, get the related field.
			 * */
			if (strstr($fields[$key], '.') !== false) {
				$parts = explode('.', $fields[$key]);
				
				if ($parts[1] == 'hasOne') {
					$description = $this->owner->getComponent($parts[0])->$parts[2];
				} else {
					$description = '';
				}
			} else {
				$description = $this->owner->getField($fields[$key]);
			}
		
			/**
			 * Extract & save meta description if necessary.
			 * */
			if (empty($this->owner->$key) && !empty($description)) {
				$matches = array();
				$this->owner->$key = SaltedHerring\Utilities::getWordsWithinCharLimit(strip_tags($description));
			}
		}
		
		/**
		 * Set up titles.
		 * */
		foreach($this->getValidKeys('/.*Title/', $fields) as $key) {
			
			if (strstr($fields[$key], '.') !== false) {
				$parts = explode('.', $fields[$key]);
				
				if ($parts[1] == 'hasOne') {
					$title = $this->owner->getComponent($parts[0])->$parts[2];
				} else {
					$title = '';
				}
			} else {
				$title = $this->owner->getField($fields[$key]);
			}
		
			/**
			 * Extract & save meta description if necessary.
			 * */				
			$this->owner->$key = $title;
		}
		
		/**
		 * Set up images.
		 * */
		foreach($this->getValidKeys('/.*Image/', $fields) as $key) {
			
			$im = $fields[$key]['Image'];
			$cropped = $fields[$key]['Cropped'];
			
			if (strstr($im, '.') !== false) {
				$parts = explode('.', $im);
				
				if ($parts[1] == 'hasOne') {
					$image = $key . 'ID';
					
					if (count($parts) > 3) {
						$this->owner->$image = $this->owner->getComponent($parts[0])->getComponent($parts[2])->$parts[3]()->ID;
					} else {
						$this->owner->$image = $this->owner->getComponent($parts[0])->$parts[2]()->ID;
					}
				}
			} else {
				if ($im && $this->owner->getComponent($im)) {
					$image = $key . 'ID';
					$this->owner->$image = $this->owner->getComponent($im)->ID;	
				}
			}
			
			if (strstr($cropped, '.') !== false) {
				$parts = explode('.', $cropped);
				
				if ($parts[1] == 'hasOne') {
					$image = $key . 'CroppedID';
					
					if (count($parts) > 3) {
						$this->owner->$image = $this->owner->getComponent($parts[0])->getComponent($parts[2])->$parts[3]()->ID;
					} else {
						$this->owner->$image = $this->owner->getComponent($parts[0])->$parts[2]()->ID;
					}
				}
			} else {
				if ($cropped && $this->owner->getComponent($cropped)) {
					$image = $key . 'CroppedID';
					$this->owner->$image = $this->owner->getComponent($cropped)->ID;	
				}
			}
		}
	}
	
	
	/**
	 * Get the field from either the extension or the 
	 * implementing class. Assumes that the field exists on this extension.
	 * */
	private function getTheField($fieldName) {
		$thisField = get_class_vars(__CLASS__)[$fieldName];
		$ownerField = get_class_vars($this->owner->ClassName);
		
		if (array_key_exists($fieldName, $ownerField)) {
			return $ownerField[$fieldName];
		}
		
		return $thisField;
	}
	
	private function getValidKeys($pattern, $fields) {
		$matches = array();
		
		foreach($fields as $key => $value) {
			if (preg_match($pattern, $key)){
				array_push($matches, $key);
			}
		}
		
		return $matches;
	}
}
