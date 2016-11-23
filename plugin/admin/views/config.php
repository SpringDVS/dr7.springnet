<?php

/**
 * Page callback: Configure the node
 *
 * @see springnet_menu()
 */
function springnet_config_node_form($form, &$form_state) {
	require_once SPRINGNET_DIR.'/plugin/models/KeyringModel.php';
	require_once SPRINGNET_DIR.'/plugin/models/LocalNodeModel.php';
	$kr = new KeyringModel();
	$hasCert = $kr->hasCertificate();
	$hasUri = variable_get('springnet_node_uri', '') != ''
			? true : false;
	
	$hasToken = variable_get('springnet_geonet_token', '') != ''
			? true : false;
	
	$configOk = true;

	if(!$hasUri || !$hasToken) {
		drupal_set_message('Node does not have a valid network setup -- please use the ' 
						.'<em><a href="/admin/config/services/springnet/network">'
						. 'Network</a></em> tab to configure.','error');
		$configOk = false;
	}
	if(!$hasCert) {
		drupal_set_message('Node does not have a public certificate -- please use the' 
						.'<em><a href="/admin/config/services/springnet/certificate"> '
						.'Certificate</a></em> tab to generate one.','error');
		$configOk = false;
	}
	
	if(!$configOk) return;
	$node = new LocalNodeModel();
	
	$isEnabled = false;
	$isRegistered = $node->isRegistered();
	
	if($isRegistered) {
		if(!($isEnabled = $node->isEnabled())) {
			drupal_set_message('Node is <strong>offline</strong>', 'warning');
		}
	} else {
		drupal_set_message('Node is not yet registered on <em>'
				. variable_get('springnet_geonet_name') 
				. '</em> -- please press <strong>Register Node</strong> button'
				. ' to perform registration', 'error');
	}
	
	$form['#attached']['js'] = array(
			drupal_get_path('module', 'springnet').'/res/js/springnet_nodectl.js',
	);
	
	$form['#attached']['css'] = array(
			drupal_get_path('module', 'springnet').'/res/css/module_admin.css',
	);
	$form['springnet_node_status_table'] = array(
		'#type' => 'markup',
		'#markup' => '<table class="springnet-config-overview" style="width: auto;"><tr><th>Registration Status</th>'
					.'<td>' . ($isRegistered ? "Registered" : "Not Registered") . '</td></tr>'
					.'<tr><th>Node Status</th>'
					.'<td>' . ($isEnabled ? "Online" : "Offline") . '</td></tr></table>'
	);

	$controls = "";
	if($isRegistered) {
		if($isEnabled) {
			$controls = '<input id="sn-nodectl-disable" type="button" class="form-submit" value="Bring Offline">';
		} else {
			$controls = '<input  id="sn-nodectl-enable" type="button" class="form-submit" value="Bring Online">';
		}
		//$controls .= '<input type="button" class="form-submit" value="Register Node" disabled="disabled">';
	} else {
		//$controls = '<input type="button" class="form-submit" value="Bring Online" disabled="disabled">';
		$controls .= '<input id="sn-nodectl-reg" type="button" class="form-submit" value="Register Node">';
	}
	//springnet-loading-icon
	$controls .=  '<img id="springnet-loading-icon" style="display: none;" src="/'.
						drupal_get_path('module', 'springnet')
					.'/res/img/loading.gif">';
	
	$form['springnet_node_control_table'] = array(
			'#type' => 'markup',
			'#markup' => $controls
	);

	$form['springnet_node_info_table'] = array(
			'#type' => 'markup',
			'#markup' => '<table class="springnet-config-overview" style="width: auto;">'
			. '<tr><th>URI</th>'
			.'<td>' . variable_get('springnet_node_uri') . '</td></tr>'
			. '<tr><th>GeoNetwork</th>'
			.'<td>' . variable_get('springnet_geonet_name') . '</td></tr>'
			. '<tr><th>Primary Service</th>'
			.'<td>' . variable_get('springnet_geonet_hostname') . '</td></tr>'
			. '<tr><th>Node Service</th>'
			.'<td>' . variable_get('springnet_node_hostname') . '</td></tr>'
			.'</table>'
	);
	return $form;
}

/**
 * Page callback: Configure the network settings
 *
 * @see springnet_menu()
 */
