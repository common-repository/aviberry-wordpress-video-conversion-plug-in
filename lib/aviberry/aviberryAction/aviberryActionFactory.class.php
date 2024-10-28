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
require_once 'aviberryAction.class.php';



class aviberryActionFactory{
	/**
	 * @var aviberryPlugin
	 */
	protected static $instance = null;
	
	/**
	 * Factory method. 
	 * 
	 * @param string $WPVersion
	 * @param string $pagenow global variable WP 
	 * 
	 * @return aviberryAction object aviberryAction the appropriate version of WP in the input.
	 */
	public static function create($WPVersion, $pagenow) {
		
		if(!self::$instance){
			
			if( aviberryPluginFactory::isWordpressVersion('>=', '3.5'))
				self::$instance = new aviberryAction3_5($WPVersion, $pagenow);
			
			elseif(aviberryPluginFactory::isWordpressVersion('<', '3.1.3'))
				self::$instance = new aviberryActionLess3_1_3($WPVersion, $pagenow);
			
			else
				self::$instance = new aviberryAction($WPVersion, $pagenow); // 3.1.3 - 3.4.2
		}
			
		return self::$instance;
	}
}

?>
