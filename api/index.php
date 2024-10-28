<?php
/*  Copyright 2012 Movavi (email : support@movavi.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

/**
 * @package aviberryPlugin
 * @author Aviberry
 * @link http://www.aviberry.com
 */


// Wordpress functionality.
require_once '../../../../wp-load.php';
// Plugin functionality.
require_once '../lib/aviberry/aviberryPlugin/aviberryPluginFactory.class.php';

// RPC Server functionality.
if (!class_exists('DateTime'))
	require_once AVIBERRY_PLUGIN_ABSPATH . 'lib/DateTime.class.php';
require_once AVIBERRY_PLUGIN_ABSPATH . 'lib/rpc/ApiServer.class.php';



$rpcServer = ApiServer::getInstance(ApiServer::JSON, ApiServer::PUBLIC_VISIBILITY, false);
$aviberryPlugin = aviberryPluginFactory::create($wp_version);


//
// Authentication.
//
if(
	empty($_SERVER['PHP_AUTH_USER']) ||
	empty($_SERVER['PHP_AUTH_PW']) ||
	$_SERVER['PHP_AUTH_USER'] != $aviberryPlugin->getOption('aviberry_api_key') ||
	$_SERVER['PHP_AUTH_PW'] != $aviberryPlugin->getOption('aviberry_api_pass')
){
	header('WWW-Authenticate: Basic realm="Aviberry WordPress Video Conversion Plugin"');
	header('HTTP/1.0 401 Unauthorized');
	
	throw new UnauthorizedAccess_ApiException(false, false, 'Authorization failed.');
}



/**
 * startConversion
 * 
 * @global aviberryPlugin $aviberryPlugin
 * @param integer $post_id
 * @param string $source_url
 * @param array $preset
 * @param array $data
 * 
 * @return array
 */
function startConversion($post_id, $source_url, $preset, $data){
	global $aviberryPlugin;
	
	$aviberryPlugin->deletePostConversion($post_id);
	$data['post_id'] = $post_id;
	
	return 
		$aviberryPlugin->startConversion($source_url, $preset, $data);
}

/**
 * cancelConversion
 * 
 * @global aviberryPlugin $aviberryPlugin
 * @param string $conversion_id
 * 
 * @return boolean
 */
function cancelConversion($conversion_id) {	
	global $aviberryPlugin;
	
	return 
		$aviberryPlugin->cancelConversion($conversion_id);
}

/**
 * getProgress
 * 
 * @global aviberryPlugin $aviberryPlugin
 * @param string $conversion_id
 * 
 * @return array
 */
function getProgress($conversion_id) {
	global $aviberryPlugin;
	
	return 
		$aviberryPlugin->getProgress($conversion_id);
}

/**
 * getAccountInfo
 * 
 * @global aviberryPlugin $aviberryPlugin
 * 
 * @return array
 */
function getAccountInfo() {
	global $aviberryPlugin;
	
	return 
		$aviberryPlugin->getAccountInfo();
}


/**
 * Convertion end callback 
 *
 * @global aviberryPlugin $aviberryPlugin
 * @param array $conversion
 *
 * @return boolean success
 * 
 */
function register_files($conversion) {
	global $aviberryPlugin;
	
	return 
		$aviberryPlugin->registerFiles($conversion);
}


/**
 * finishPostConversion
 * 
 * @global aviberryPlugin $aviberryPlugin
 * @param integer $post_id
 * @param string $conversion_id
 * @param integer $error_code (0)
 * @param string $error_message ('')
 * 
 * @return boolean success
 */
function finishPostConversion($post_id, $conversion_id, $error_code = 0, $error_message = '') {
	global $aviberryPlugin;
	
	return
		$aviberryPlugin->deletePostConversion($post_id, $conversion_id);
}



// Proxy functions.
$rpcServer->addMethod('startConversion');
$rpcServer->addMethod('cancelConversion');
$rpcServer->addMethod('getProgress');
$rpcServer->addMethod('getAccountInfo');
$rpcServer->addMethod('register_files');

// Plugin functions.
$rpcServer->addMethod('finishPostConversion');

$rpcServer->processRequest();
?>
