<?php

class ModelAdminExtension extends DataExtension {

	public function getEditForm($id = null, $fields = null) {
		$form = parent::getEditForm($id, $fields);
		$gridField = $form->Fields()->fieldByName($this->sanitiseClassName($this->modelClass));
		$gridField->getConfig()->removeComponentsByType('GridFieldPaginator');
		$gridField->getConfig()->addComponent(new GridFieldPaginatorWithShowAll());
		
		return $form;
	}
}