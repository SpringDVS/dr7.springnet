<?php


function springnet_keyring_view($msg = '', $type = 'status') {
	if($msg != '') {
		drupal_set_message($msg, $type);
	}
	require_once SPRINGNET_DIR.'/plugin/models/KeyringModel.php';
	$kr = new KeyringModel();
	$page = filter_input(INPUT_GET, "page");
	$page = !$page ? 1 : $page < 1 ? 1 : $page;
	$limit = 10;
	$ptotal = ceil($kr->getCertificateCount() / $limit);
	if($page > $ptotal){ $page = $ptotal; }
	
	$keys =  $kr->getUidList($page, $limit);
	$markup = "<table><tr> <th>Name</th> <th>Email</th> <th>Key ID</th></tr>";
	foreach($keys as $name => $detail) {
		$markup .= "<tr> <td><a href=\"/springnet/keyring/view/$detail->keyid\">$name</a></td> <td>{$detail->uidemail}</td> <td>{$detail->keyid}</td>";
	}
	$markup .= "</table>";
	$markup .= $page > 1 ? '<a href="/springnet/keyring/?page='.($page-1).'"><strong>&lt;</strong></a>' : "<strong>&lt;</strong>";
	$markup .= "&nbsp;&nbsp;$page / $ptotal&nbsp;&nbsp;";
	$markup .= $page < $ptotal ? '<a href="/springnet/keyring/?page='.($page+1).'"><strong>&gt;</strong></a>' : "<strong>&gt;</strong>";

	
	$form['springnet_keyring_overview'] = array(
			'#markup' => $markup 
	);
	
	return $markup;
}

function springnet_keyring_import_view($msg, $type, $key) {
	if($msg != '') {
		drupal_set_message($msg, $type);
	}

	drupal_add_css(drupal_get_path('module', 'springnet').'/res/css/module_admin.css');
	$markup = '<form method="post"><textarea name="key" class="springnet-key-display" rows="15" cols="65" id="springnet-key-import" style="clear: both;">'.$key.'</textarea><br>';
	$markup .= '<input type="submit" value="Import Certificate" class="form-submit"><form><br>';

	$markup .= '<form method="post"><input name="uri" type="text" placeholder="Spring URI">';
	$markup .= '<input type="submit" value="Pull" class="form-submit"></form>';
	return $markup;
}


function springnet_keyring_cert_view($keyid, $req = '') {
	require_once SPRINGNET_DIR.'/plugin/models/KeyringModel.php';
	require_once SPRINGNET_DIR.'/plugin/models/LocalNodeModel.php';
	require_once SPRINGNET_DIR.'/plugin/models/GatewayHandler.php';
	$node = new LocalNodeModel();
	$kr = new KeyringModel();
	$key = $kr->getResolvedCertificate($keyid);


	if($req == 'pullreq' ) {
		$targetUri = "spring://{$key['uidname']}";
		try {
			$uri = $node->uri();
			$msg = \SpringDvs\Message::fromStr("service $targetUri/cert/pullreq/?source=$uri");
		} catch(\Exception $e) {
			drupal_set_message("Error constructing request", 'error');
		}
		$response = GatewayHandler::requestUriFirstResponse($targetUri, $msg);
		try {
			$obj = json_decode($response->getContentResponse()->getServiceText()->get(), true);
			$r = reset($obj);
			switch($r['result']) {
				case 'ok':
					drupal_set_message("Made pull request");
					break;
				case 'queued':
					drupal_set_message("Request already queued at node");
					break;
				case 'error':
					drupal_set_message("Error occurred at other node", 'error');
					break;
				default:
					drupal_set_message("Unexpected response result `{$obj->result}`", 'error');
					break;
			}
		} catch(\Exception $e) {
			drupal_set_message("Unexpected response from other node", 'error');
		}
		
	}
	
	
	
	
	
	drupal_add_css(drupal_get_path('module', 'springnet').'/res/css/module_admin.css');
	$markup  = "";

	$markup .= "<h2>{$key['uidname']}</h2>";
	
	$markup .= $key['uidemail'];
	$markup .= "<div style=\"margin-top: 15px;\"><strong>Signatures</strong><ul>";
	foreach($key['sigs'] as $sig) {
		$markup .= "<li><span style=\"font-family: monospace;\">{$sig['keyid']}</span>&nbsp;&nbsp;({$sig['name']})</li>";
	}
	
	$markup .= '</ul>';
	if(!$key['signed']) {
		$markup .= "<a href=\"/springnet/keyring/unlock/sign/{$key['keyid']}\">Sign Key</a>";
	} else {
		$markup .= "<a href=\"/springnet/keyring/view/{$key['keyid']}/pullreq\">Make Pull Request</a>";
	}
	$markup .= '</div><br><textarea class="springnet-key-display" rows="15" cols="65" id="springnet-key-import">'.$key['armor'].'</textarea><br>';
	
	$markup .= '<a href="/springnet/keyring/delete/'.$keyid.'">Remove Key</a>';
	return $markup;
}

function springnet_keyring_cert_delete($keyid) {
	require_once SPRINGNET_DIR.'/plugin/models/KeyringModel.php';
	
	$kr = new KeyringModel();
	if($kr->removeCertificate($keyid)) {
		$msg = "Successfully removed certificate <em>$keyid</em>";
		drupal_set_message($msg);
		
	} else {
		$msg = "Error removing <em>$keyid</em>";
		drupal_set_message($msg, 'error');
	}
	
	return '<a href="/springnet/keyring/">Back to Keyring</a>';
}

function springnet_keyring_unlock($reason, $msg = '',$type = 'status') {
	if($msg != '') {
		drupal_set_message($msg, $type);
	}

	$markup = "<div style=\"font-size: 1.2em\">Reason for unlocking: <strong>{$reason}<strong></div>";
	$markup .= '<form method="post">';
	$markup .= '<input type="password" name="passphrase"><br>';
	$markup .= '<input type="submit" class="form-submit" value="Unlock Key">';
	$markup .= '</form>';

	return $markup;
}

function springnet_keyring_pullreq($requests) {
	
	$markup = '<table class=""><tbody><tr><th>From</th><th></th></tr>';
	
	if(empty($requests)) {
		$markup .= '<tr><td colspan="2">No pull requests</td></tr>';
	} else {
		foreach($requests as $row) {
		$markup .= '<tr><td>'. $row->repo_data . '</td>'
				.'<td style="text-align: right">'
				.'<a href="/springnet/keyring/pullreq/accept/'.$row->repo_id.'" class="page-title-action" >Accept</a> | '
				.'<a href="/springnet/keyring/pullreq/ignore/'.$row->repo_id.'" class="page-title-action" >Ignore</a>'
				.'</td></tr>';
		}
	}
		
	
	$markup .= '</tbody></table>';
	return $markup;		
}