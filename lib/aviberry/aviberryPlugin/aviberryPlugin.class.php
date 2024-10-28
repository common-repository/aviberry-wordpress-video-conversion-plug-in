<?php
/**
 * @copyright 2012 Movavi (email : support@movavi.com)
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2
 * @link http://movavi.com
 * @link http://www.aviberry.com
 * 
 * @package aviberryPlugin
 */
 

// Constants
define('AVIBERRY_PLUGIN_DIR',      basename(realpath(dirname(__FILE__) . '/../../../')));
define('AVIBERRY_PLUGIN_ABSPATH',  WP_PLUGIN_DIR . '/' . AVIBERRY_PLUGIN_DIR . '/');
define('AVIBERRY_PLUGIN_URL',      WP_PLUGIN_URL . '/' . AVIBERRY_PLUGIN_DIR . '/');
define('AVIBERRY_VIDEO_FILEXTS',   '|3gp|3gp2|avi|flv|mov|mp4|mpeg|mpg|wmv|webm|');
define('AVIBERRY_S3_HOST',         's3.amazonaws.com');
define('AVIBERRY_PLUGIN_API_URL',  AVIBERRY_PLUGIN_URL . 'api/');
//define('AVIBERRY_PLUGIN_API_URL',  AVIBERRY_PLUGIN_URL . 'api/?XDEBUG_SESSION_START=netbeans-shirjaev');

define('AVIBERRY_API_HOST', 'www.aviberry.com');
define('AVIBERRY_API_VERSION', 'v1.1.1');
define('AVIBERRY_API_PROTOCOL', 'json'); // json|xml.
define('AVIBERRY_API_URL_AUTHORIZED',  'http://{$api_key}:{$api_pass}@{$api_host}/api/' . AVIBERRY_API_VERSION . '/' . AVIBERRY_API_PROTOCOL . '/');
define('AVIBERRY_API_URL_RESEND_CONFIRM_EMAIL', 'http://' . AVIBERRY_API_HOST . '/login_api.php?action=request_confirm&username={username}');

//Connection timeout for ApiClient. Seconds
define('AVIBERRY_API_CLIENT_CONNECTTIMEOUT', 30);
define('AVIBERRY_API_CLIENT_CONNECTRETRIES', 2);

define('AVIBERRY_WATCH_TIMEOUT', 8000);
define('AVIBERRY_ACCOUNT_INFO_TIMEOUT', 3600000);
//define('AVIBERRY_ACCOUNT_INFO_TIMEOUT', 3000);

// Defaults
define('AVIBERRY_PLAYER_WIDTH',		400);
define('AVIBERRY_PLAYER_HEIGHT',	300);



require_once AVIBERRY_PLUGIN_ABSPATH . 'lib/rpc/ApiException.class.php';
require_once AVIBERRY_PLUGIN_ABSPATH . 'lib/rpc/ApiClient.class.php'; //json



class aviberryPlugin {
	
	const VERSION = '2.4';
	const API_REQUEST_SOURCE = 'wordpress';
	
	const BUY_NOW_URL = 'http://www.aviberry.com/plugin-pricing.html?source=wppanel';
	//const BUY_NOW_URL = 'http://local-aviberry.xxx/cloud-online-converter-pricing.html';
	
	const AVIBERRY_MEMBERS_API_URL_AUTHORIZED = 'http://{$api_key}:{$api_pass}@{$api_host}/members/api/?source=wordpress';
	
	const CONVERSION_META_KEY_PREFIX = '_aviberry_conversion_';
	
	const CONVERSION_STATUS_CONVERTING = 'converting';
	const CONVERSION_STATUS_FINISHED = 'finished';
	
	/**
	 * The slug name to refer to options menu by (should be unique for this menu).
	 * @var string
	 */
	const MENU_SLUG_SETTINGS = 'aviberry_settings';
	
	/**
	 * Option name to save the account information.
	 * @var string
	 */
	const OPTION_NAME_ACCOUNT_INFO = 'aviberry_account_info';
	
	/**
	 * Account payment status paid.
	 * @var string
	 */
	const ACCOUNT_INFO_PAYMENT_STATUS_PAID = 'paid';
	
	
	public $_i18n = null;
	
	private $_isInstallationCompleted = null;
	private $_isAPIDataSet = null;
	private $rpcClient = null;
	
		
		
