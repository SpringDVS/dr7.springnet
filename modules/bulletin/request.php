<?php
if(!defined('SPRING_IF')) { die(); }


include_once 'BulletinModel.php';
$bm = new BulletinModel();
$local = new LocalNodeModel();


if(isset($resource_path[1]) && !empty($resource_path[1])) {
	return json_encode(array(
			$local->uri() => $bm->withUid($resource_path[1])
			));
}

$categories = isset($query['categories']) ? explode(",", $query['categories']) : array();
$tags = isset($query['tags']) ? explode(",", $query['tags']) : array();
$limit = isset($query['limit']) ?  $query['limit'] : 5;


return json_encode(array(
		$local->uri() => $bm->withFilters($categories, $tags, $limit),
));