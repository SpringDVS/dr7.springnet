<?php
$local = new LocalNodeModel();
return json_encode(
		array($local->uri() => array(
			'name' => variable_get('springnet_serv_orgprofile_name', ''),
			'website' => variable_get('springnet_serv_orgprofile_address', ''),
			'tags' => explode(',',variable_get('springnet_serv_orgprofile_tags', ''))
		))
	);