	/**
	 * init
	 */
	public function init() {
		
		// lines below are used for internationalization JavaScript
		if (is_null($this->_i18n))
			$this->_i18n = array (
				'ok'									=> __('Ok'), 
				'cancel'								=> __('Cancel'), 
				'convert'								=> __('Convert'), 
				'settingsAviberry'						=> __('Aviberry Settings'), 
				'user'									=> __('User'), 
				'pass'									=> __('Password'), 
				'path'									=> __('Path'), 
				'awsKey'								=> __('AWS key'), 
				'awsPass'								=> __('AWS password'), 
				'awsBucket'								=> __('AWS bucket'), 
				'conversionProgress'					=> __('Conversion<br/>progress'), 
				'cancelPostConversion'					=> __('Cancel conversion of \"{$filename}\"?'), // Escape " for JS 
				'startPostConversion'					=> __('Please wait...'), 
				'initPostConversion'					=> __('Connecting to service...'), 
				'h'										=> __('h.'), 
				'm'										=> __('min.'), 
				's'										=> __('sec.'), 
				'less_min'								=> __('less than a minute'), 
				'few_secs'								=> __('a few seconds'), 
				'tooltipConvert'						=> __('Convert file with Aviberry'), 
				'tooltipCancel'							=> __('Cancel file conversion'), 
				'tooltipNci'							=> __('Please, register on Aviberry.com and specify your Aviberry API key and password on the plugin\'s setting page.'), // Not completed installation. 
				'progressDownloading'					=> __('Downloading...'), 
				'progressUploading'						=> __('Uploading...'), 
				'progressQueued'						=> __('Queued...'), 
				'progressConverting'					=> __('Converting...'), 
				'progressCanceling'						=> __('Canceling...'), 
				'timeLeft'								=> __('{$time} left'), 
				'epicWin'								=> __('Successfully completed'),
				'pleaseReloadThePage'					=> __('Please reload webpage to see the converted file'),
				'epicFail'								=> __('Unable to convert the file'), 
				'examplePath'							=> __('path/to/target/dir/'), 
				'examplePathToWordpressMediaLibrary'	=> __('path/to/WordPress/wp-content/uploads/'), 
				'exampleBacket'							=> __('bucket_name/path/to/target/dir/'), 
				'labelStoragePath'						=> __('Path'), 
				'labelPathWordpressMediaLibrary'		=> __('Path to WordPress media library'), 
				'conversionCanceled'					=> __('Canceled'),
				'convertWithAviberry'					=> __('Convert with Aviberry'),
				'attachmentNotFound'					=> __('Cannot find the attachment \'%s\''),
				'progressFailed'						=> __('Failed to get progress'),
				'typeMediaLibraryDescription'			=> __('The conversion result will be stored in the User\'s Media Library.<br>FTP/FTPS settings should be adjusted to access user\'s Media Library.'),
				'typeNotMediaLibraryDescription'		=> __('The conversion result will not be stored in Media Library.<br>The conversion cannot be used when creating a post.'),
				'signInEmpty'							=> __('You should fill in all required fields'),
				'errorAgreePolice'						=> __('You should accept the Privacy Policy and the Terms of Use'),
				'errorEmptyEmailAndPassword'			=> __('The Email and Password fields cannot be empty'),
				'errorIncorrectEmail'					=> __('Email you entered is incorrect'),
			);
	}
	
	
	/**
	 * i18n
	 */
	public function i18n($key) {
		$this->init();
		
		if (!array_key_exists($key, $this->_i18n))
			throw new aviberryPluginException(sprintf('Unknown internationalization key "%s".', $key));
		
		return $this->_i18n[$key];
	}
	
	
	/**
	 * getPluginAPIURLAuthorized
	 * 
	 * @param string $APIURL
	 * @param string $login
	 * @param string $password
	 * 
	 * @return string 
	 */
	public function getPluginAPIURLAuthorized($APIURL, $login = false, $password = false){
		$result = $APIURL;
		
		if(	$login && $password)
			$result = 
				str_replace(
					array(
						'http://',
						'https://',
					),
					array(
						sprintf('http://%s:%s@', rawurlencode($login), rawurlencode($password)),
						sprintf('https://%s:%s@', rawurlencode($login), rawurlencode($password)),
					),
					$result
				);
		
		return $result;
	}
		
	
	/**
	 * getRPCClient
	 * 
	 * @return ApiClient 
	 * 
	 * @throws aviberryPluginException
	 */
	public function getRPCClient(){
		
		if(!$this->rpcClient)
			if(!$this->isAPIDataSet())
				throw new aviberryPluginException('The plugin was not configured properly.');
			else 
				$this->rpcClient = new ApiClient(
					str_replace(
						array(
							'{$api_key}',
							'{$api_pass}',
							'{$api_host}'
						),
						array(
							$this->getOption('aviberry_api_key'),
							$this->getOption('aviberry_api_pass'),
							$this->getOption('aviberry_api_host')
						),
						AVIBERRY_API_URL_AUTHORIZED
					),
					false,
					false,
					AVIBERRY_API_CLIENT_CONNECTTIMEOUT,
					AVIBERRY_API_CLIENT_CONNECTRETRIES
				);
		
		return $this->rpcClient;
	}
	
