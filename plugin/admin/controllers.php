<?php

function springnet_config_certificate_form_validate($form, &$form_state) {

	if(isset($form_state['values']['springnet_cert_uidemail'])) {
		// We're using the generation form
		$pass = $form_state['values']['springnet_cert_pass'];
		$val = $form_state['values']['springnet_cert_pass_val'];
		if(!$pass || $pass != $val) {
			form_set_error('springnet_cert_pass', t('Passphrases do not match'));
		}
	} elseif(isset($form_state['values']['springnet_node_name_removal'])
	&& $form_state['values']['springnet_key_removal_check'] == 1) {
		// we're using the deletion form
		$node = $form_state['values']['springnet_node_name_removal'];
		if($node != variable_get('springnet_node_springname')) {
			form_set_error('springnet_node_name_removal',
							t('Supplied springname does not match with node springname'));
		}
	}
	return;
	
	
}
function springnet_config_certificate_form_submit($form, &$form_state) {
	require_once SPRINGNET_DIR.'/plugin/models/PkServiceModel.php';
	$pks = new PkServiceModel();
	
	if(isset($form_state['values']['springnet_cert_uidemail'])) {
		$email = $form_state['values']['springnet_cert_uidemail'];
		$passphrase = $form_state['values']['springnet_cert_pass'];
		$name = variable_get('springnet_node_uri', '');
		$key = $pks->generateKeypair($name, $email, $passphrase);
		if(!$key || $key['public'] == "" || $key['private'] == "") {
			drupal_set_message('Recieved malformed keys. If this continues please contact spring@care-connections.org', 'error');
		}
		$private = $key['private'];
		$public = $key['public'];
		
		$cert = $pks->import($public);
		if(!$cert) {
			drupal_set_message('Error importing certificate. If this continues please contact spring@care-connections.org', 'error');
		}
		$pks->keyring()->setNodeCertificate($cert['keyid'], $cert['email'], $cert['sigs'], $cert['armor']);
		$pks->keyring()->setNodePrivate($private);
		drupal_set_message('Successfully generated keypair and certificate');
	} elseif(isset($form_state['values']['springnet_node_name_removal'])
	&& $form_state['values']['springnet_key_removal_check'] == 1){
		$pks->keyring()->resetNodeKeys();
		drupal_set_message('Successfully reset node keys');
	} else {
		variable_set('springnet_cert_pullreq', $form_state['values']['springnet_cert_pullreq']);
	}
}

function snadmin_process_import($key, &$msg, &$type) {
	require_once SPRINGNET_DIR.'/plugin/models/PkServiceModel.php';
	$pk = new PkServiceModel();

	$cert = $pk->import($key);
	if(!$cert || $cert['name'] == '') {
		$msg = 'Error importing -- received malformed certificate. If this continues, please contact spring@care-connections.org';
		$type = 'error';
		return '';
	}
	
	if( ($subject = $pk->keyring()->getCertificate($cert['keyid']))) {
		$cert = $pk->import($key, $subject);
	}
	
	if($pk->keyring()->setCertificate(
				$cert['keyid'],
				$cert['name'],
				$cert['email'],
				$cert['sigs'],
				$cert['armor'])) {
		$status = 'success';
		$name = $cert['name'];
		$type = 'status';
		$msg = "Successfully imported certificate for <em>$name</em>";
	} else {
		$type = 'error';
		$msg = 'Error importing -- received malformed certificate. If this continues, please contact spring@care-connections.org';
		return '';
	}
}

function snadmin_process_key_request_uri($uri, &$key, &$msg, &$type) {
	require_once SPRINGNET_DIR.'/plugin/models/GatewayHandler.php';
	if(substr($uri, 0,10) != "spring://") {
		$uri = "spring://$uri";
	}
	$out = \SpringDvs\Message::fromStr("service $uri/cert/key");
	$response = GatewayHandler::requestUriFirstResponse($uri, $out);
	
	if(!$response) {
		$msg = 'Failed to perform request';
		$type = 'error';
		return;
	}
	
	$json = '';
	try {
	
		$json = $response->getContentResponse()->getServiceText()->get();
		$tmp = json_decode($json, true);
		$o = array_shift($tmp);
		if($o['key'] == 'error') {
			$type = 'error';
			$msg = 'Certificate request resulted in an error';
			return;
	
		} else {
			$key = $o['key'];
		}
	
	} catch(\Exception $e) {
		$type = 'error';
		$msg = 'Unexpected response from request of certificate';
		return;
	}
}

function springnet_keyring_import_controller() {
	$msg = '';
	$type = 'status';
	$key = '';
	if( ($key = filter_input(INPUT_POST, 'key'))) {
		snadmin_process_import($key, $msg, $status);
	} elseif ( ($uri = filter_input(INPUT_POST, 'uri'))) {
		snadmin_process_key_request_uri($uri, $key, $msg, $type);
	}
	
	return springnet_keyring_import_view($msg, $type, $key);
}

