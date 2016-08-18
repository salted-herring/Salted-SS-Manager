<?php

class CacheExt extends DataExtension {
	
	public function read_cache($factory, $cache_key) {
		$cache			=	SS_Cache::factory($factory);
		$cached			=	$cache->load($cache_key);
		
		if (!empty($cached)) {
			$cached		=	unserialize($cached);
			return $cached;
		}
		
		return false;
	}
	
	public function delete_cache($factory, $cache_key) {
		$cache			=	SS_Cache::factory($factory);
		$cached			=	$cache->load($cache_key);
		if (!empty($cached)) {
			$cache->remove($cache_key);
		}
	}
	
	public function save_cache($factory, $cache_key, $result) {
		$cache			=	SS_Cache::factory($factory);
		$cache_object	=	serialize($result);
		$cache->save($cache_object, $cache_key);
	}
}