	/**
	 * getRPCClientMembers
	 * 
	 * @param string $login
	 * @param string $password
	 * @param boolean $sendSource
	 * 
	 * @return ApiClient 
	 * 
	 * @throws aviberryPluginException
	 */
	public function getRPCClientMembers($login, $password, $sendSource = true){
		
		if(empty($login))
			throw new aviberryPluginException('The login is empty.');
		
		if(empty($password))
			throw new aviberryPluginException('The password is empty.');
		
		$host = $this->getOption('aviberry_api_host');
		if(!$host)
			$host = AVIBERRY_API_HOST;
		
		return new ApiClient(
			str_replace(
				array(
					'{$api_key}',
					'{$api_pass}',
					'{$api_host}'
				),
				array(
					rawurlencode($login),
					rawurlencode($password),
					$host
				),
				$sendSource ? self::AVIBERRY_MEMBERS_API_URL_AUTHORIZED : str_replace('?source=wordpress', '', self::AVIBERRY_MEMBERS_API_URL_AUTHORIZED)
			),
			false,
			false,
			AVIBERRY_API_CLIENT_CONNECTTIMEOUT,
			AVIBERRY_API_CLIENT_CONNECTRETRIES
		);
	}
	
	/**
	 * Returns "true" if required info for API access is filled.
	 * 
	 * @return bool
	 */
	public function isAPIDataSet() {
		if (is_null($this->_isAPIDataSet))
			$this->_isAPIDataSet = 
				$this->getOption('aviberry_api_host') &&
				$this->getOption('aviberry_api_key') &&
				$this->getOption('aviberry_api_pass');
				
		return $this->_isAPIDataSet;
	}
	
	/**
	 * Returns "true" if required info for plugin is filled.
	 * 
	 * @return bool
	 */
	public function isInstallationCompleted() {
		if (is_null($this->_isInstallationCompleted))
			$this->_isInstallationCompleted = 
				$this->isAPIDataSet() &&
				$this->getOption('aviberry_preset_default') &&
				$this->isFileStorageSpecified();
				
		return $this->_isInstallationCompleted;
	}
	
	/**
	 * Returns "true" if info about storage is filled.
	 * 
	 * @return bool
	 */
	public function isFileStorageSpecified() {
		return 
			   $this->getOption('aviberry_storage_user')
			&& $this->getOption('aviberry_storage_pass')
			&& $this->getOption('aviberry_storage_host');
	}
	
	/**
	 * Returns plugin option.
	 * 
	 * @param string $option
	 * 
	 * @return string
	 */
	public function getOption($option) {
		$default = false;
		
		$constant = strtoupper($option);
		if (defined($constant))
			$default = constant($constant);
		
		$result = get_option($option, $default);
		
		// set deafult value for certain empty options
		if(!$result && 
			$default &&
			in_array(
				$option, 
				array(
					'aviberry_player_width', 
					'aviberry_player_height'
				)
			)
		)
			$result = $default;
		
		return $result;
	}
	
