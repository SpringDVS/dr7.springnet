<?php
global $Gcm__;
$Gcm__ = array(
	'SpringDvs\CmdType'=>'src/CmdType.php',
	'SpringDvs\ContentInfoRequest'=>'src/ContentInfoRequest.php',
	'SpringDvs\ContentNodeSingle'=>'src/ContentNodeSingle.php',
	'SpringDvs\ContentRegistration'=>'src/ContentRegistration.php',
	'SpringDvs\ContentResolve'=>'src/ContentResolve.php',
	'SpringDvs\ContentResponse'=>'src/ContentResponse.php',
	'SpringDvs\ContentService'=>'src/ContentService.php',
	'SpringDvs\ContentUpdate'=>'src/ContentUpdate.php',
	'SpringDvs\IEnum'=>'src/IEnum.php',
	'SpringDvs\IJson'=>'src/IJson.php',
	'SpringDvs\INodeNetInterface'=>'src/INodeNetInterface.php',
	'SpringDvs\IProtocolObject'=>'src/IProtocolObject.php',
	'SpringDvs\Message'=>'src/Message.php',
	'SpringDvs\NetworkFmt'=>'src/NetworkFmt.php',
	'SpringDvs\Node'=>'src/Node.php',
	'SpringDvs\NodeDoubleFmt'=>'src/NodeDoubleFmt.php',
	'SpringDvs\NodeInfoFmt'=>'src/NodeInfoFmt.php',
	'SpringDvs\NodeProperty'=>'src/NodeProperty.php',
	'SpringDvs\NodeQuadFmt'=>'src/NodeQuadFmt.php',
	'SpringDvs\NodeRole'=>'src/NodeRole.php',
	'SpringDvs\NodeService'=>'src/NodeService.php',
	'SpringDvs\NodeSingleFmt'=>'src/NodeSingleFmt.php',
	'SpringDvs\NodeState'=>'src/NodeState.php',
	'SpringDvs\NodeTripleFmt'=>'src/NodeTripleFmt.php',
	'SpringDvs\ParseFailure'=>'src/ParseFailure.php',
	'SpringDvs\ProtocolResponse'=>'src/ProtocolResponse.php',
	'SpringDvs\ServiceTextFmt'=>'src/ServiceTextFmt.php',
	'SpringDvs\Uri'=>'src/Uri.php',
	'SpringDvs\iMetaspace'=>'src/iMetaspace.php',
	'SpringDvs\iNetspace'=>'src/iNetspace.php',
);
spl_autoload_register(function ($class) {
	global $Gcm__;
	if(!isset($Gcm__[$class])) return;

	include SPRINGNET_DIR.'/netlib/' . $Gcm__[$class];
	
});
