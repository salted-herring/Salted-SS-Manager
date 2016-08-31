<?php

class FileExt extends DataExtension {
	protected static $has_one = array(
		'BelongsTo'		=>	'Environment'
	);
}