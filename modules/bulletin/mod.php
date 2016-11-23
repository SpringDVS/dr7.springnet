<?php

function snbulletin_form($node, &$form_state) {
	return node_content_form($node, $form_state);
}


function springnet_form_snbulletin_node_form_submit($form, &$form_state) {
	drupal_set_message(t('Form submit\n\n\n'.print_r($form_state['values'], true)));
	return $form;
}

function springnet_netserv_bulletin_latest_gwreq() {
	$network =($n = filter_input(INPUT_POST,'network')) ?
	"spring://".$n
	: null;
	$node = ($n = filter_input(INPUT_POST,'node')) ?
	"spring://$n"
	: null;
	
	$uid = ($u = filter_input(INPUT_POST,'uid')) ?
	"$u"
	: null;
	
	
	if(!$network && !$node) { die('{"status":"error"}'); }
	
	$hier = $network ? $network : $node;
	
	$qobj = array();
	if($t = filter_input(INPUT_POST,'tags')) { $qobj['tags'] = $t; };
	$qobj['limit'] = 2;
	
	if($t = filter_input(INPUT_POST,'limit')) { $qobj['limit'] = $t; };
	
	
	include SPRINGNET_DIR.'/plugin/models/GatewayHandler.php';
	$nodes = GatewayHandler::resolveUri($hier);
	


	$service = $network || ($node && $uid) ?
	"$hier/bulletin/{$uid}?".http_build_query($qobj)
	: "$hier/orgprofile/?".http_build_query($qobj);
	
	
	try {
		$message = \SpringDvs\Message::fromStr("service $service");
		$response = GatewayHandler::outboundFirstResponse($message, $nodes);
	} catch(\Exception $e) {
	
		die(['status' => 'error', 'uri' => $service]);
	}
	
	if(!$response) {
		die(json_encode(['status' => 'error', 'uri' => $hier, 'reason' => 'Request failed']));
	}
	
	$serviced = array();
	
	
	switch($response->getContentResponse()->type()) {
	
		case \SpringDvs\ContentResponse::ServiceText:
			$jobj = json_decode($response->getContentResponse()->getServiceText()->get(), true);
			$v = reset($jobj);
			$k = key($jobj);
			$serviced[] = array($k => $v);
			break;
	
	
		case \SpringDvs\ContentResponse::ServiceMulti:
			foreach($response->getContentResponse()->getServiceParts() as $msg) {
				$jobj = json_decode($msg->getContentResponse()->getServiceText()->get(), true);
				$v = reset($jobj);
				$k = key($jobj);
					
				$serviced[] = array($k => $v);
			}
			break;
				
		default: break;
	}
	
	
	$dec = [
			"status" => 'ok',
			"content" => $serviced
	];
	
	die(json_encode($dec));
}

function springnet_netserv_bulletin_explorer_gwreq() {
	$network = filter_input(INPUT_POST,'network') ?
	filter_input(INPUT_POST,'network') : "";
	
	$category = filter_input(INPUT_POST,'category') ?
	filter_input(INPUT_POST,'category') : "";
	
	$uid = filter_input(INPUT_POST,'uid') ?
	filter_input(INPUT_POST,'uid') : "";
	
	$profile = filter_input(INPUT_POST,'profile') ?
	filter_input(INPUT_POST,'profile') : "";
		
	include SPRINGNET_DIR.'/plugin/models/GatewayHandler.php';
	
	$uri = "spring://$network";
	$message = null;
	$out = null;
	
	
	
	if($profile != "") {
		$uri = "spring://$profile";
		$message = \SpringDvs\Message::fromStr("service $uri/orgprofile/");
	} else if($uid == "") {
		$message = \SpringDvs\Message::fromStr("service $uri/bulletin/?categories=$category");
	} else {
		$message = \SpringDvs\Message::fromStr("service $uri/bulletin/$uid");
	}
	
	$response = GatewayHandler::requestUriFirstResponse($uri, $message);
	
	
	if(!$response) { die(json_encode(array('status'=>'error on response'))); }
	
	
	if($profile == "") {
		if($uid == "") { // Getting broadcast listings
			$out = springnet_netserv_bulletin_explore_listing($response);
		} else { // Getting specific listing from node
			$out = springnet_netserv_bulletin_explore_listing($response);
		}
	} else {
		$out = springnet_netserv_bulletin_explore_profile($response);
	}
	
	die(json_encode(array('status'=>'ok','content'=>$out)));
}


