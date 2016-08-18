<?php

global $project;
$project = 'mainsite';

global $database;
$database = SS_DATABASE_NAME;
 
// Use _ss_environment.php file for configuration
require_once("conf/ConfigureFromEnv.php");

GD::set_default_quality(90);
Image::set_backend("OptimisedGDBackend");
// ImagickBackend::set_default_quality(90);
// Image::set_backend("ImagickBackend");


Requirements::set_write_js_to_body(false);

if (Director::isLive()) {
	SS_Log::add_writer(new SS_LogEmailWriter('administration@saltedherring.com'), SS_Log::ERR);
}
