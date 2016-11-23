<?php
require_once SPRINGNET_DIR.'/plugin/models/HttpService.php';
require_once SPRINGNET_DIR.'/plugin/models/KeyringModel.php';

class LocalNodeModel {
	private $primary_host;
	private $node_hostname;
	private $node_springname;
	private $reg_cache;

	public function __construct() {
		$this->primary_host = variable_get('springnet_geonet_hostname');
		$this->node_hostname = variable_get('springnet_node_hostname');
		$this->node_springname = variable_get('springnet_node_springname');
		$this->reg_cache = null;
	}

	public function register() {
		$keyring = new KeyringModel();
		$double = $this->node_springname.",".$this->node_hostname;
		$token = variable_get('springnet_geonet_token');
		$armor = $keyring->getNodePublicKey();

		$msg = \SpringDvs\Message::fromStr("register {$double};org;http;$token\n$armor");

		$response = HttpService::dvspRequest($this->primary_host, $msg);

		if(!$response) {
			return false;
		}

		if(\SpringDvs\CmdType::Response != $response->cmd()
				&& \SpringDvs\ProtocolResponse::Ok != $response->content()->code()) {
					return false;
				}

				return true;
	}

	public function enable() {
		$msg = \SpringDvs\Message::fromStr("update {$this->node_springname} state enabled");
		$response = HttpService::dvspRequest($this->primary_host, $msg);
		return $this->responseOk($response);
	}

	public function disable() {
		$msg = \SpringDvs\Message::fromStr("update {$this->node_springname} state disabled");
		$response = HttpService::dvspRequest($this->primary_host, $msg);
		return $this->responseOk($response);
	}

	public function isRegistered() {
		$msg = \SpringDvs\Message::fromStr("info node {$this->node_springname}");

		$response = HttpService::dvspRequest($this->primary_host, $msg);

		if(!$response) {
			return false;
		}

		if(\SpringDvs\CmdType::Response == $response->cmd()
				&& \SpringDvs\ProtocolResponse::NetspaceError == $response->content()->code()) {
					return false;
				}

				$this->reg_cache = $response;
				return true;
	}

	public function isEnabled() {
		if(!$this->reg_cache) {
			$msg = \SpringDvs\Message::fromStr("info node {$this->node_springname} state");
				
			$response = HttpService::dvspRequest($this->primary_host, $msg);

				
			if(!$response) {
				return false;
			}
		} else {
			$response = $this->reg_cache;
		}

		if(!$this->responseOk($response)) {
			return false;
		}

		if(\SpringDvs\ContentResponse::NodeInfo
		!= $response->content()->type()) {
			return false;
		}

		if(\SpringDvs\NodeState::Enabled
		!= $response->content()->content()->state()) {
			return false;
		}

		return true;
	}

	private function responseOk($response) {
		if(!$response) {
			return false;
		}

		if(\SpringDvs\CmdType::Response != $response->cmd()) {
			return false;
		}


		if(\SpringDvs\ProtocolResponse::Ok != $response->content()->code()) {
			return false;
		}
		return true;
	}
	
	public function uri() {
		return variable_get('springnet_node_uri', '');
	}
	
	public function springname() {
		return variable_get('springnet_node_springname', '');
	}
	
	public function jsonEncode($data) {
		
	}
}
