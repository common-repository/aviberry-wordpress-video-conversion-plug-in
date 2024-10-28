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

require_once 'aviberryShortCode.class.php';



class aviberryShortCodeFactory{
	/**
	 * @var aviberryPlugin
	 */
	protected static $instance = null;
	
	/**
	 * Factory method. 
	 * 
	 * @param string $WPVersion
	 * 
	 * @return aviberryShortCode object aviberryShortCode the appropriate version of WP in the input.
	 */
	public static function create($WPVersion) {
		
		if(!self::$instance)
			self::$instance = new aviberryShortCode($WPVersion);
			
		return self::$instance;
	}
}

?>
