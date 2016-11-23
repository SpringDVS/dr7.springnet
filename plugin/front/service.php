<?php
function snfront_service_request() {
	$request = trim(file_get_contents('php://input'));
	define('SPRING_IF', true);	
	echo snfront_protocol_handler($request);
	return true;
}

function snfront_protocol_handler($request) {
	try {
		$msg = \SpringDvs\Message::fromStr($request);

	} catch(Exception $e) {
		return "104"; // MalformedContent
	}

	switch($msg->cmd()) {
		case \SpringDvs\CmdType::Service: return snfront_service($msg);
		default: return "121";
	}
}

function snfront_service(\SpringDvs\Message $msg) {
	require_once SPRINGNET_DIR.'/plugin/models/LocalNodeModel.php';
	require_once SPRINGNET_DIR.'/plugin/models/NetworkService.php';
	$ns = new NetworkService(variable_get('springnet_node_springname'),
			ModuleHandler::listPaths('request.php'));

	die($ns->run($msg->content()->uri()));
}
