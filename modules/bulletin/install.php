<?php

function springnet_netserv_bulletin_node_info() {
	return array(
			'snbulletin' => array(
					'name' => t('SpringNet Bulletin'),
					'base' => 'snbulletin',
					'description' => t('Bulletin posts to broadcast on the Spring network'),
					'has_title' => true,
					'locked' => true,
			)
	);
}


function springnet_netserv_bulletin_menu() {

	$items['springnet/bulletin/latest'] = array(
			'page callback' => 'springnet_netserv_bulletin_latest_gwreq',
			'access arguments' => array('access content'),
			'type' => MENU_CALLBACK,
	);

	$items['springnet/bulletin/explorer'] = array(
			'page callback' => 'springnet_netserv_bulletin_explorer_gwreq',
			'access arguments' => array('access content'),
			'type' => MENU_CALLBACK,
	);
	return $items;
}

function springnet_netserv_bulletin_block_info() {
	$block = array();
	$blocks['snns_bulletin_latest'] = array(
			'info' => t('SpringNet Latest Bulletins'),
			'cache' => DRUPAL_CACHE_PER_PAGE,
	);
	$blocks['snns_bulletin_explorer'] = array(
			'info' => t('SpringNet Explore Bulletins'),
			'cache' => DRUPAL_CACHE_PER_PAGE,
	);
	return $blocks;
}