function springnet_netserv_bulletin_explore_listing(\SpringDvs\Message $response) {

	$listing = array();
	$type = $response->getContentResponse()->type();
	if($type == \SpringDvs\ContentResponse::ServiceText) {
		$json = $response->getContentResponse()->getServiceText()->get();
		$jobj = json_decode($json,true);
		$listing = reset($jobj);
		$listing['node'] = key($jobj);
	} elseif ($type == \SpringDvs\ContentResponse::ServiceMulti) {
		foreach($response->getContentResponse()->getServiceParts() as $msg) {
			$json = $msg->getContentResponse()->getServiceText()->get();
			$jobj = json_decode($json,true);
			$nlst = reset($jobj);
			$node = key($jobj);
			foreach($nlst as &$item) { $item['node'] = $node; }
				
			$listing = array_merge($listing, $nlst);
		}
	}

	return $listing;
}

function springnet_netserv_bulletin_explore_profile($response) {
	$profile = array();
	$type = $response->getContentResponse()->type();
	if($type == \SpringDvs\ContentResponse::ServiceText) {
		$json = $response->getContentResponse()->getServiceText()->get();
		$jobj = json_decode($json,true);
		$profile = reset($jobj);
		$profile['node'] = key($jobj);
	}

	return $profile;
}


function springnet_netserv_bulletin_latest_block_view() {
	$network = variable_get('snns_bulletin_latest_network', variable_get('springnet_geonet_name'));

	$loader = '<img class="sdvs-loader" id="spring-bulletin-loader" src="/'.drupal_get_path('module', 'springnet').'/res/img/loading.gif">';
	$block['subject'] = t('Latest Bulletins on <em>'.$network.'.uk</em>' . $loader);
	

	$tags = variable_get('snns_bulletin_latest_tags', '');
	$limit = variable_get('snns_bulletin_latest_limit', '');

	$markup ='<div id="snetbl-list-container" class="snet-bulletin-widget spring-bulletin">';
	$markup .= '<div>Filter: <span id="sdvs-bulletin-list-filter">none</span> <a href="javascript:void(0);" onclick="SpringBulletinLatestClient.rerequest(``)" class="reset">reset</a></div>';
	$markup .= '<table class="wp-list-table widefat  striped main">';
	$markup .= '<tbody class="the-list" id="sdvs-bulletin-list-body">';
	$markup .= '</tbody>';
	$markup .= '</table>';
	$markup .= '</div>';
	$markup .= '<input type="hidden" id="spring-bulletin-network" value="'.$network.'">';
	$markup .= '<input type="hidden" id="spring-bulletin-tags" value="'.$tags.'">';
	$markup .= '<input type="hidden" id="spring-bulletin-limit" value="'.$limit.'">';

	$block['content'] = array(
		'#markup' => $markup,
		'#attached' => array(
			'js' =>array(
					drupal_get_path('module', 'springnet').'/modules/bulletin/res/BulletinPopup.js',
					drupal_get_path('module', 'springnet').'/modules/bulletin/res/BulletinLatestClient.js',
					),
			'css' =>array(
					drupal_get_path('module', 'springnet').'/modules/bulletin/res/BulletinPopup.css',
					drupal_get_path('module', 'springnet').'/modules/bulletin/res/BulletinLatestStyle.css',
			),
		),
	);

	return $block;
}

function springnet_netserv_bulletin_latest_block_configure() {
	$form['snns_bulletin_latest_network'] = array(
			'#type' => 'textfield',
			'#title' => t('Network'),
			'#description' => t('The initial GeoNetwork to query'),
			'#default_value' => variable_get('snns_bulletin_latest_network', variable_get('springnet_geonet_name')),
	);
	$form['snns_bulletin_latest_tags'] = array(
			'#type' => 'textfield',
			'#title' => t('Tags'),
			'#description' => t('Comma separated list of tags to initially filter with'),
			'#default_value' => variable_get('snns_bulletin_latest_tags', ''),
	);
	$form['snns_bulletin_latest_limit'] = array(
			'#type' => 'textfield',
			'#title' => t('Limit'),
			'#description' => t('Limit the number of posts per organisation (default is 2)'),
			'#default_value' => variable_get('snns_bulletin_latest_limit', ''),
	);
	return $form;
}

