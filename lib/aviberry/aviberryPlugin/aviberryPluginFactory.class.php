<?php
/**
 * @copyright 2013 Movavi (email : support@movavi.com)
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2
 * @link http://movavi.com
 * @link http://www.aviberry.com
 * 
 * @author Shiryaev Dmitriy <d.shirjaev@movavi.com>
 * @since 2013-02-11 
 * 
 * @package aviberryPlugin
 */

//
// Requirements
//

require_once 'aviberryPlugin.class.php';



class aviberryPluginFactory{
	
	/**
	 * @var aviberryPlugin
	 */
	protected static $instance = null;
	
	/**
	 * Factory method. 
	 * 
	 * @param string $WPVersion
	 * 
	 * @return aviberryPlugin object aviberryPlugin the appropriate version of WP in the input.
	 */
	public static function create($WPVersion) {
		
		if(!self::$instance)
			self::$instance = new aviberryPlugin();
			
		return self::$instance;
	}
	
	/**
	 * Compares Wordpress version with a specified one.
	 *
	 * @global string $wp_version
	 * @param string $operator
	 * @param string $version
	 * 
	 * @return mixed
	 */
	public static function isWordpressVersion($operator, $version) {
		global $wp_version;
		
		return version_compare($wp_version, $version, $operator);
	}
}

?>
