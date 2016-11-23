<?php
class NetworkService {
	private $springname;
	private $resources;
	public function __construct($springname, array $resources) {
		$this->springname = $springname;
		$this->resources = $resources;
	}

	public function run($uri) {
		$resource_path = $uri->res();
		$query = array();
		
		parse_str($uri->query(), $query);
		
		if(!isset($resource_path[0])) {
			return "104";
		}
		$mod = $resource_path[0];
		
		if(reset($uri->route()) != $this->springname) {
			return "103"; // Network error -- it's not the right node
		}
		
		if(!isset($this->resources[$mod])) {
			return "122"; // UnsupportedService -- we don't have the service
		}
		
		$info_path = dirname($this->resources[$mod]).'/info.php';
		if(!file_exists($info_path)) return "122";
		$info = include $info_path;
		$encoding = "json";
		if(isset($info['encoding'])) {
			$encoding = $info['encoding'];
		}

		
		$response = include $this->resources[$mod];
		
		switch($encoding) {
			case 'json':
				$text = "service/text $response";
				$len = strlen($text);
				return "200 $len $text";
			default:
				return "122";
		}
	}
}