	/**
	 * Built and returns storage URL.
	 *
	 * @return string
	 */
	public function getStorageUrl() {
		$result = '';
		
		$type = $this->getOption('aviberry_storage_type');
		$host = $this->getOption('aviberry_storage_host');
		$user = $this->getOption('aviberry_storage_user');
		$pass = $this->getOption('aviberry_storage_pass');
		$port = $this->getOption('aviberry_storage_port');
		
		$bucket = '';
		
		$path   = $this->getOption('aviberry_storage_path');
		
		// Normalize path.
		$path = array_diff(explode('/', $path), array(''));
		$path = implode('/', $path);
		
		$user = rawurlencode($user);
		$pass = rawurlencode($pass);
		
		//Determine scheme
		switch ($type) {
			case 'wp_media_lib_ftp': 
				$scheme = 'ftp://'; 
				break;
			
			case 'wp_media_lib_ftps': 
				$scheme = 'ftps://';
				break;
			
			case 'ftp':  
				$scheme = 'ftp://'; 
				break;
				
			case 'ftps': 
				$scheme = 'ftps://';
				break;
				
			case 's3':  
				$scheme = 'http://';
				break;
				
			default:     
				$scheme = 'http://';
				break;
		}
		
		//Determine specific URL parameters
		switch ($type) {
			case 'wp_media_lib_ftps':
			case 'wp_media_lib_ftp': 				
				/**
				* path for media library must be added
				* with wordpress upload subdir
				*/
				$upload_dir = wp_upload_dir();				
				$path .= $upload_dir['subdir'];				
				
				break;
				
			case 's3':  
				$pos = strpos($path, '/');
				if ($pos !== false) {
					$bucket = substr($path, 0, $pos);
					$path   = substr($path, $pos + 1);
				} else {
					$bucket = $path;
					$path   = '';
				}
				
				break;
		}
		
		$result =
			  $scheme
			. ($user ? ($user . ($pass ? ':' . $pass : '')) . '@' : '')
			. ($bucket ? $bucket . '.' : '')
			. $host	. ($port ? ':' . $port : '') . '/'
			. ($path ? $path . '/' : '');
			
		return $result;
	}
	
	
	/**
	 * Returns the ID of the first conversion of the post
	 * 
	 * @return string|false conversion_id
	 */
	public function getPostConversionId($post_id) {
		
		$conversion_id = false;
		foreach(get_post_meta($post_id, '') as $key => $value){	// '' - compatibility with 2.9.2
			if(	strpos($key, self::CONVERSION_META_KEY_PREFIX) !== false){
				$conversion_id = str_replace(self::CONVERSION_META_KEY_PREFIX, '', $key);
				break;
			}
		}
		
		return $conversion_id;
	}
	
	
	/**
	 * startPostConversion
	 * 
	 * @param integer $post_id
	 * @param string $conversion_id
	 * 
	 * @return mixed Returns meta_id if the meta doesn't exist, otherwise returns true on success and false on failure
	 */
	public function startPostConversion($post_id, $conversion_id) {
		return 
			$this->setPostConversion(
				$post_id, 
				$conversion_id, 
				array(
					'status' => self::CONVERSION_STATUS_CONVERTING,
					'time_created' => time()
				)
			);
	}
	
	
	/**
	 * finishPostConversion
	 * 
	 * @param integer $post_id
	 * @param string $conversion_id (false)
	 * @param integer $error_code (0)
	 * @param string $error_message ('')
	 * @param integer $attach_id (false) The ID of new attachment
	 * 
	 * @return mixed Returns meta_id if the meta doesn't exist, otherwise returns true on success and false on failure
	 */
	public function finishPostConversion($post_id, $conversion_id, $error_code = 0, $error_message = '', $attach_id = false) {
		return 
			$this->setPostConversion(
				$post_id, 
				$conversion_id, 
				array(
					'status' => self::CONVERSION_STATUS_FINISHED,
					'error_code' => $error_code, 
					'error_message' => $error_message,
					'attach_id' => $attach_id
				)
			);
	}
	
	
	/**
	 * getPostConversion
	 * 
	 * @param integer $post_id
	 * @param string $conversion_id
	 * 
	 * @return array|false $conversion
	 */
	public function getPostConversion($post_id, $conversion_id) {
		return 
			maybe_unserialize(
				get_post_meta($post_id, self::CONVERSION_META_KEY_PREFIX . $conversion_id, true)
			);
	}
	
	
	/**
	 * setPostConversion
	 * 
	 * @param integer $post_id
	 * @param string $conversion_id
	 * @param array $conversion
	 * 
	 * @return mixed Returns meta_id if the meta doesn't exist, otherwise returns true on success and false on failure
	 */
	private function setPostConversion($post_id, $conversion_id, $conversion) {
		return update_post_meta($post_id, self::CONVERSION_META_KEY_PREFIX . $conversion_id, maybe_serialize($conversion));
	}
	
	
	/**
	 * deletePostConversion
	 * 
	 * @param integer $post_id
	 * @param string $conversion_id (false)
	 * 
	 * @return boolean success
	 */
	public function deletePostConversion($post_id, $conversion_id = false) {
		
		$result = true;
		
		// if conversion id was not set then delete all meta conversion
		if(!$conversion_id){
			$conversion_id = false;
			foreach(get_post_meta($post_id, '') as $key => $value){ // '' - compatibility with 2.9.2
				if(	strpos($key, self::CONVERSION_META_KEY_PREFIX) !== false)
					$result = $result && delete_post_meta($post_id, $key);
			}
			
		} else
			$result = delete_post_meta($post_id, self::CONVERSION_META_KEY_PREFIX . $conversion_id);
		
		return $result;
	}
	
	
	/**
	 * startConversion
	 * 
	 * @param string $source_url
	 * @param array $preset
	 * @param array $data
	 * @global string $wp_version
	 * 
	 * @return type 
	 */
	public function startConversion(
		$source_url, 
		$preset,
		$data
	) {
		global $wp_version;
		
		if(!isset($data['post_id']))
			throw new aviberryPluginException('The post ID is not defined.');
		
		$data['api_request_source'] = self::API_REQUEST_SOURCE;
		$data['aviberry_version_cms'] = $wp_version;
		$data['aviberry_version_plugin'] = self::VERSION;
		
		usleep(1);
		$target_url = $this->getStorageUrl() . (int)round(microtime(true) * 1000000);
		
		// Callback
		$callback = array(
			'url' => $this->getPluginAPIURLAuthorized(
				AVIBERRY_PLUGIN_API_URL,
				$this->getOption('aviberry_api_key'), 
				$this->getOption('aviberry_api_pass')
			),
			'method' => 'register_files',
			'protocol' => 'json',
			'call_on_cancel' => true
		);

		$method_params = array(
			'source_url' => $source_url,
			'target_url' => $target_url,
			'preset' => $preset,		
			'callback' => $callback,
			'params' => array(
				'target_filename_policy' => 'OVERWRITE_EXTENSION'
			),
			'data' => $data
		);
		
		//Call aviberry API
		$result = $this->getRPCClient()->startConversion($method_params);		
		$conversion_id = $result['conversion_id'];
		
		$this->startPostConversion($data['post_id'], $conversion_id);
		
		return $result;
	}
	
	
	/**
	 * cancelConversion
	 * 
	 * @param string $conversion_id
	 * 
	 * @return mixed 
	 */
	public function cancelConversion($conversion_id) {	
		return $this->getRPCClient()->cancelConversion(
			array(
				'conversion_id' => $conversion_id
			)
		);
	}


