<?php
function springnet_netserv_cert_pullreq_handler(&$resource_path, &$query) {
	if(!isset($query['source'])) {
		return array('request' => 'error');
	}
	$notify = variable_get('springnet_cert_pullreq',true);
	
	if($notify) {
		return springnet_nscert_notify_pull($query['source']);
	} else {
		return springnet_nscert_direct_pull($query['source']);
	}
	
}

function springnet_nscert_direct_pull($source ) {
	require_once SPRINGNET_DIR.'/plugin/models/PkServiceModel.php';
		
	$keyring = new KeyringModel();
	
	$pulled = $keyring->performPull($source);
	if(!$pulled) {
		return array('result' => 'error');
	}
		
	$service = new PkServiceModel();
	
		
	$node_certificate = $keyring->getNodePublicKey();
	$response = $service->import($pulled, $node_certificate);
	if(!$response || (isset($response['name']) && $response['name'] == "")) {
		return array('request' => 'error');
	} elseif($keyring->setNodeCertificate(
			$response['keyid'],
			$response['email'],
			$response['sigs'],
			$response['armor'])) {
				return array('result' => 'ok');
	} else {
		return array('result' => 'error');
	}
}

function springnet_nscert_notify_pull($source) {
	global $_snetrepo;
	if($_snetrepo->dataExists('cert_pullreq', $source)) {
		return array('result' => 'queued');
	}
	$handler = new NotificationHandler();
	$notif = $handler->addNotification('Certificate Pull Request',
						'/springnet/keyring/pullreq',
						'Certificates',
						"$source is requesting an update to your
						public certificate");
		
		
	$_snetrepo->addData('cert_pullreq', $source, $notif);
		
	$handler->activateNotification($notif);
	return array('result' => 'ok');
}