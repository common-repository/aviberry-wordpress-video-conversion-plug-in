<?php
/**
 * @copyright 2012 Movavi (email : support@movavi.com)
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2
 * @link http://movavi.com
 * @link http://www.aviberry.com
 * 
 * @package aviberryPlugin
 */

//
// Requirements
//
require_once AVIBERRY_PLUGIN_ABSPATH . 'lib/aviberry/aviberryPlugin/aviberryPluginFactory.class.php';



class aviberryAction {
	
	/**
	 * @var aviberryPlugin
	 */
	protected $aviberryPlugin = null;
	
	
	
	/**
	 * Constructor
	 * 
	 * @param string $WPVersion
	 * @param string $pagenow global variable WP 
	 */
	public function __construct($WPVersion, $pagenow){
		
		$this->aviberryPlugin = aviberryPluginFactory::create($WPVersion);
		
		// Admin
		add_action('admin_init',			array($this, 'action_admin_init'));
		add_action('admin_menu',			array($this, 'action_admin_menu'));
		add_action('admin_print_styles',	array($this, 'action_admin_print_styles'));
		add_action('admin_footer',			array($this, 'action_admin_footer'));
		add_action('delete_attachment',		array($this, 'action_delete_attachment'));

		// "Media" page. (media-upload.php)
		add_action('admin_print_scripts-upload.php', array($this, 'action_admin_print_scripts_upload_php'));
		add_action('admin_head-upload.php',          array($this, 'action_admin_head'));

		// WP
		add_action('init',					array($this, 'action_init'));
		add_action('wp_print_styles',		array($this, 'action_wp_print_styles'));
		add_action('wp_enqueue_scripts',	array($this, 'action_wp_enqueue_scripts'));
		add_action('wp_head',				array($this, 'action_wp_head'));
	}
	
	
	/*
	 * Actions
	 */

	/**
	 * action_admin_init
	 */
	public function action_admin_init(){

		// Register our styles.
		wp_register_style('aviberry',			AVIBERRY_PLUGIN_URL . 'css/aviberry/aviberry.css');
		wp_register_style('aviberry-tooltip',	AVIBERRY_PLUGIN_URL . 'css/jquery/smoothness/jquery.tooltip.css');
		wp_register_style('aviberry-jquery',	AVIBERRY_PLUGIN_URL . 'css/jquery/smoothness/jquery-ui-1.7.2.custom.css');

		// Register our script with dependencies.

		// In hope that any other software use 'json-rpc' as identifier for 'rpc.js'.  
		wp_register_script('json-rpc',         AVIBERRY_PLUGIN_URL . 'js/json-rpc/rpc.js', array());
		
		// In hope that any other software use 'jquery-ui-progressbar' as identifier for 'ui.progressbar.js'.
		//wp_deregister_script('jquery-ui-progressbar');
		//wp_register_script('jquery-ui-progressbar', AVIBERRY_PLUGIN_URL . 'js/jquery/ui.progressbar.js',      array('jquery-ui-core')); // wp 2.9.2 has no jquery-ui-progressbar

		// In hope that any other software use 'jquery-ui-tooltip' as identifier for 'jquery.tooltip.js'.
		wp_register_script('jquery-ui-tooltip',		AVIBERRY_PLUGIN_URL . 'js/jquery/jquery.tooltip.js',      array());
		wp_register_script('ajax_dialog',			AVIBERRY_PLUGIN_URL . 'js/aviberry/ajax_dialog.js',       array());
		wp_register_script('aviberry-accountInfo',	AVIBERRY_PLUGIN_URL . 'js/aviberry/aviberry-accountInfo.js', array('json-rpc'));
		wp_register_script('aviberry-post',			AVIBERRY_PLUGIN_URL . 'js/aviberry/aviberry-post.js',     array());
		wp_register_script(
			'aviberry-media',
			AVIBERRY_PLUGIN_URL . 'js/aviberry/aviberry-media.js',
			array(
				'json-rpc', 
				'jquery-ui-dialog', 
				'jquery-ui-tooltip', 
				'aviberry-accountInfo'
				/*, 'jquery-ui-progressbar'*/
			)
		);
		wp_register_script(
			'aviberry-settings',
			AVIBERRY_PLUGIN_URL . 'js/aviberry/aviberry-settings.js', 
			array(
				'json-rpc', 
				'jquery-ui-tooltip', 
				'jquery-ui-dialog', 
				'ajax_dialog', 
				'aviberry-accountInfo'
			)
		);
		// Register our options.
		$this->registerOptions();

//		if(isset($_GET['notconfirmed'])){
//			add_settings_error('aviberry_storage_type', '', 'Your email is not confirmed. You can <a href=\'/login_api.php?action=request_confirm&username={username}\'> resend confirmation</a> email');
//		}
	}
	
	
	/**
	 * action_admin_menu
	 */
	public function action_admin_menu(){
		
		// Menu global "Aviberry Plugin"
		add_menu_page(
			__('Aviberry Plugin'), 
			__('Aviberry Plugin'), 
			'manage_options', 
			aviberryPlugin::MENU_SLUG_SETTINGS, 
			array($this, 'aviberry_add_options_page'),
			plugins_url(AVIBERRY_PLUGIN_DIR . '/img/aviberry.ico')
		);
		
		/*
		 * get_plugin_page_hook
		 * 
		 * @param string $plugin_page
		 * @param string $parent_page 
		 */
		$page_hook = get_plugin_page_hook(
			aviberryPlugin::MENU_SLUG_SETTINGS, 
			aviberryPlugin::MENU_SLUG_SETTINGS
		);
		
		// Hook admin scripts loading only for required pages.
		
		// "Settings" page.
		add_action('admin_print_scripts-' . $page_hook, array($this, 'action_admin_print_scripts_settings'));
		add_action('admin_head-'          . $page_hook, array($this, 'action_admin_head'));
	}
	
	
	/**
	 * action_admin_head
	 */
	public function action_admin_head() {
		require(AVIBERRY_PLUGIN_ABSPATH . 'tpl/admin_head.php');
	}
	
	
	/**
	 * action_admin_footer
	 */
	public function action_admin_footer() { 
		require(AVIBERRY_PLUGIN_ABSPATH . 'tpl/preset_dialogs.php'); 
	}
	

