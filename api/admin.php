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
 * @author Aviberry <support@aviberry.com>
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


if(!current_user_can('install_plugins'))
	throw new Exception('Access denied.');

$aviberryPlugin = aviberryPluginFactory::create($wp_version);


/**
 * getAPICredentials
 * 
 * @global aviberryPlugin $aviberryPlugin
 * @param string $login
 * @param string $password
 * @param boolean $sendSource
 * 
 * @return array
 */
function getAPICredentials($login, $password, $sendSource = true) {
	global $aviberryPlugin;
	
	$credentials = $aviberryPlugin->getAPICredentials($login, $password, $sendSource);
	if(!empty($credentials['account_info']))
		$aviberryPlugin->setAccountInfoDB($credentials['account_info']);
	return $credentials;
}

/**
 * Save API credentials to DB
 * 
 * @global aviberryPlugin $aviberryPlugin
 * @param string $host
 * @param string $key
 * @param string $password
 */
function setAPICredentialsDB($host, $key, $password) {
	global $aviberryPlugin;
	
	$aviberryPlugin->setAPICredentialsDB($host, $key, $password);
}


$rpcServer->addMethod("getAPICredentials");
$rpcServer->addMethod("setAPICredentialsDB");

$rpcServer->processRequest();

?>
