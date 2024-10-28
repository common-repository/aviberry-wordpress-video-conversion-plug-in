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

//
// Requirements
//
require_once AVIBERRY_PLUGIN_ABSPATH . 'lib/aviberry/aviberryPlugin/aviberryPluginFactory.class.php';
require_once AVIBERRY_PLUGIN_ABSPATH . 'lib/aviberry/aviberryShortCode/aviberryShortCodeFactory.class.php';



class aviberryFilter {
	
	/**
	 * @var aviberryPlugin
	 */
	protected $aviberryPlugin = null;
	/**
	 * @var aviberryShortCode
	 */
	protected $aviberryShortCode = null;
	
	
	
	/**
	 * Constructor
	 * 
	 * @param string $WPVersion
	 */
	public function __construct($WPVersion){
		
		$this->aviberryPlugin = aviberryPluginFactory::create($WPVersion);
		$this->aviberryShortCode = aviberryShortCodeFactory::create($WPVersion);
		
		// Admin
		add_filter('media_row_actions',					array($this, 'filter_media_row_actions'), 10, 2);
		add_filter('pre_option_aviberry_storage_host',	array($this, 'filter_pre_option_aviberry_storage_host'));
		add_filter('media_send_to_editor',				array($this, 'filter_media_send_to_editor'), 10, 3);
		add_filter('content_save_pre',					array($this, 'filter_content_save_pre'));
	}
	
		
	/*
	 * Filters
	 */

	// Admin
	
