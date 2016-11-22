<?php

/**
 * Page callback: Configure the node
 *
 * @see snadmin_menu()
 */
function springnet_form_config_node($form, &$form_state) {
	$form['springnet_node_uri'] = array(
			'#type' => 'markup',
			'#markup' => '<strong>spring://'.variable_get('springnet_node_uri', '').'</strong>',
			'#description' => 'Spring URI of this node'
		
	);

	return system_settings_form($form);
}

/**
 * Page callback: Configure the network settings
 *
 * @see snadmin_menu()
 */
function springnet_form_config_network($form, &$form_state) {

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
	$form['springnet_network_config_network_settings']['springnet_geonet_hostname'] = array(
			'#type' => 'textfield',
			'#title' => t('Hostname'),
			'#default_value' => variable_get('springnet_geonet_hostname', ''),
			'#size' => 32,
			'#maxlength' => 64,
			'#required' => TRUE,
	);

	$form['springnet_network_config_network_settings']['springnet_geonet_address'] = array(
			'#type' => 'textfield',
			'#title' => t('Address'),
			'#default_value' => variable_get('springnet_geonet_address', ''),
			'#size' => 32,
			'#maxlength' => 64,
			'#required' => TRUE,
	);

	$form['springnet_network_config_network_settings']['springnet_geonet_resource'] = array(
			'#type' => 'textfield',
			'#title' => t('Service Resource'),
			'#default_value' => variable_get('springnet_geonet_resource', ''),
			'#size' => 32,
			'#maxlength' => 64,
			'#required' => TRUE,
	);

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