<?php

function snadmin_menu(&$items) {
	
	// Config
	$items['admin/config/services/springnet'] = array(
			'title' => 'SpringNet',
			'description' => 'Configure your Spring network node',
			'page callback' => 'drupal_get_form',
			'page arguments' => array('springnet_config_node_form'),
			'access arguments' => array('access administration pages'),
			'type' => MENU_NORMAL_ITEM,
	);
	
	$items['admin/config/services/springnet/node'] = array(
			'title' => 'Node',
			'type' => MENU_DEFAULT_LOCAL_TASK,
			'weight' => 1
	);
	
	$items['admin/config/services/springnet/network'] = array(
			'title' => 'Network',
			'page callback' => 'drupal_get_form',
			'page arguments' => array('springnet_config_network_form'),
			'access arguments' => array('access administration pages'),
			'type' => MENU_LOCAL_TASK,
			'weight' => 2,
	);
	
	$items['admin/config/services/springnet/certificate'] = array(
			'title' => 'Certificate',
			'page callback' => 'drupal_get_form',
			'page arguments' => array('springnet_config_certificate_form'),
			'access arguments' => array('access administration pages'),
			'type' => MENU_LOCAL_TASK,
			'weight' => 3,
	);
	

	$items['admin/config/services/springnet/general'] = array(
			'title' => 'General',
			'page callback' => 'drupal_get_form',
			'page arguments' => array('springnet_config_general_form'),
			'access arguments' => array('access administration pages'),
			'type' => MENU_LOCAL_TASK,
			'weight' => 4,
	);
	
	// Ajax
	
	$items['admin/config/services/springnet/georesolve'] = array(
			'page callback' => 'springnet_get_geonetwork_ajax',
			'type' => MENU_CALLBACK,
			'access arguments' => array('access administration pages'),
	);
	
	$items['admin/config/services/springnet/nodectl/register'] = array(
			'page callback' => 'springnet_register_node_ajax',
			'type' => MENU_CALLBACK,
			'access arguments' => array('access administration pages'),
	);

	$items['admin/config/services/springnet/nodectl/enable'] = array(
			'page callback' => 'springnet_enable_node_ajax',
			'type' => MENU_CALLBACK,
			'access arguments' => array('access administration pages'),
	);

	$items['admin/config/services/springnet/nodectl/disable'] = array(
			'page callback' => 'springnet_disable_node_ajax',
			'type' => MENU_CALLBACK,
			'access arguments' => array('access administration pages'),
	);
	
	$items['admin/config/services/springnet/fakeinstall'] = array(
			'page callback' => 'springnet_fake_install_routine',
			'type' => MENU_CALLBACK,
			'access arguments' => array('access administration pages'),
	);
	
	$items['admin/config/services/springnet/fakeuninstall'] = array(
			'page callback' => 'springnet_fake_uninstall_routine',
			'type' => MENU_CALLBACK,
			'access arguments' => array('access administration pages'),
	);
	
	
	// SpringNet Dashboard
	$items['springnet'] = array(
			'title' => 'SpringNet',
			'description' => 'Overview of Springnet',
			'page callback' => 'drupal_get_form',
			'page arguments' => array('springnet_service_dashboard_form'),
			'access arguments' => array('springnet service manage'),
			'type' => MENU_NORMAL_ITEM,
	);

	// SpringNet Keyring
	$items['springnet/keyring'] = array(
			'title' => 'Keyring',
			'description' => '',
			'page callback' => 'springnet_keyring_view',
			'access arguments' => array('springnet keyring manage'),
			'type' => MENU_NORMAL_ITEM,
	);
	
	$items['springnet/keyring/overview'] = array(
			'title' => 'Keyring',
			'type' => MENU_DEFAULT_LOCAL_TASK,
			'weight' => 1
	);
	
	$items['springnet/keyring/import'] = array(
			'title' => 'Import',
			'page callback' => 'springnet_keyring_import_controller',
			'access arguments' => array('springnet keyring manage'),
			'type' => MENU_LOCAL_TASK,
			'weight' => 2,
	);

	$items['springnet/keyring/pullreq'] = array(
			'title' => 'Pull Requests',
			'description' => '',
			'page callback' => 'springnet_keyring_pullreq_controller',
			'access arguments' => array('springnet keyring manage'),
			'type' => MENU_LOCAL_TASK,
			'weight' => 3,
	);
	
	$items['springnet/keyring/view'] = array(
			'title' => 'Certificate',
			'description' => '',
			'page callback' => 'springnet_keyring_cert_view',
			//			'page arguments' => array('springnet_keyring_overview'),
			'access arguments' => array('springnet keyring manage'),
			'type' => MENU_VISIBLE_IN_BREADCRUMB,
	);

	$items['springnet/keyring/delete'] = array(
			'title' => 'Certificate',
			'description' => '',
			'page callback' => 'springnet_keyring_cert_delete',
			//			'page arguments' => array('springnet_keyring_overview'),
			'access arguments' => array('springnet keyring manage'),
			'type' => MENU_VISIBLE_IN_BREADCRUMB,
	);
	
	$items['springnet/keyring/unlock'] = array(
			'title' => 'Key Safe',
			'description' => '',
			'page callback' => 'springnet_keyring_unlock_controller',
			//			'page arguments' => array('springnet_keyring_overview'),
			'access arguments' => array('springnet keyring manage'),
			'type' => MENU_VISIBLE_IN_BREADCRUMB,
	);
	

	$items = array_merge($items, snadmin_menu_modules());

}

function snadmin_menu_modules() {
	$items = array();

	foreach(ModuleHandler::listPaths('install.php') as $mod => $path) {
		include_once $path;
		$ret = module_invoke( 'springnet_netserv_'.$mod, 'menu' );
		if(!$ret) continue;

		$items = array_merge($items, $ret);
	}
	return $items;

}