	/**
	 * action_admin_print_scripts_settings
	 * The "Settings" page.
	 */
	public function action_admin_print_scripts_settings() {
		wp_enqueue_script('aviberry-settings');
	}
	
	/**
	 * action_admin_print_scripts_upload_php
	 */
	public function action_admin_print_scripts_upload_php() {
		wp_enqueue_script('aviberry-media');
	}
	
	
	/**
	 * action_admin_print_styles
	 */
	public function action_admin_print_styles(){
		wp_enqueue_style('aviberry');
		wp_enqueue_style('aviberry-tooltip');
		wp_enqueue_style('aviberry-jquery');
	}
	
	/**
	 * action_delete_attachment
	 * 
	 * @param integer $attachment_id The ID of the attachment being deleted
	 */
	public function action_delete_attachment($attachment_id){
		if($file = get_attached_file($attachment_id)){
			@unlink(WP_CONTENT_DIR . '/uploads' . $file . '.jpg');
			@unlink($file . '.jpg');
		}
	}
	
	
	// WP
	
	/**
	 * action_init
	 */
	public function action_init(){
		// Register our styles.
		wp_register_style('aviberry', AVIBERRY_PLUGIN_URL . 'css/aviberry/aviberry.css');
		
		if( $this->aviberryPlugin->getOption('aviberry_use_player') && 
			$this->aviberryPlugin->getOption('aviberry_player_embedding_type') == 'javascript'
		)
			wp_register_script(
				'aviberry-player', 
				AVIBERRY_PLUGIN_URL . 'js/aviberry/aviberry-player.js', 
				array(
					'swfobject', 
					'jquery-ui-core'
				)
			);
	}
	
	
	/**
	 * action_wp_print_styles
	 */
	public function action_wp_print_styles(){
		wp_enqueue_style('aviberry');
	}
	
	
	/**
	 * action_wp_enqueue_scripts
	 */
	public function action_wp_enqueue_scripts(){
		wp_enqueue_script('aviberry-player');
	}
	
	
	/**
	 * action_wp_head
	 */
	public function action_wp_head(){
		require(AVIBERRY_PLUGIN_ABSPATH . 'tpl/wp_head.php');
	}
	
	
	/**
	 * aviberry_add_options_page
	 */
	public function aviberry_add_options_page() { 
		
		// Hack. Wordpress does not support error reporting on custom pages administration panel.
		// Since WordPress 3.0
		// todo: move this into abstaraction
		if ( function_exists('settings_errors') )
			settings_errors();
		
		require_once(AVIBERRY_PLUGIN_ABSPATH . 'tpl/options_page.php');
	}
	
	
	/**
	 * registerOptions 
	 */
	private function registerOptions() {
		// Register plugin options.
		register_setting('aviberry-options-group', 'aviberry_api_host', array($this, 'sanitizeAPIHost'));
		register_setting('aviberry-options-group', 'aviberry_api_key');
		register_setting('aviberry-options-group', 'aviberry_api_pass');
		register_setting('aviberry-options-group', 'aviberry_storage_type');
		register_setting('aviberry-options-group', 'aviberry_storage_user');
		register_setting('aviberry-options-group', 'aviberry_storage_pass');
		register_setting('aviberry-options-group', 'aviberry_storage_host');
		register_setting('aviberry-options-group', 'aviberry_storage_port');
		// Works only since WP 3.0
		// todo: move this into abstaraction
		register_setting('aviberry-options-group', 'aviberry_storage_path', array($this, 'sanitizeStoragePath'));
		register_setting('aviberry-options-group', 'aviberry_display_tooltips');
		register_setting('aviberry-options-group', 'aviberry_use_player');
		register_setting('aviberry-options-group', 'aviberry_player_embedding_type');
		register_setting('aviberry-options-group', 'aviberry_player_width');
		register_setting('aviberry-options-group', 'aviberry_player_height');
		register_setting('aviberry-options-group', 'aviberry_preset_default');
	}
	
