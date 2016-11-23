<?php
/**
 * Handler for gateway requests.
*
* As nodes act as gateways to the Spring network, this is used to
* handle certain requests made of the node, and perform that request
* internally on the network, providing the response once received.
*
* Each node provides particular interfaces to the network through
* gateway services, and these are what the external system works
* through. Gateway services will likely have a complimentary
* network service for which they are providing the interface.
*
*/

require_once SPRINGNET_DIR.'/plugin/models/HttpService.php';

class GatewayHandler {

	/**
	 * Perform a resolution of a Spring URI.
	 *
	 * If there is an error then it returns false otherwise
	 * it will return an array of objects that implement the
	 * INodeNetInterface
	 *
	 * @param string $uri
	 * @return array(\SpringDvs\INodeNetInterface) | false
	 */
	public static function resolveUri($uri) {
		$message = SpringDvs\Message::fromStr("resolve $uri");

		$response = HttpService::dvspRequest(variable_get('springnet_geonet_hostname'),
				$message);

		if(!$response
				|| $response->cmd() != \SpringDvs\CmdType::Response
				|| $response->content()->code() != \SpringDvs\ProtocolResponse::Ok) {
					return false;
				}
				$type = $response->content()->type();
				switch($type) {
					case \SpringDvs\ContentResponse::Network:
						return $response->content()->content()->nodes();
					case \SpringDvs\ContentResponse::NodeInfo:
						return array($response->content()->content());
					default:
						return false;
				}

	}

	/**
	 * Perform a request and accept first response
	 *
	 *  This method takes an array of potential target nodes
	 *  and if the request fails, it moves onto the next one.
	 *
	 *  If there is no valid response then the entire method
	 *  fails by returning null.
	 *
	 * @param \SpringDvs\Message $msg
	 * @param array $nodes
	 * @return \SpringDvs\Message on success
	 */
	public static function outboundFirstResponse(\SpringDvs\Message $msg, array $nodes) {

		foreach($nodes as $node) {
			$response = HttpService::dvspRequest($node->host(), $msg);

			if($response === false
					|| $response->cmd() != \SpringDvs\CmdType::Response
					|| $response->content()->code() != \SpringDvs\ProtocolResponse::Ok) {
						continue;
					}
					return $response;
		}

		return null;
	}

	/**
	 * Perform a request on a given URI and take the first response
	 *
	 * This method combines the resolution and request. If any fail it returns
	 * false otherwise it returns a message
	 *
	 * @param String $uri The URI to make the request
	 * @param \SpringDvs\Message $msg The message to send
	 * @return \SpringDvs\Message
	 */
	public static function requestUriFirstResponse($uri, \SpringDvs\Message $msg) {
		$nodes = GatewayHandler::resolveUri($uri);
		if(!$nodes) return false;

		return GatewayHandler::outboundFirstResponse($msg, $nodes);
	}

	/**
	 * Convert a multicast service response to array
	 * @param \SpringDvs\Message $response
	 */
	public static function multicastServiceArray($response) {
		if(!$response) return null;

		try {
			return explode("|", $response->getContentResponse()->getServiceText()->get());
		} catch(\Exception $e) {
			return null;
		}
	}
}