function springnet_netserv_bulletin_latest_block_save($edit = array()) {
	variable_set('snns_bulletin_latest_network', $edit['snns_bulletin_latest_network']);
	variable_set('snns_bulletin_latest_tags', $edit['snns_bulletin_latest_tags']);
	variable_set('snns_bulletin_latest_limit', $edit['snns_bulletin_latest_limit']);
}

function springnet_netserv_bulletin_explorer_block_configure() {
	$form['snns_bulletin_explorer_network'] = array(
			'#type' => 'textfield',
			'#title' => t('Network'),
			'#description' => t('The initial GeoNetwork to query'),
			'#default_value' => variable_get('snns_bulletin_explorer_network', variable_get('springnet_geonet_name')),
	);
	$form['snns_bulletin_explorer_categories'] = array(
			'#type' => 'textfield',
			'#title' => t('Categories'),
			'#description' => t('Comma separated list of category options to display'),
			'#default_value' => variable_get('snns_bulletin_explorer_categories', 'Events,Services'),
	);
	return $form;
}

function springnet_netserv_bulletin_explorer_block_save($edit = array()) {
	variable_set('snns_bulletin_explorer_network', $edit['snns_bulletin_explorer_network']);
	variable_set('snns_bulletin_explorer_categories', $edit['snns_bulletin_explorer_categories']);
}


function springnet_netserv_bulletin_explorer_block_view() {

	$uri = variable_get('snns_bulletin_explorer_network', variable_get('springnet_geonet_name'));
	$categories = explode(",", variable_get('snns_bulletin_explorer_categories', 'Events,Services'));

	$selected = $categories[0];
	ob_start();
	?>
		<div class="snetb-explorer snet-bulletin-widget" id="snetb-explorer-container">
		<h2 class="tabs">

		<?php foreach($categories as $cat): ?>
			<?php if($cat == $selected): ?>
				<div id="snetb-explorer-<?php echo $cat; ?>" class="tab-button tab-button-active">
				<a href="javascript:void(0);" onclick="SpringBulletinExplorerClient.filterCat(<?php echo "'$cat'" ?>);"><?php echo $cat; ?></a>
				</div>
			<?php else: ?>
				<div id="snetb-explorer-<?php echo $cat; ?>" class="tab-button">
					<a href="javascript:void(0);" onclick="SpringBulletinExplorerClient.filterCat(<?php echo "'$cat'" ?>);"><?php echo $cat; ?></a>
				</div>
			<?php endif; ?>
				
		
		<?php endforeach; ?>
		
		</h2> <!-- tabs -->
		<table class="listing">
			<tbody id="snetb-explorer-listing">
			</tbody>				
		</table>
		</div> <!-- snet-explorer -->
	
	<?php
	
	$markup = ob_get_clean();
	$markup .= '<input type="hidden" id="spring-explorer-network" value="'.$uri.'">';
	$markup .= '<input type="hidden" id="spring-explorer-category" value="'.$selected.'">';
	
	
	$loader = '<img class="sdvs-loader" id="spring-explorer-loader" src="/'.drupal_get_path('module', 'springnet').'/res/img/loading.gif">';
	$block['subject'] = t('Explore <em>'.$uri.'.uk</em>' . $loader);
	$block['content'] = array(
			'#markup' => $markup,
			'#attached' => array(
					'js' =>array(
							drupal_get_path('module', 'springnet').'/modules/bulletin/res/BulletinPopup.js',
							drupal_get_path('module', 'springnet').'/modules/bulletin/res/BulletinExplorerClient.js',
					),
					'css' =>array(
							drupal_get_path('module', 'springnet').'/modules/bulletin/res/BulletinPopup.css',
							drupal_get_path('module', 'springnet').'/modules/bulletin/res/BulletinExplorerStyle.css',
					),
			),
		);
	return $block;
}