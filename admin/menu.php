<?php
require SPRINGNET_DIR.'/admin/views/config.php';
require SPRINGNET_DIR.'/admin/ajax_handlers.php';

function snadmin_menu(&$items) {
	$items['admin/config/services/springnet'] = array(
			'title' => 'SpringNet',
			'description' => 'Configure your Spring network node',
			'page callback' => 'drupal_get_form',
			'page arguments' => array('springnet_form_config_node'),
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
			'page arguments' => array('springnet_form_config_network'),
			'access arguments' => array('access administration pages'),
			'type' => MENU_LOCAL_TASK,
			'weight' => 2,
	);
	
	$items['admin/config/services/springnet/georesolve'] = array(
			'page callback' => 'springnet_get_geonetwork_ajax',
			'type' => MENU_CALLBACK,
			'access arguments' => array('access administration pages'),
	);
}