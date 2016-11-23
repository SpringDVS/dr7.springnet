<?php
class ModuleHandler {
	
	public static function listModules($resource = '') {
		$dirs = array_filter(glob(SPRINGNET_DIR.'/modules/*'), 'is_dir');
		$mods = array();
		foreach($dirs as $dir) {
			$path = $resource == ''
						? ''
						: $dir.'/'.$resource;
			
			if($resource != '' && !file_exists($path)) continue;
			$mods[] = basename($dir);
						
						
		}
		return $mods;
	}
	
	public static function listPaths($resource = '') {
		$dirs = array_filter(glob(SPRINGNET_DIR.'/modules/*'), 'is_dir');
		$mods = array();
		foreach($dirs as $dir) {
			$path = $resource == ''
					? ''
					: $dir.'/'.$resource;
						
					if($resource != '' && !file_exists($path)) continue;
	
					
					$mod = basename($dir);
					$mods[$mod] = $path;
		}
		return $mods;
	}
	
	public static function resourcePath($module, $resource) {
		$path = SPRINGNET_DIR.'/modules/'.$module.'/'.$resource;
		if(!file_exists($path)) return false;
		return $path;
	}
}