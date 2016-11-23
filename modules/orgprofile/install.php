<?php

function springnet_netserv_orgprofile_menu() {
	
	$items['springnet/orgprofile'] = array(
			'title' => 'Organisation Profile',
			'description' => '',
			'page callback' => 'drupal_get_form',
			'page arguments' => array('springnet_netserv_orgprofile_edit_form'),
			'access arguments' => array('springnet service manage'),
			'type' => MENU_NORMAL_ITEM,
	);
	
	return $items;
}