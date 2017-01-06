<?php
class KeyringModel {
	private $table;

	public function __construct() {
		$this->table = 'sn_certificates';
	}

	public function getNodePublicKey() {
		return db_select($this->table, 'c')
					->fields('c', array('armor'))
					->condition('owned', 1, '=')
					->condition('keyid', 'private', '!=')
					->execute()
					->fetchField();
		}

	public function getNodePrivateKey() {
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

	public function getNodeCertificate() {
		return db_select($this->table, 'c')
					->fields('c')
					->condition('owned', 1, '=')
					->condition('keyid', 'private', '!=')
					->execute()
					->fetchAssoc();
	}

	public function getNodeKeyid() {
		return db_select($this->table, 'c')
					->fields('c',array('keyid'))
					->condition('owned', 1, '=')
					->condition('keyid', 'private', '!=')
					->execute()
					->fetchField();
	}

	public function setNodeCertificate($keyid, $email, $sigs, $armor) {
		$name = variable_get('springnet_node_uri', '');
		return $this->setCertificate($keyid, $name, $email, $sigs,
				$armor,'owned');
	}

	public function setNodePrivate($armor) {
		// is_admin checked inside set_certificate for 'private'
		return $this->setCertificate('private', 'private', 'private', array(),
				$armor, 'owned');
	}

	public function resetNodeKeys() {
		if(!user_access('administer site configuration')) {
			return false;
		}

		return db_delete($this->table)
						->condition('owned',1,'=')
						->execute();
	}

	public function setCertificate($keyid, $name, $email, $sigs,
			$armor, $status = 'other')
	{
		if('private' == $keyid) {
			if(!user_access('administer site configuration')) {
				return false;
			}
		}

		$owned = $status == 'owned' ? 1 : 0;
		$sigtext = implode(',', $sigs);

		if(!$this->getUidName($keyid)) {
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

	public function getCertificate($keyid) {
		if('private' == $keyid) {
			return null;
		}

		return db_select($this->table, 'c')
							->fields('c')
							->condition('keyid',$keyid, '=')
							->execute()
							->fetchAssoc();
	}

	public function removeCertificate($keyid) {
		if(!user_access('administer site configuration') || !$keyid || 'private' == $keyid) {
			return false;
		}

		return db_delete($this->table)
						->condition('keyid',$keyid,'=')
						->execute();
	}

	public function getKey($keyid) {
		$row = $this->getCertificate($keyid);

		if(!$row) {
			return false;
		}

		return $row['armor'];
	}

	public function getResolvedCertificate($keyid) {
		$node_id = $this->getNodeKeyid();

		$key = $this->getCertificate($keyid);

		if(!$key) {
			return null;
		}

		$list = explode(",", $key['sigs']);
		$sigs = array();
		$signed = false;
		foreach($list as $id) {
			$name = $this->getUidName($id);
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

	public function getUidList($page, $limit = 10) {
		$page = $page < 1 ? 0 : $page - 1;
		$limit = $limit < 1 ? 1 : $limit;

		$from = $page * $limit;

		$q = db_select($this->table,'c')
					->fields('c', array(
							'keyid','uidname','uidemail'							
					))
					->condition('keyid','private','!=')
					->orderBy('uidname')
					->range($from, $limit);

		return $q->execute()
					->fetchAllAssoc('uidname');
	}

	public function getUidName($keyid) {
		if('private' == $keyid) {
			return false;
		}
		
		return db_select($this->table, 'c')
							->fields('c', array('uidname'))
							->condition('keyid', $keyid, '=')
							->execute()
							->fetchField();
	}

	public function performPull($uri) {
		
		if(substr($uri, 0, 9) != 'spring://') {
			$uri = "spring://$uri";
		}

		require_once SPRINGNET_DIR.'/plugin/models/HttpService.php';
		$service = new HttpService();

		$keyid = $this->getNodeKeyid();
		try {
			$node = $service->dvspResolve($uri);
				
			if(!$node || !isset($node[0])) {
				return false;
			}
			$node = $node[0];
			$message = SpringDvs\Message::fromStr("service $uri/cert/pull/$keyid");
				
			$response = $service->dvspRequest($node->host(), $message);
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

	}

	public function hasPrivateKey() {
		if(!$this->getNodePrivateKey()) {
			return false;
		}
		return true;
	}

	public function hasCertificate() {
		
		if(!db_select($this->table,'c')
						->fields('c',array('certid'))
						->condition('keyid','private', '=')
						->execute()
						->fetchField())
			return false; 

		return true;
	}

	public function getCertificateCount() {
		$count = db_select($this->table, 'c')
						->fields('c')
						->countQuery()
						->execute()
						->fetchField();
		
		if($this->hasPrivateKey()) {
			$count--;
		}

		return $count;
	}
}
