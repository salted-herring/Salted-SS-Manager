<?php
use Ntb\RestAPI\BaseRestController as BaseRestController;
use SaltedHerring\Debugger as Debugger;
/**
 * @file DeploymentPanel.php
 *
 * Controller to present the data from forms.
 * */
class PanelAPI extends BaseRestController {
	private static $allowed_actions = array (
        'post'		=>	"->isAuthenticated",
        'put'		=>	"->isAuthenticated",
        'get'		=>	true
    );
}