	/**
	 * filter_media_row_actions
	 */
	public function filter_media_row_actions($actions, $post) {
		// Next line from WordPress sources.
		$file_extension = strtolower(preg_replace('/^.*?\.(\w+)$/', '$1', get_attached_file($post->ID)));
		if (strpos(AVIBERRY_VIDEO_FILEXTS, "|$file_extension|") === false)
			return $actions; // If we don't know what is it, skip.

		$thumbnail_url = false;
		$attachment_url = wp_get_attachment_url($post->ID);
		$attached_file = get_attached_file($post->ID);
		if(!empty($attached_file) && (
				file_exists(WP_CONTENT_DIR . '/uploads' . $attached_file . '.jpg') ||
				file_exists($attached_file . '.jpg')
			)
		)
			$thumbnail_url = $attachment_url . '.jpg';

		//post is converting
		if ($this->aviberryPlugin->getPostConversionId($post->ID))
			// Add "Cancel" action.
			if ($this->aviberryPlugin->isInstallationCompleted())
				$actions['aviberry_convert'] = 
					'<a class="aviberry-conversion-link" href="javascript:void(0)" ' 
						. 'post_id="'       . $post->ID                                      . '" '
						. 'filename="'      . basename($attachment_url)     . '" '
						. 'filelink="'      . $attachment_url               . '" '
						. 'conversion_id="' . $this->aviberryPlugin->getPostConversionId($post->ID) . '" '
						. 'title="'         . $this->aviberryPlugin->i18n('tooltipCancel')          . '" '
						. ($thumbnail_url ? 'thumbnail_url="'       . $thumbnail_url         . '" ' : '')
					. '>'
						. $this->aviberryPlugin->i18n('cancel')
					. '</a>'
				;
			else
				$actions['aviberry_convert'] = 
					'<span class="aviberry-not-completed-installation"' 
						. 'title="' . $this->aviberryPlugin->i18n('tooltipNci') . '" ' 
					. '>' 
						. $this->aviberryPlugin->i18n('cancel')
					. '</span>'
				;

		//post is NOT converting
		else
			// Add "Convert" action.
			if ($this->aviberryPlugin->isInstallationCompleted())
				$actions['aviberry_convert'] =
					'<a class="aviberry-conversion-link" '
						. 'href="javascript:void(0)" '
						. 'post_id="'  . $post->ID                                  . '" '
						. 'filename="' . basename($attachment_url) . '" '
						. 'filelink="' . $attachment_url           . '" '
						. 'title="'    . $this->aviberryPlugin->i18n('tooltipConvert')     . '" '
						. ($thumbnail_url ? 'thumbnail_url="'       . $thumbnail_url         . '" ' : '')
					. '>'
						. $this->aviberryPlugin->i18n('convert')
					. '</a>'
				;
			else
				$actions['aviberry_convert'] =
					'<span class="aviberry-not-completed-installation" '
						. 'title="' . $this->aviberryPlugin->i18n('tooltipNci') . '" '
					. '>'
						. $this->aviberryPlugin->i18n('convert')
					. '</span>'
				;
	
		// Settings link
		if (!$this->aviberryPlugin->isInstallationCompleted()){
			$actions['aviberry_settings'] =
					'<a class="aviberry-settings-link" '
						. 'href="' . $this->aviberryPlugin->getPluginSettingsURL() . '" '
					. '>'
						. $this->aviberryPlugin->i18n('settingsAviberry')
					. '</a>'
				;
		}
			
		return $actions;
	}
	
	
	/**
	 * filter_pre_option_aviberry_storage_host
	 * 
	 * For storage type "s3" host always the same. 
	 * 
	 * @param string $value
	 * @return string
	 */
	public function filter_pre_option_aviberry_storage_host($value) {
		if ($this->aviberryPlugin->getOption('aviberry_storage_type') == 's3')
			$value = AVIBERRY_S3_HOST;

		return $value;
	}
	
	
	/**
	 * filter_media_send_to_editor
	 * 
	 * @param string $html
	 * @param integer $send_id
	 * @param array $attachment
	 * @return string 
	 */
	public function filter_media_send_to_editor($html, $send_id, $attachment) {
		
		// if conversion shortcode
		if(	preg_match(aviberryShortCode::REG_EXP_SHORTCODE_CONVERSION, $attachment['url']) ){
			$html = $attachment['url'];
			
			
		} elseif($this->aviberryPlugin->getOption('aviberry_use_player')){
			
			$html = $this->aviberryPlugin->getPlayerEmbeddingCode(
				$attachment['post_title'], 
				$attachment['url'], 
				$send_id
			);
		}

		return $html;
	}
	
	
	/**
	 * Applied to post content prior to saving it in the database (also used for attachments).
	 * Performs Aviberry shortcode tags.
	 * 
	 * @global type $post
	 * @param string $content Post body
	 * 
	 * @return string
	 * 
	 * @throws aviberryPluginException 
	 */
	public function filter_content_save_pre($content){
		global $post;
		global $post_ID;
		global $action;
		
		// skip saving ajax, skip all actions except "editpost"
		if(	!isset($action) || 
			!in_array(
				$action, 
				array(
					'editpost', 
					'post-quickpress-save',
					'post-quickpress-publish'
				)
			)
		)
			return $content;
		
		// determine the ID of the post
		$postID = get_the_ID();
		if(!$postID)
			$postID = 
				!empty($post) ? 
					$post->ID : 
					(!empty($_POST['post_ID']) ? // WP < 3.5
						$_POST['post_ID'] : 
						(!empty($post_ID) ?  // WP 2.9.2
							$post_ID : 
							false
						)
					);
		
		$configured = $this->aviberryPlugin->isInstallationCompleted();
		if($configured)
			$preset_id = $this->aviberryPlugin->getOption('aviberry_preset_default');
		
		// process shortcodes
		while(
			preg_match(	// find new shortcodes
				aviberryShortCode::REG_EXP_SHORTCODE_CONVERSION_NEW, 
				$content, 
				$matches, 
				PREG_OFFSET_CAPTURE
			)
		){
			set_exception_handler(array($this, 'handleException'));
			
			if(!$postID)
				throw new aviberryPluginException(__('Can not identify publiation ID.'));
			
			// check current user rights
			if ( !current_user_can('upload_files') )
				throw new aviberryPluginException(__('Oops, you are not permitted to convert with Aviberry. Please contact the site administrator.'));
			
			// if installation completed
			if($configured){
				
				//[aviberry_conversion source_url="%s" source_attachment_id="%s" status="%s" conversion_id="%s" error_code="%s" error_message="%s"]				
				/* $matches :
				 * [0]=>
				 *		array(2) {
				 *			[0]=> string(226) "[aviberry_conversion source_attachment_id=\'6\' conversion_id=\'\']"
				 *			[1]=> int(240)
				 *		}
				 */
				
				$source_attachment_id = $matches[1][0];
				$width = $matches[2][0];
				$height = $matches[3][0];
				
				$source_url = wp_get_attachment_url($source_attachment_id);
				
				// if attachment found
				if($attachment = get_post($source_attachment_id)){
					
					//start new conversion
					$result = $this->aviberryPlugin->startConversion(
						$source_url, 
						array(
							'preset_id' => $preset_id
						), 
						array(
							'title' => $attachment->post_title,
							'post_id' => $postID,
							'parent_post_id' => $postID
						)
					);

					$conversion_id = $result['conversion_id'];

					// switch conversion tag to the converting state
					// switching only the current tag from all of these
					$content = substr_replace(
						$content, 
						$this->aviberryShortCode->getShortCodeConversion( // conversion tag
							$source_attachment_id,
							$width,
							$height,
							$conversion_id
						),
						$matches[0][1], 
						strlen($matches[0][0])
					);


				// if attachment NOT found
				} else 
					throw new aviberryPluginException(sprintf($this->aviberryPlugin->i18n('attachmentNotFound'), $source_attachment_id));
				
			
			// if installation is NOT completed
			} else
				throw new aviberryPluginException($this->aviberryPlugin->i18n('tooltipNci'));
		}
		
		// hack. Wordpress calls this filter twice.
		remove_filter('content_save_pre', array($this, 'filter_content_save_pre'));
		
		return $content;
	}
	
	
	/**
	 * Show error message
	 * 
	 * @param type $message
	 * @param type $title 
	 */
	public function showErrorMessage($message, $title = ''){
		wp_die($message, $title, array('back_link' => true) );
	}
	
	
	/**
	 * handleException
	 * 
	 * @param type $e 
	 */
	public function handleException($e){
		$this->showErrorMessage($e->getMessage());
	}
}


