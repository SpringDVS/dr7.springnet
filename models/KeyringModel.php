<?php
class Keyring_Model {
	private $table;

	public function __construct() {
		$this->table = sn_certificates;
	}

	public function get_node_public_key() {
		return db_select($this->table, 'c')
					->fields('c', array('armor'))
					->condition('owned', 1, '=')
					->condition('keyid', 'private', '=')
					->execute()
					->fetchAssoc();
		}

	public function get_node_private_key() {
		if(!user_access('administer site configuration')) {
			return false;
		}
		return db_select($this->table, 'c')
					->fields('c', array('armor'))
					->condition('owned', 1, '=')
					->condition('keyid', 'private', '=')
					->execute()
					->fetchField();
	}

	public function get_node_certificate() {
		return db_select($this->table, 'c')
					->fields('c')
					->condition('owned', 1, '=')
					->condition('keyid', 'private', '!=')
					->execute()
					->fetchAssoc();
	}

	public function get_node_keyid() {
		return db_select($this->table, 'c')
					->fields('c',array('keyid'))
					->condition('owned', 1, '=')
					->condition('keyid', 'private', '!=')
					->execute()
					->fetchField();
	}

	public function set_node_certificate($keyid, $email, $sigs, $armor) {
		$name = get_option('node_uri');
		return $this->set_certificate($keyid, $name, $email, $sigs,
				$armor,'owned');
	}

	public function set_node_private($armor) {
		// is_admin checked inside set_certificate for 'private'
		return $this->set_certificate('private', 'private', 'private', array(),
				$armor, 'owned');
	}

	public function reset_node_keys() {
		if(!user_access('administer site configuration')) {
			return false;
		}

		return db_delete($this->table)
						->condition('owned',1,'=')
						->execute();
	}

	public function set_certificate($keyid, $name, $email, $sigs,
			$armor, $status = 'other')
	{
		if('private' == $keyid) {
			if(!user_access('administer site configuration')) {
				return false;
			}
		}

		$owned = $status == 'owned' ? 1 : 0;
		$sigtext = implode(',', $sigs);

		if(!$this->get_uid_name($keyid)) {
			return db_insert($this->table)
							->fields(array(
								'keyid' => $keyid,
								'uidname' => $name,
								'uidemail' => $email,
								'sigs' => $sigtext,
								'armor' => $armor,
								'owned' => $owned,
							))
							->execute();
		} else {
			return db_update($this->table)
							->fields(array(
								'sigs' => $sigtext,
								'armor' => $armor,
							))
							->condition('keyid', $keyid, '=')
							->execute();
		}


	}

	public function get_certificate($keyid) {
		if('private' == $keyid) {
			return null;
		}

		return db_select($this->table, 'c')
							->fields('c')
							->condition('keyid',$keyid, '=')
							->execute()
							->fetchAssoc();
	}

	public function remove_certificate($keyid) {
		if(!user_access('administer site configuration') || !$keyid || 'private' == $keyid) {
			return false;
		}

		return db_delete($this->table)
						->condition('keyid',$keyid,'=')
						->execute();
	}

	public function get_key($keyid) {
		$row = $this->get_certificate($keyid);
		
		if(!$row) {
			return false;
		}
		
		return $row['keyid'];
	}

	public function get_resolved_certificate($keyid) {
		$node_id = $this->get_node_keyid();

		$key = $this->get_certificate($keyid);

		if(!$key) {
			return null;
		}

		$list = explode(",", $key['sigs']);
		$sigs = array();
		$signed = false;
		foreach($list as $id) {
			$name = $this->get_uid_name($id);
			$name = $name != null ? $name : 'unknown';
			if($id == $node_id) {
				$signed = true;
			}
			$sigs[] = array(
					'keyid' => $id,
					'name' => $name
			);
		}

		$key['sigs'] = $sigs;
		$key['signed'] = $signed;
		return $key;
	}

	public function get_uid_list($page, $limit = 10) {
		$page = $page < 1 ? 0 : $page - 1;
		$limit = $limit < 1 ? 1 : $limit;

		$from = $page * $limit;
		
		return db_select($this->table,'c')
					->fields('c', array(
							'keyid','uidname','uidemail'							
					))
					->condition('keyid','private','!=')
					->orderBy('uidname')
					->range($from, $limit)
					->execute()
					->fetchAllAssoc('keyid');
	}

	public function get_uid_name($keyid) {
		if('private' == $keyid) {
			return false;
		}
		
		return db_select($this->table, 'c')
							->fields(array('uidname'))
							->condition('keyid', $keyid, '=')
							->execute()
							->fetchField();
	}

	public function perform_pull($uri) {
/*		
		if(substr($uri, 0, 9) != 'spring://') {
			$uri = "spring://$uri";
		}

		require_once SPRINGNET_DIR.'/plugin/models/class-http-service.php';
		$service = new HTTP_Service();
		$keyid = $this->get_node_keyid();
		try {
				
			$node = $service->dvsp_resolve($uri);
				
			if(!$node || !isset($node[0])) {
				return false;
			}
			$node = $node[0];
			$message = SpringDvs\Message::fromStr("service $uri/cert/pull/$keyid");
				
			$response = $service->dvsp_request($node->host(), $message);
			if($response->content()->code() != \SpringDvs\ProtocolResponse::Ok) {
				return false;
			}
			$response_array = json_decode($response->content()->content()->get(), true);
			$key = array_pop($response_array);
			$key = isset($key['key']) ?  $key['key'] : false;
			return $key;
		} catch(Exception $e) {
			return false;
		}
*/
	}

	public function has_private_key() {
		if(!$this->get_node_private_key()) {
			return false;
		}
		return true;
	}

	public function has_certificate() {
		
		if(!db_select($this->table,'c')
						->fields('c',array('certid'))
						->condition('keyid','private', '='))
			return false; 

		return true;
	}

	public function get_certificate_count() {
		$count = db_select($this->table, 'c')
						->fields('c')
						->countQuery()
						->execute()
						->fetchField();
		
		if($this->has_private_key()) {
			$count--;
		}

		return $count;
	}
}
