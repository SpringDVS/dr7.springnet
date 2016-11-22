<?php
function snintern_front_menu(&$items) {
	$items['spring'] = array(
			'page callback' => 'snfront_service_request',
			'type' => MENU_CALLBACK,
			'access arguments' => array('access content'),
	);
}

function snfront_service_request() {
	echo "104";
}