/**
 * WP 3.0 - 3.4.2
 */
class aviberryFilter3_0__3_4_2 extends aviberryFilter {
	
	/**
	 * "Convert with Aviberry" button template
	 * 
	 * @deprecated since WP 3.5
	 * @var string 
	 */
	const TEMPLATE_HTML_CONVERT_WITH_AVIBERRY = '<button type="button" class="button urlaviberry" data-link-url="%s" title="%s">%s</button>';
	/**
	 * Regular expression to get attachment_id from attachment fields
	 * 
	 * @deprecated since WP 3.5
	 * @var string 
	 */
	const REG_EXP_ATTACHMENT_FIELDS_TO_EDIT_ATTACHMENT_ID = '/attachment_id=(\d+)/';
	
	
	
	/**
	 * Constructor
	 * 
	 * @param string $WPVersion
	 */
	public function __construct($WPVersion){
		
		call_user_func_array(array(parent, '__construct'), func_get_args());
		
		// Media manager has been refactored in WP 3.5 from PHP to JS
		add_filter('attachment_fields_to_edit',	array($this, 'filter_attachment_fields_to_edit'));
	}
	
	
	/**
	 * Show "Convert with aviberry" button
	 * 	 
	 * @deprecated since WP 3.5
	 * 
	 * @param string $form
	 * @return string 
	 */
	public function filter_attachment_fields_to_edit($form){
		//print_r(func_get_args()); 
		if( isset($form['url']) && 
			!empty($form['url']['html']) &&
			/*
				<input class="text urlfield" name="attachments[29][url]" value="http://local-wordpress.xxx/wp-content/uploads/2012/06/broken.avi" type="text"><br>
				<button type="button" class="button urlnone" data-link-url="">Нет</button>
				<button type="button" class="button urlfile" data-link-url="http://local-wordpress.xxx/wp-content/uploads/2012/06/broken.avi">Ссылка на файл</button>
				<button type="button" class="button urlpost" data-link-url="http://local-wordpress.xxx/?attachment_id=29">Ссылка на страницу вложения</button>
			 */
			preg_match(
				self::REG_EXP_ATTACHMENT_FIELDS_TO_EDIT_ATTACHMENT_ID, 
				$form['url']['html'], 
				$attachmentID
			)
		){
			$shortcode = $this->aviberryShortCode->getShortCodeConversion($attachmentID[1]); // conversion shortcode
			
			// convert with Aviberry button
			$form['url']['html'] .= 
				"\n" . 
				sprintf( // button
					self::TEMPLATE_HTML_CONVERT_WITH_AVIBERRY, 
					$shortcode, // conversion shortcode
					$shortcode, // conversion shortcode compatibility with 2.9.2
					$this->aviberryPlugin->i18n('convertWithAviberry') // button label
				) 
				. "\n";
		}
		return $form;
	}
}


/**
 * WP <3.0
 */
class aviberryFilterLess3_0 extends aviberryFilter3_0__3_4_2 {
	
	/**
	 * Constructor
	 * 
	 * @param string $WPVersion
	 */
	public function __construct($WPVersion){
		
		call_user_func_array(array(parent, '__construct'), func_get_args());
	}
	
	
	/**
	 * Applied to post content prior to saving it in the database (also used for attachments).
	 * Performs Aviberry shortcode tags.
	 * 
	 * @global type $post
	 * @param string $content Post body
	 * 
	 * @return string
	 * 
	 * @throws aviberryPluginException 
	 */
	public function filter_content_save_pre($content){
		global $post_ID;
		global $action;
		
		// hack. Wordpress provides a new post ID since version 3.0 
		// when you create the message without the title
		if(	$action == 'post' && 
			$_POST['post_type'] == 'post' && 
			empty($post_ID) &&
			preg_match(	// find new shortcodes
				aviberryShortCode::REG_EXP_SHORTCODE_CONVERSION_NEW, 
				$content, 
				$matches, 
				PREG_OFFSET_CAPTURE
			)
		){
			set_exception_handler(array($this, 'handleException'));
			throw new aviberryPluginException(__('For WordPress versions < 3.0: Save posts before adding video, otherwise Aviberry will not start conversion.'));
		}
		
		return 
			call_user_func_array(array(parent, 'filter_content_save_pre'), func_get_args());
	}
}


?>