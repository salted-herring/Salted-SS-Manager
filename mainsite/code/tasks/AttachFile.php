<?php
/*
 * @file PurgeExpired.php
 *
 * delete expired articles
 */
use SaltedHerring\Debugger as Debugger;
class AttachFile extends BuildTask {
	protected $title = 'File Attacher';
	protected $description = 'Attach file to environment';

	protected $enabled = true;

	public function run($request) {
		$echo = "missing parameter(s)";
		
		if ($getVars = $request->getVar('args')) {

			$environmentID = $getVars[0];
			$filename = $getVars[1];

			if (!empty($environmentID) && !empty($filename)) {
				$environment = Environment::get()->byID($environmentID);
				$filename = $environment->AssetFolder()->getFullPath() . $filename;
				$file = DataObject::get_one('File', array('Filename' => $filename));
				if (empty($file)) {
					$file = new File();
					$file->Filename = $filename;
					$file->ParentID = $environment->AssetFolderID;
					$file->write();
					$environment->AssetsAndDatabase()->add($file->ID);
					$echo = 'File attached.';
				} else {
					$echo = 'File already attached.';
				}
			}
		}

		echo $echo . "\n";
	}
}