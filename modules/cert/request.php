<?php
if(!defined('SPRING_IF')) { die(); }

include SPRINGNET_DIR.'/modules/cert/pullreq.php';
include_once SPRINGNET_DIR.'/plugin/models/KeyringModel.php';
$kr = new KeyringModel();

$local = new LocalNodeModel();

$content = null;

if(isset($resource_path[1]) && !empty($resource_path[1])) {
	switch($resource_path[1]) {
		case 'key':
			$key = ($key = $kr->getNodePublicKey())
						? $key : "error";
						
			$content = array('key' => $key);
			break;
			
		case 'pull':
			$id = isset($resource_path[2])
					? $resource_path[2]
					: '0';
			
			$key = ($key = $kr->getKey($id))
					? $key : "error";
			$content = array('key' => $key);
			break;
		case 'pullreq':
			$content = springnet_netserv_cert_pullreq_handler($resource_path, $query);
		default:
			array('error' => $key);
	}
} else {
	$content = ($cert = $kr->getNodeCertificate()) ?
		array(
			'certificate' => array(
					'name' => $cert['uidname'],
					'email' => $cert['uidemail'],
					'keyid' => $cert['keyid'],
					'sigs' => explode(",", $cert['sigs']),
					'armor' => $cert['armor']
			),
		) : "error";
}

return json_encode(array(
		$local->uri() => $content
));