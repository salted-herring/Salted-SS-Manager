<?php

class ApiObject extends DataObject implements ApiInterface {
	public function format($map = null) {
        if (empty($map)) {
            $data = array(
                'id'	=>	$this->ID
            );
        } else {
            $data = array();
            foreach ($map as $key => $value) {
                if ($this->hasField($value)) {
                        $data[$key] = $this->$value;
                } else if (method_exists($this, $value)) {
                        $data[$key] = $this->$value();
                }
            }
        }

        return $data;
    }
}