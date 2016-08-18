<?php 
class CustomSiteConfig extends DataExtension { 
	
	public static $db = array(
		'GoogleSiteVerificationCode'	=>	'Varchar(128)',
		'GoogleAnalyticsCode'			=>	'Varchar(20)',
		'SiteVersion'					=>	'Varchar(10)',
		'GoogleCustomCode'				=>	'HTMLText',
		'ContactNumber'					=>	'Varchar(32)',
		'Email'							=>	'Varchar(254)',
		'Address'						=>	'Text'
	);
	
	public function updateCMSFields(FieldList $fields) {	
		$fields->addFieldsToTab(
			'Root.CompanyInfo',
			array(
				TextField::create('ContactNumber'),
				EmailField::create('Email'),
				TextareaField::create('Address')
			)
		);
		
		$fields->addFieldToTab("Root.Google", new TextField('GoogleSiteVerificationCode', 'Google Site Verification Code'));
		$fields->addFieldToTab("Root.Google", new TextField('GoogleAnalyticsCode', 'Google Analytics Code'));
		$fields->addFieldToTab("Root.Google", new TextareaField('GoogleCustomCode', 'Custom Google Code'));
		
		$fields->addFieldToTab('Root.Main', new TextField('SiteVersion', 'Site Version'));
	}
	
}
