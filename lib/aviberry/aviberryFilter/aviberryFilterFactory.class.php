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
require_once AVIBERRY_PLUGIN_ABSPATH . 'lib/aviberry/aviberryPlugin/aviberryPluginFactory.class.php';
require_once 'aviberryFilter.class.php';



class aviberryFilterFactory{
	/**
	 * @var aviberryPlugin
	 */
	protected static $instance = null;
	
	/**
	 * Factory method. 
	 * 
	 * @param string $WPVersion
	 * 
	 * @return aviberryFilter object aviberryFilter the appropriate version of WP in the input.
	 */
	public static function create($WPVersion) {
		
		if(!self::$instance){
			if( aviberryPluginFactory::isWordpressVersion('>=', '3.5'))
				self::$instance = new aviberryFilter($WPVersion);
			
			elseif(aviberryPluginFactory::isWordpressVersion('<', '3.0'))
				self::$instance = new aviberryFilterLess3_0($WPVersion);
				
			else
				self::$instance = new aviberryFilter3_0__3_4_2($WPVersion);
		}
			
		return self::$instance;
	}
}

?>
