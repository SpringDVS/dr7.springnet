<?php

/**
 * Ajax handler for resolving the geonetwork HTTP details
 * 
 * @param string $geonet The name of the network to resolve
 */
function springnet_get_geonetwork_ajax($geonet) {
	$response = drupal_http_request("https://resolve.spring-dvs.org/geosubs/$geonet");
	echo $response->data;
}
