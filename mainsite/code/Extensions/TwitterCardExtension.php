<?php

/**
 * @file TwitteCarExtension
 *
 * Extension to provide support for twitter cards.
 * */
class TwitterCardExtension extends DataExtension {
	private static $db = array(
		'TitterCard'			=> 'Enum("summary,summary_large_image")',
		'TwitterTitle' 			=> 'Varchar(255)',
		'TwitterDescription'	=> 'Varchar(255)',
	);
	
	private static $has_one =  array(
		'TwitterImage' 			=> 'Image',
		'TwitterImageCropped'	=> 'Image'
	);
	
	public function updateCMSFields(FieldList $fields) {
		$baseClass = ClassInfo::baseDataClass($this->owner->class);
		
		$title = new TextField('TwitterTitle', 'Title');

		// If extending a page, disable access to the title - the page title will suffice.
		if ($baseClass == 'SiteTree') {
			$title = $title->performReadonlyTransformation();
		}
		
		$twitter = ToggleCompositeField::create(
			'Twitter',
			new LabelField('Twitter', 'Twitter Card Tags'),
			array(
				new DropdownField('TitterCard', 'Card Type', 
					singleton($this->owner->ClassName)->dbObject('TitterCard')->enumValues()
				),
				$title,
				new TextareaField('TwitterDescription', 'Description'),
				$TwitterImage = new UploadField('TwitterImage', 'Image'),
				$TwitterCropper = new CropperField\CropperField(
					'TwitterImageCropped',
					'Cropped Twitter Card Image',
					new CropperField\Adapter\UploadField(
						$TwitterImage
					),
					array(
						'aspect_ratio' 			=> 440/220,
						'generated_max_width'	=> 880,
						'generated_max_height'	=> 440
					)
				)
			)
		);
		$TwitterImage->setDescription('Image must be at least 880px x 440px.');
		$fields->addFieldToTab('Root.Social', $twitter);
	}
}