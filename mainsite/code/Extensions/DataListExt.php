<?php

class DataListExt extends Extension {
	public function format($map = null) {
        $lst = $this->owner;
        $formated = array();
        foreach ($lst as $item) {
            $formated[] = $item->format($map);
        }
        return $formated;
	}
}