	/**
	 * Sanitize API Host
	 * 
	 * @param string $value
	 * @return string
	 */
	public function sanitizeAPIHost($value){

		if(!$value)
			$value = AVIBERRY_API_HOST;
		
		return $value;
	}
	
	/**
	 * Sanitize Storage URL
	 * Works with WordPress versions 3.x and above
	 * 
	 * @param string $value
	 * 
	 * @return string
	 * 
	 * @todo move this into abstaraction
	 */
	public function sanitizeStoragePath($value){

		// not supported WordPress versions 2.x 
		if ( !function_exists('add_settings_error') )
			return $value;
		
		$url = array();
		$url['host'] = $this->aviberryPlugin->getOption('aviberry_storage_host');
		$url['user'] = $this->aviberryPlugin->getOption('aviberry_storage_user');
		$url['pass'] = $this->aviberryPlugin->getOption('aviberry_storage_pass');
		$url['port'] = $this->aviberryPlugin->getOption('aviberry_storage_port');
		$url['path'] = $value;

		switch ($this->aviberryPlugin->getOption('aviberry_storage_type')) {			
			case "wp_media_lib_ftp":
				$url['scheme'] = 'ftp';
				break;
			case "wp_media_lib_ftps":				
				$url['scheme'] = 'ftps';
				break;
			case "ftp":
				$url['scheme'] = 'ftp';
				break;
			case "ftps":
				$url['scheme'] = 'ftps';
				break;
			case "s3":
				$url['scheme'] = 'http';
				$url['host'] = AVIBERRY_S3_HOST;
				break;
		}
		try{
			$this->checkStorageURL($url);
			
		}catch(Exception $e){
			add_settings_error('aviberry_storage_type', $e->getCode(), sprintf('Error accessing file storage. %s', $e->getMessage()));
		}
		return $value;
	}

