<?php
class DeploymentAdmin extends ModelAdmin {
   private static $managed_models = array('Site', 'HtAccess', 'Server');
   static $url_segment = 'deploy-management';
   static $menu_title = 'Deploy Management';

   public function getEditForm($id = null, $fields = null){
		$form = parent::getEditForm($id, $fields);

		$gridField = $form->Fields()->fieldByName($this->sanitiseClassName($this->modelClass));

        $gridField->getConfig()
        	->removeComponentsByType('GridFieldPaginator')
        	->addComponents(new GridFieldPaginatorWithShowAll());

		return $form;
	}
}
