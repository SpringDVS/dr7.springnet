<?php
function springnet_netserv_orgprofile_edit_form() {
	$form['orgprofile_name'] = array(
			'#type' => 'textfield',
			'#title' => t('Organisation Name'),
			'#default_value' => variable_get('springnet_serv_orgprofile_name', ''),
			'#description' => t('Name of the organisation behind the node'),
			'#size' => 32,
			'#maxlength' => 64,
			'#required' => TRUE,
	);

	$form['orgprofile_address'] = array(
			'#type' => 'textfield',
			'#title' => t('Website Address'),
			'#description' => t('Full address (http://www.example.com)'),
			'#default_value' => variable_get('springnet_serv_orgprofile_address', ''),
			'#size' => 32,
			'#maxlength' => 320,
			'#required' => TRUE,
	);

	$form['orgprofile_tags'] = array(
			'#type' => 'textfield',
			'#title' => t('Tags'),
			'#description' => t('Comma separated tags'),
			'#default_value' => variable_get('springnet_serv_orgprofile_tags', ''),
			'#size' => 32,
			'#maxlength' => 64,
			'#required' => FALSE,
	);

	$form['submit'] = array(
			'#type' => 'submit',
			'#value' => t('Save Changes'),
	);
	return $form;
}

function springnet_netserv_orgprofile_edit_form_validate($form, &$form_state) {
	if(substr($form_state['values']['orgprofile_address'],0,7) != 'http://') {
		form_set_error('orgprofile_address',
				t('Web address does not start with `http://` scheme'));
	}
}

function springnet_netserv_orgprofile_edit_form_submit($form, &$form_state) {
	variable_set('springnet_serv_orgprofile_name',$form_state['values']['orgprofile_name']);
	variable_set('springnet_serv_orgprofile_address',$form_state['values']['orgprofile_address']);
	variable_set('springnet_serv_orgprofile_tags',$form_state['values']['orgprofile_tags']);
	drupal_set_message("Profile saved");
}