function springnet_config_network_form($form, &$form_state) {

	$form['#attached']['js'] = array(
			drupal_get_path('module', 'springnet').'/res/js/springnet_config.js',
	);

	$form['springnet_network_config_node_settings_console'] = array(
			'#markup' => '<div style="display: none;" id="springnet-error-console" class="messages error"></div>'
	);
	$form['springnet_network_config_node_settings'] = array(
			'#type' => 'fieldset',
			'#title' => 'Local Node'
	);

	$form['springnet_network_config_node_settings']['springnet_node_springname'] = array(
			'#type' => 'textfield',
			'#title' => t('Springname'),
			'#default_value' => variable_get('springnet_node_springname', ''),
			'#size' => 32,
			'#maxlength' => 64,
			'#description' => t('The springname of this node'),
			'#required' => TRUE,
	);

	$form['springnet_network_config_node_settings']['springnet_node_hostname'] = array(
			'#type' => 'textfield',
			'#title' => t('Hostname'),
			'#default_value' => variable_get('springnet_node_hostname', $_SERVER['SERVER_NAME']),
			'#size' => 32,
			'#maxlength' => 64,
			'#description' => t('The hostname running the node'),
			'#required' => TRUE,
	);

	$form['springnet_network_config_network_settings'] = array(
			'#type' => 'fieldset',
			'#title' => 'Network'
	);

	$form['springnet_network_config_network_settings']['springnet_geonet_name'] = array(
			'#type' => 'textfield',
			'#title' => t('GeoNetwork'),
			'#default_value' => variable_get('springnet_geonet_name', ''),
			'#size' => 32,
			'#maxlength' => 64,
			'#description' => t('The regional network to connect to'),
			'#required' => TRUE,
	);
	$form['springnet_network_config_network_settings']['springnet_geonet_lookup'] = array(
			'#markup' => '<input type="button" class="form-submit" value="Lookup" id="edit-springnet-geonet-lookup">'
			.'<img id="springnet-loading-icon" style="display: none;" src="/'.
			drupal_get_path('module', 'springnet')
			.'/res/img/loading.gif">',
	);

	// Auto form URI
	$form['springnet_network_config_network_settings']['springnet_node_uri'] = array(
			'#type' => 'hidden',
			'#default_value' => variable_get('springnet_node_uri', ''),
			'#required' => TRUE,
			'#id' => 'edit-springnet-node-uri',
	);
	
	$form['springnet_network_config_network_settings']['springnet_node_uri_vis'] = array(
			'#markup' => '<div><strong>spring://<span id="edit-springnet-node-uri-vis">'.variable_get('springnet_node_uri', '').'</span></div>'
	);

	// Auto form Hostname
	$form['springnet_network_config_network_settings']['springnet_geonet_hostname'] = array(
			'#type' => 'hidden',
			'#default_value' => variable_get('springnet_geonet_hostname', ''),
			'#required' => TRUE,
			'#id' => 'edit-springnet-geonet-hostname',
	);
	
	$form['springnet_network_config_network_settings']['springnet_geonet_hostname_vis'] = array(
			'#type' => 'textfield',
			'#title' => t('Hostname'),
			'#default_value' => variable_get('springnet_geonet_hostname', ''),
			'#size' => 32,
			'#maxlength' => 64,
			'#required' => TRUE,
			'#disabled' => TRUE,
	);

	
	// Auto form Address
	$form['springnet_network_config_network_settings']['springnet_geonet_address'] = array(
			'#type' => 'hidden',
			'#default_value' => variable_get('springnet_geonet_address', ''),
			'#required' => TRUE,
			'#id' => 'edit-springnet-geonet-address',
	);
	
	$form['springnet_network_config_network_settings']['springnet_geonet_address_vis'] = array(
			'#type' => 'textfield',
			'#title' => t('Address'),
			'#default_value' => variable_get('springnet_geonet_address', ''),
			'#size' => 32,
			'#maxlength' => 64,
			'#required' => TRUE,
			'#disabled' => TRUE,
	);
	

	// Auto form Resource
	$form['springnet_network_config_network_settings']['springnet_geonet_resource'] = array(
			'#type' => 'hidden',
			'#default_value' => variable_get('springnet_geonet_resource', ''),
			'#required' => TRUE,
			'#id' => 'edit-springnet-geonet-resource',
	);
	
	
	$form['springnet_network_config_network_settings']['springnet_geonet_resource_vis'] = array(
			'#type' => 'textfield',
			'#title' => t('Service Resource'),
			'#default_value' => variable_get('springnet_geonet_resource', ''),
			'#size' => 32,
			'#maxlength' => 64,
			'#required' => TRUE,
			'#disabled' => TRUE,
	);
	
	// Form token
	$form['springnet_network_config_network_settings']['springnet_geonet_token'] = array(
			'#type' => 'textfield',
			'#title' => t('Validation Token'),
			'#default_value' => variable_get('springnet_geonet_token', ''),
			'#size' => 32,
			'#maxlength' => 32,
			'#required' => TRUE,
	);


	return system_settings_form($form);
}

