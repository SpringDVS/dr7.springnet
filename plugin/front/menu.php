<?php
function snfront_menu(&$items) {
	$items['spring'] = array(
			'page callback' => 'snfront_service_request',
			'type' => MENU_CALLBACK,
			'access arguments' => array('access content'),
	);
}