	/**
	 * getProgress
	 * 
	 * @param string $conversion_id
	 * 
	 * @return mixed 
	 */
	public function getProgress($conversion_id) {
		return $this->getRPCClient()->getProgress(
			array(
				'conversion_id' => $conversion_id
			)
		);
	}
	
	
	/**
	 * getAccountInfo
	 * 
	 * @return array
	 */
	public function getAccountInfo() {
		$result = $this->getRPCClient()->getAccountInfo();
		
		$this->setAccountInfoDB($result);
		
		return $result;
	}
	
	/**
	 * getAccountInfoDB
	 * 
	 * @return array account info
	 */
	public function getAccountInfoDB() {
		return 
			maybe_unserialize(
				get_option(self::OPTION_NAME_ACCOUNT_INFO)
			);
	}
	/**
	 * setAccountInfoDB
	 * 
	 * @param array $info
	 * 
	 * @return boolean True if option value has changed, false if not or if update failed.
	 */
	public function setAccountInfoDB($info) {
		return update_option(self::OPTION_NAME_ACCOUNT_INFO, maybe_serialize($info));
	}
	
	/**
	 * getAPICredentials
	 * 
	 * @param string $login
	 * @param string $password
	 * @param boolean $sendSource
	 * 
	 * @return array
	 */
	public function getAPICredentials($login, $password, $sendSource = true){
		
		$credentials = $this->getRPCClientMembers($login, $password, $sendSource)->getAPICredentials();
		
		// if there are no credentials then show error and exit
		if(	empty($credentials) || 
			empty($credentials['api_key']) || 
			empty($credentials['api_pass'])
		)
			throw new aviberryPluginException(__('Cannot get API credentials. Try to get them at your Aviberry account manually.'));
		
		update_option('aviberry_api_key', $credentials['api_key']);
		update_option('aviberry_api_pass', $credentials['api_pass']);
		
		return $credentials;
	}
	