function springnet_config_certificate_form($form, &$form_state) {
	require_once SPRINGNET_DIR.'/plugin/models/PkServiceModel.php';
	$pk = new PkServiceModel();
	if(!$pk->keyring()->hasCertificate()) {
		return snadmin_config_certificate_form_generate($form, $form_state); 
	}
	
	return snadmin_config_certificate_form_view($form, $form_state, $pk);
}

function snadmin_config_certificate_form_generate($form, &$form_state) {
	$form['#submit'][] = 'springnet_config_certificate_form_submit';
	$form['springnet_certificate_config_creds'] = array(
			'#type' => 'fieldset',
			'#title' => 'Local Node'
	);
	$form['springnet_certificate_config_creds']['springnet_cert_uidemail'] = array(
			'#type' => 'textfield',
			'#title' => t('Contact Email'),
			'#description' => t('Contact email to show on certificate'),
			'#default_value' => '',
			'#size' => 32,
			'#maxlength' => 32,
			'#required' => TRUE,
	);
	
	$form['springnet_certificate_config_creds']['springnet_cert_pass'] = array(
			'#type' => 'password',
			'#title' => t('Passphrase'),
			'#description' => t('Used to lock up private key'),
			'#default_value' => '',
			'#size' => 32,
			'#maxlength' => 32,
			'#required' => TRUE,
	);
	
	$form['springnet_certificate_config_creds']['springnet_cert_pass_val'] = array(
			'#type' => 'password',
			'#title' => t('Verify Passphrase'),
			'#description' => t('Avoids mispelling passphrase'),
			'#default_value' => '',
			'#size' => 32,
			'#maxlength' => 32,
			'#required' => TRUE,
	);
	$form['springnet_certificate_config_creds']['submit'] = array(
			'#type' => 'submit',
			'#value' => 'Submit',
	);
	return $form;
}

function snadmin_config_certificate_form_view($form, &$form_state, PkServiceModel &$pk) {
	$cert = $pk->keyring()->getNodeCertificate();
	$private = $pk->keyring()->getNodePrivateKey();
	
	$form['#attached']['css'] = array(
			drupal_get_path('module', 'springnet').'/res/css/module_admin.css',
	);
	
	$form['springnet_cert_pullreq'] = array(
		'#type' => 'checkbox',
		'#title' => t('Notify certificate pull requests'),
		'#description' => t('Create a notification when a node makes a pull request'),
		'#default_value' => variable_get('springnet_cert_pullreq',true),
	);
	
	$form['submit_cert'] = array(
			'#type' => 'submit',
			'#value' => 'Save settings',
	);
	$form['springnet_certificate_config_view_public_set'] = array(
			'#type' => 'fieldset',
			'#title' => 'Public Key'
	);
	$form['springnet_certificate_config_view_public_set']['springnet_certificate_config_view_public'] = array(
			'#markup' => '<textarea class="springnet-key-display" cols="65" rows="15">'.$cert['armor'].'</textarea>',
	);
	
	
	$form['springnet_certificate_config_view_private_set'] = array(
			'#type' => 'fieldset',
			'#title' => 'Private Key'
	);
	$form['springnet_certificate_config_view_private_set']['springnet_certificate_config_view_private'] = array(
			'#markup' => '<textarea class="springnet-key-display" cols="65" rows="15">'.$private.'</textarea>',
	);
	
	
	$form['springnet_certificate_config_view_reset_header'] = array(
			'#markup' => '<h3 style="margin-top: 50px;">Reset Node Keys</h3><div class="springnet-risky-message">This is a risky, irreversible action</div>',
	);
	$form['springnet_node_name_removal'] = array(
			'#type' => 'textfield',
			'#title' => t('Node Springname'),
			'#description' => t('Enter this node\'s Springname to verify key reset'),
			'#default_value' => '',
			'#size' => 32,
			'#maxlength' => 64,
			'#required' => false,
	);
	$form['springnet_key_removal_check'] = array(
			'#type' => 'checkbox',
			'#title' => t('Yes, reset the keys'),
			'#default_value' => false,
	);
	$form['submit'] = array(
			'#type' => 'submit',
			'#value' => 'Reset Node Keys',
	);
			
	return $form;
}

function springnet_config_general_form($form, &$form_state) {

	$form['#attached']['js'] = array(
			drupal_get_path('module', 'springnet').'/res/js/springnet_config.js',
	);


	$form['springnet_general_hide_network_news'] = array(
			'#type' => 'checkbox',
			'#title' => t('Hide Network News'),
			'#default_value' => variable_get('springnet_general_hide_network_news', false),
			'#description' => t('Hide the news from the SpringNet Dashboard'),
	);
	return system_settings_form($form);
	return $form;
}


