<?php use SaltedHerring\Debugger as Debugger;

class DeploymentPanel extends Page_Controller {
	protected static $allowed_actions = array(
		''      =>      'index'
	);

	public function init() {
		parent::init();
		Requirements::combine_files(
	        'reactful.js',
	        array(
	        	'themes/default/js/components/jquery/dist/jquery.min.js',
	        	'themes/default/js/components/react/react.js',
	            'themes/default/js/components/react/react-dom.js',
	            'themes/default/js/components/salted-js/dist/salted-js.min.js',
	            'https://cdnjs.cloudflare.com/ajax/libs/babel-core/5.8.24/browser.min.js'
	        )
        );
	}

	public function index($request) {
		return $this->renderWith('Reactful');
	}

	public function Title() {
		return 'Deployment Panel';
	}

	public function getSites() {
		$sites = Site::get();
		$js = 'var sites = [';
		$stringified = array();
		foreach ($sites as $site) {
			$stringified[] = json_encode($site->format());
		}
		$js .= implode(',', $stringified);
		$js .= '];';

		return $js;		
	}

	public function getEnvironments() {
		$environments = Environment::get();
		$js = 'var environments = [';
		$stringified = array();
		foreach ($environments as $environment) {
			$stringified[] = json_encode($environment->format());
		}
		$js .= implode(',', $stringified);
		$js .= '];';

		return $js;
	}
}