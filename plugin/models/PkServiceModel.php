<?php
require_once SPRINGNET_DIR.'/plugin/models/KeyringModel.php';

class PkServiceModel {
	private $keyring;
	private $service;

	public function __construct() {
		$this->keyring = new KeyringModel();
		//$this->service = 'http://ppks.zni.lan';
		$this->service = 'https://pkserv.spring-dvs.org/process';
	}
	
	public function generateKeypair($name, $email, $passphrase) {
		$body = "KEYGEN
$passphrase
$name
$email\n\n";
		
		return $this->performRequest($body);
	}
	
	public function import($armor, $subject = null) {
		$body = "IMPORT
PUBLIC {
$armor
}\n";
		if($subject) {
			$body .= "SUBJECT {\n$subject\n}\n";
		}
		
		$body .= "\n";

		return $this->performRequest($body);
		
		
	}
	
	public function sign($certificate, $key, $passphrase) {
		$body = "SIGN
$passphrase
PUBLIC {
$certificate
}
PRIVATE {
$key
}\n";
		return $this->performRequest($body);
	}
	
	public function keyring() {
		return $this->keyring;
	}
	
	private function performRequest($body) {
		
		$ch = curl_init($this->service);
		
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt($ch, CURLOPT_POST,           1 );
		curl_setopt($ch, CURLOPT_USERAGENT,      "WpSpringNet/0.1" );
		curl_setopt($ch, CURLOPT_POSTFIELDS,      $body);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);
		curl_setopt($ch, CURLOPT_HTTPHEADER,     array(
				'User-Agent: WpSpringNet/0.1'));
		$json = curl_exec($ch);
		
		if($json === false) {
			return false;
		}

		return json_decode($json, true);
	}
}