	/**
	 * Check storage URL
	 * 
	 * @param string $url
	 * @throws Exception
	 */
	private function checkStorageURL($url){
		
		if(empty($url['host']))
			throw new Exception(sprintf('Can\'t resolve host "%s".', $url['host']));
	
		// try to check connection to the hostname or IP
		switch($url['scheme']){

			case 'https':
			case 'http': 

				$host = $url['scheme'] . '://' . $url['host'] . (!empty($url['port'])?':' . $url['port']:'');
				$res = @get_headers($host);
				if(empty($res)){
					$error = $this->aviberryPlugin->errorGetLast();
					$error = preg_replace('/get_headers\(.*\).*?: /', '', $error);
					throw new Exception(sprintf('Unable to connect host "%s". %s', $host, $error));
				}
				break;

			case 'ftp':
			case 'ftps':

				$conn_id = @ftp_connect($url['host'], ($url['port']?$url['port']:21));
				if (!$conn_id){
					$error = $this->aviberryPlugin->errorGetLast();
					$error = preg_replace('/ftp_connect\(.*\).*?: /', '', $error);
				    throw new Exception(sprintf('Unable to connect to "%s:%s". %s', $url['scheme'] . '://' . $url['host'], $url['port']?$url['port']:21, $error));
				}

				if (!@ftp_login($conn_id, $url['user'], $url['pass'])){
				    ftp_close($conn_id);
				    throw new Exception(sprintf('Inavalid login/password for "%s" on "%s".', $url['user'], $url['host']));
				}

				if (!@ftp_chdir($conn_id, $url['path'])){
				    ftp_close($conn_id);
				    throw new Exception(sprintf('Invalid remote path "%s".', $url['scheme'] . '://' . ($url['user']?$url['user'].':'.preg_replace('/./', '*', $url['pass']).'@':'') . $url['host'] . '/' . $url['path'], $url['port']?$url['port']:21));
				} 
				break;
		}
	}
}


/**
 * WP >3.5
 */
class aviberryAction3_5 extends aviberryAction {
	
	/**
	 * Constructor
	 * 
	 * @param string $WPVersion
	 * @param string $pagenow global variable WP 
	 */
	public function __construct($WPVersion, $pagenow){
		
		call_user_func_array(array(parent, '__construct'), func_get_args());

		//
		// post.php
		//
		// Media manager has been refactored in WP 3.5 from PHP to JS
		// add JS hack for certain pages
		if(in_array(
				$pagenow, 
				array(
					'index.php',
					'post.php', 
					'post-new.php'
				)
			) 
		){
			add_action('admin_head',	array($this, 'action_admin_head'));
			add_action('admin_footer',	array($this, 'action_admin_print_scripts_post_php'));
		}
	}
	
	/**
	 * action_admin_print_scripts_post_php
	 */
	public function action_admin_print_scripts_post_php() {
		
		wp_enqueue_script(
			'aviberry-post',
			AVIBERRY_PLUGIN_URL . 'js/aviberry/aviberry-post.js',
			array(),
			null,
			true
		);
	}
}


/**
 * WP 2.9.2 - 3.1.2
 */
class aviberryActionLess3_1_3 extends aviberryAction {
	
	/**
	 * Constructor
	 * 
	 * @param string $WPVersion
	 * @param string $pagenow global variable WP 
	 */
	public function __construct($WPVersion, $pagenow){
		
		call_user_func_array(array(parent, '__construct'), func_get_args());
		
		// hack. Wordpress supports protected meta since version 3.1.3.
		add_action('admin_init', array($this, 'postSaveClearConversionMeta'));
	}
	
	/**
	 * hack. Wordpress sends a "meta" to editor post as a fields of form, and then writes the metadata when saving the post.
	 */
	public function postSaveClearConversionMeta(){
		if (
			$_SERVER['REQUEST_METHOD'] = 'POST' &&
			!empty($_POST['action']) &&
			$_POST['action'] == 'editpost' &&
			!empty($_POST['meta'])
		){
			foreach($_POST['meta'] as $id => $meta){
				if(strpos($meta['key'], aviberryPlugin::CONVERSION_META_KEY_PREFIX) === 0)
					unset($_POST['meta'][$id]);
			}

			if(count($_POST['meta']) == 0)
				unset($_POST['meta']);
		}
	}
}

?>