function snadmin_process_sign($keyid, $passphrase, &$msg, &$type) {
	require_once SPRINGNET_DIR.'/plugin/models/PkServiceModel.php';
	$pk = new PkServiceModel();
	$pre = $pk->keyring()->getCertificate($keyid);
	if(!$pre) {
		$msg = "Error -- key does not exist";
		$type = 'error';
		return;
	}
	
	$signed = $pk->sign($pre['armor'], $pk->keyring()->getNodePrivateKey(), $passphrase);
	//var_dump($signed);
	if(!isset($signed['public']) || $signed['public'] == '') {
		$msg = "Error signing -- If this continues, please contact spring@care-connections.org";
		$type = 'error';
		return;
	}
	
	$cert = $pk->import($signed['public']);
	if(!$pk->keyring()->setCertificate(
			$cert['keyid'],
			$cert['name'],
			$cert['email'],
			$cert['sigs'],
			$cert['armor'])) {
		$type = 'error';
		$msg = 'Error importing signed certificate';
		return;
	}
	$msg = "Performed signing on <em>{$cert['name']}</em>. <a href=\"/springnet/keyring/view/$keyid\">Go Back</a> to key";
}

function springnet_keyring_unlock_controller($reason, $optional = '') {
	// ToDo: Check for the `springnet keyring unlock` permission
	$msg = '';
	$type = 'status';
	if( ($passphrase = filter_input(INPUT_POST, 'passphrase'))) {
		switch($reason) {
			case 'sign':
				snadmin_process_sign($optional, $passphrase, $msg, $type);
				break;
		}
	}
	return springnet_keyring_unlock($reason, $msg, $type);
}

function springnet_permission() {
	return array(
		'springnet keyring unlock' => array(
				'title' => t("Unlock keyring"),
				'description' => t("Unlock and use the private key")
		),

		'springnet keyring manage' => array(
				'title' => t("Manage certificates"),
				'description' => t("View, import and delete certificates")
		),

		'springnet service manage' => array(
				'title' => t("Manage SpringNet Services"),
				'description' => t("The basic permission to use the services running on the node")
		),
	);
}



/**
 * Ajax handler for resolving the geonetwork HTTP details
 *
 * @param string $geonet The name of the network to resolve
 */
function springnet_get_geonetwork_ajax($geonet) {
	$response = drupal_http_request("https://resolve.spring-dvs.org/geosubs/$geonet");
	echo $response->data;
}

/**
 * Ajax handler for registering node with network
 */
function springnet_register_node_ajax() {
	require_once SPRINGNET_DIR.'/plugin/models/LocalNodeModel.php';
	$node = new LocalNodeModel();
	$status = $node->register();
}

/**
 * Ajax handler for enabling node on network
 */
function springnet_enable_node_ajax() {
	require_once SPRINGNET_DIR.'/plugin/models/LocalNodeModel.php';
	$node = new LocalNodeModel();
	$status = $node->enable();
}

/**
 * Ajax handler for disabling node on network
 */
function springnet_disable_node_ajax() {
	require_once SPRINGNET_DIR.'/plugin/models/LocalNodeModel.php';
	$node = new LocalNodeModel();
	
	$status = $node->disable();
}

function springnet_keyring_pullreq_controller($method = null, $reqid = null) {
	global $_snetrepo;
	
	if($method == 'accept') {
		require_once SPRINGNET_DIR.'/plugin/models/PkServiceModel.php';
		$service = new PkServiceModel();
		$keyring = $service->keyring();

		$data = $_snetrepo->getDatumFromId('cert_pullreq', $reqid);

		if($data) {
			$pulled = $keyring->performPull($data->repo_data);
			if(!$pulled) {
				$status = 'error';
				drupal_set_message('Something went wrong with the pull','error');
			}
			
			
			$handler = new NotificationHandler();
			
			
			$node_certificate = $keyring->getNodePublicKey();
			$response = $service->import($pulled, $node_certificate);

			if(!$response) {
				drupal_set_message('Failed to perform import service request','error');
			} elseif($keyring->setNodeCertificate(
					$response['keyid'],
					$response['email'],
					$response['sigs'],
					$response['armor'])) {
						drupal_set_message('Performed pull from ' . $data->repo_data);
						$_snetrepo->removeDataWithId('cert_pullreq', $reqid);
						$handler->resolveNotificationId($data->repo_notif);
			} else {
				if($keyring->hasCertificate()) {
					drupal_set_message('No changes occurred -- certificate already up to date','warning');
				} else {
					drupal_set_message('An error occurred updating certificate','error');
				}
				$handler->resolveNotificationId($data->repo_notif);
				$_snetrepo->removeDataWithId('cert_pullreq', $reqid);
			}
		} else {
			drupal_set_message('An error occured accepting pull request -- does request still exist?', 'error');
		}
		
	} else if($method == 'ignore') {
		
		$data = $_snetrepo->getDatumFromId('cert_pullreq', $reqid);
		if($data && $_snetrepo->removeDataWithId('cert_pullreq', $reqid)) {
			$handler = new NotificationHandler();
			$handler->resolveNotificationId($data->repo_notif);
			drupal_set_message("Ignored pull request from $data->repo_data");
		} else {
			drupal_set_message('An error occured ignoring pull request -- does request still exist?', 'error');
		}
	}
	
	$requests = $_snetrepo->getDataFromTag('cert_pullreq');
	$requests = empty($requests) ? array() : $requests;
	return springnet_keyring_pullreq($requests);
}