	/**
	 * setAPICredentialsDB
	 * 
	 * @param string $host
	 * @param string $key
	 * @param string $password
	 */
	public function setAPICredentialsDB($host, $key, $password){
		// if no credentials passed then show error and exit
		if(	empty($host) )
			throw new aviberryPluginException(__('API host cannot be empty.'));
		
		if(	empty($key) )
			throw new aviberryPluginException(__('API key cannot be empty.'));
		
		if(	empty($password) )
			throw new aviberryPluginException(__('API password cannot be empty.'));
		
		update_option('aviberry_api_host', $host);
		update_option('aviberry_api_key', $key);
		update_option('aviberry_api_pass', $password);
	}
	
	
	/**
	 * Convertion end callback 
	 *
	 * @param array $conversion
	 *
	 * @return boolean success
	 */
	public function registerFiles($conversion) {
		
		$attach_id = false;

		// hack.
		if($conversion['status'] == 'canceled'){
			$conversion['error_code'] = -1;
			$conversion['error_message'] = __('The conversion has been canceled.');
		}
			
		//if success then register file in media library
		if(!$conversion['error_code']){

			$attach_id = $this->registerMediaLibraryByURL(
				$this->getOption('aviberry_storage_type'), 
				$this->getOption('aviberry_storage_path'), 
				$conversion['target_url'][0][0],	// ftp://user:password@host/www/wordpress.xxx/wp-content/uploads/2011/10/2_1317794798.flv
				$conversion['data']['title'],
				isset($conversion['data']['parent_post_id']) ? $conversion['data']['parent_post_id'] : 0
			);
		}

		// update post meta
		$this->finishPostConversion(
			$conversion['data']['post_id'], 
			$conversion['conversion_id'],
			$conversion['error_code'],
			$conversion['error_message'],
			$attach_id
		);

		return true;
	}
	
	
	/**
	 * Register file in media library by URL
	 * 
	 * @param string $storageType storage type
	 * @param string $storagePath path to media library
	 * @param string $url File URL to register.		<br>
	 *			ftp://user:password@host/www/wordpress.xxx/wp-content/uploads/2011/10/2_1317794798.flv
	 * @param string $title File title ('')
	 * @param integer $parent_post_id Attachments are associated with parent posts. This is the ID of the parent's post ID (0)
	 * 
	 * @return integer|false The new attachment id
	 */
	private function registerMediaLibraryByURL($storageType, $storagePath, $url, $title = '', $parent_post_id = 0){
		
		switch ($storageType) {
			//register file at media library
			case 'wp_media_lib_ftp':
			case 'wp_media_lib_ftps':
				// you must first include the image.php file
				// for the function wp_generate_attachment_metadata() to work
				require_once(ABSPATH . "wp-admin" . '/includes/image.php');

				//get upload path to media library
				$path   = $storagePath; // www/wordpress.xxx/wp-content/uploads/
				// Normalize path.
				$path = array_diff(explode('/', $path), array('')); // www/wordpress.xxx/wp-content/uploads
				$path = implode('/', $path); // www/wordpress.xxx/wp-content/uploads

				if(empty($path))
					return false;

				//get the upload subdir was
												// ftp://user:password@host/www/wordpress.xxx/wp-content/uploads/2011/10/2_1317794798.flv
				$subPath = explode($path, $url);// ftp://user:password@host/									/2011/10/2_1317794798.flv
				if(count($subPath) < 2)
					return false;
				$subPath = $subPath[count($subPath) - 1]; // /2011/10/2_1317794798.flv
				
				$filename = rawurldecode(basename($subPath)); // 2_1317794798.flv
				$wp_filetype = wp_check_filetype($filename, null);
				
				/*
				returns something like the following (if successful)
				Array ( 
					[path] => C:\path\to\wordpress\wp-content\uploads\2010\05 
					[url] => http://example.com/wp-content/uploads/2010/05 
					[subdir] => /2010/05 
					[basedir] => C:\path\to\wordpress\wp-content\uploads 
					[baseurl] => http://example.com/wp-content/uploads 
					[error] => 
				)*/
				$wp_upload_dir = wp_upload_dir();
				
				$attachment = array(
					// necessary to properly display the file name
					'guid' => $wp_upload_dir['baseurl'] . $subPath, // http://example.com/wp-content/uploads/2011/10/2_1317794798.flv
					'post_mime_type' => $wp_filetype['type'],
					'post_title' => $title,
					'post_content' => '',
					'post_status' => 'inherit'
				);

				//
				// @param array $attachment Array of data about the attachment that will be written into the wp_posts table of the database. 
				//		Must contain at a minimum the keys post_title, post_content (the value for this key should be the empty string), 
				//		post_status and post_mime_type.
				//		
				// @param string $filename (optional) Location of the file on the server.
				// 		Use absolute path and not the URI of the file.
				// 		The file MUST be on the uploads directory
				//		WORDPRESS_DIR + $subPath
				//		
				// $parent_post_id - (int) (optional) Attachments are associated with parent posts. This is the ID of the parent's post ID.
				//
				$attach_id = wp_insert_attachment($attachment, $subPath, $parent_post_id); // /2011/10/2_1317794798.flv
				$attach_data = wp_generate_attachment_metadata($attach_id, $filename);
				wp_update_attachment_metadata($attach_id,  $attach_data);
				
				return $attach_id;

				break;
		}
		return false;
	}
	
		
	/**
	 * getPlayerEmbeddingCode
	 * 
	 * @param string $title
	 * @param string $href
	 * @param integer $mediaID
	 * @param integer $width (optional)
	 * @param integer $height (optional)
	 * 
	 * @return string
	 */
	public function getPlayerEmbeddingCode($title, $url, $mediaID, $width = false, $height = false){
		
		$width || ($width = $this->getOption('aviberry_player_width'));
		$height || ($height = $this->getOption('aviberry_player_height'));
		
		$result = '';
		
		switch ($this->getOption('aviberry_player_embedding_type')){
			case 'shortcode':
				$result   = "[aviberry_player title=\"$title\" href=\"$url\" width=\"$width\" height=\"$height\" id=\"$mediaID\"]";
				break;

			default: // By default as 'javascript'
				$title = esc_html($title); 
				$url  = esc_attr($url);
				$result  = "<a class=\"aviberry-player\" id=\"media-$mediaID\" width=\"$width\" height=\"$height\" href=\"$url\">$title</a>";
				break;
		}
		
		return $result;
	}
	
	
	/**
	 * Return URL for plugin settings.
	 * 
	 * @return string URL
	 */
	public function getPluginSettingsURL(){
		return
			$this->admin_url('admin.php?page=' . self::MENU_SLUG_SETTINGS);
	}
	
	
	/**
	 * Abstarction of admin_url function for WP < 3.0
	 * 
	 * @param string $path 'edit-tags.php?taxonomy=category'
	 * 
	 * @return string URL
	 * 
	 * @todo move this into abstaraction ?
	 */
	private function admin_url($path = ''){
		return 
			function_exists('admin_url') ? 
				admin_url($path) :
				site_url() . 'wp-admin/' . $path
		;
	}
	
	/**
	 * Get error_get_last() message.
	 * Try to decode message from original charset to UTF-8
	 * 
	 * @return string
	 */
	public function errorGetLast(){
		$error = error_get_last();
		$result = '';
		if(!empty($error['message'])){
			if(!$this->isASCII($error['message']))
				$result = iconv( mb_detect_encoding($error['message']), 'UTF-8//TRANSLIT', $error['message'] );
		}
		return $result;
	}
	
	/**
	 * Check string for ASCII characters
	 * 
	 * @param string $value
	 * @return boolean
	 */
	private function isASCII($value){
		$result = true;
		for ($i = 0; $i < strlen($value); $i++)
			if (ord($value[$i]) > 127) {
				$result = false;
				break;
			}
		return $result;
	}
	
}


class aviberryPluginException extends Exception {}


?>