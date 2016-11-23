<?php

function springnet_service_dashboard_form($form, &$form_state) {
	$response = drupal_http_request("http://spring-dvs.org/wp-json/wp/v2/posts?per_page=5&filter[category_name]=Network");
	$posts = json_decode($response->data);
	$posts = !$posts ? array() : $posts;
	
	$nh = new NotificationHandler();
	$page = filter_input(INPUT_GET, "page");
	$page = !$page ? 1 : $page < 1 ? 1 : $page;
	$limit = 10;
	
	$notifs = $nh->getNotifications($page,$limit);
	$ptotal = $nh->getTotalPages();
	if($page > $ptotal){ $page = $ptotal; }
	

	$form['notifications'] = array(
			'#type' => 'fieldset',
			'#title' => 'Notifications'
	);
	
	
	if(empty($notifs)) {
		$form['notifications']['open'] = array('#markup' => '<strong>No notifications</strong>');
	} else {
		$form['notifications']['open'] = array('#markup' => '<ul>');
		foreach($notifs as $notif) {
			$markup = '<li>'
					.'<a href="' . $notif->notif_action .'?_actionid='.$notif->notif_id.'">'
					. $notif->notif_title
					.'</a><br>'	
					.'<span class="source">'. $notif->notif_source .'</span>'
					.'<div class="description">'.$notif->notif_description.'</div>'
					.'</li>';
			$form['notifications'][$notif->notif_id] = array(
					'#markup' => $markup, 
			);
		}
		$form['notifications']['close'] = array('#markup' => '</ul>');
		
		$markup = $page > 1 ? '<a href="/springnet/?page='.($page-1).'"><strong>&lt;</strong></a>' : "<strong>&lt;</strong>";
		$markup .= "&nbsp;&nbsp;$page / $ptotal&nbsp;&nbsp;";
		$markup .= $page < $ptotal ? '<a href="/springnet/?page='.($page+1).'"><strong>&gt;</strong></a>' : "<strong>&gt;</strong>";
		
		
		$form['notifications']['tool'] = array(
				'#markup' => $markup
		);
	}
	
	if(variable_get('springnet_general_hide_network_news') ==  0) {
		$form['network_news'] = array(
				'#type' => 'fieldset',
				'#title' => 'Network News'
		);
		$first = true;
		$form['network_news']['open'] = array(
				'#markup'=> '<ul style="list-style-type: none;">',
		);
		foreach($posts as $post) {
			$markup = '<a target="_BLANK" href="'.$post->link.'">'.$post->title->rendered.'</a><br>';
			$markup .= format_date(strtotime($post->date), 'custom', 'D j M Y');
			if($first) {
				$markup .= $post->excerpt->rendered;
				$first = false;
			}
			$form['network_news'][$post->id] = array(
				'#prefix' => '<li>',
				'#markup'=> $markup,
				'#suffix' => '</li>',
			);
		}
		
		$form['network_news']['close'] = array(
				'#markup'=> '</ul>',
		);
	}
	$form['details'] = array(
			'#type' => 'fieldset',
			'#title' => 'Details'
	);
	$first = true;
	$form['details']['version'] = array(
			'#markup'=> '<strong>Version:</strong> '.SPRINGNET_VERSION,
	);
	return $form; 
}