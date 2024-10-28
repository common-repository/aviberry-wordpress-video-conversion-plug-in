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



define('AVIBERRY_CONVERSION_POSTER_TEMPLATE_FILE', AVIBERRY_PLUGIN_ABSPATH . 'tpl/conversion_poster.html');



class aviberryShortCode {
	
	/**
	 * Aviberry conversion tag template
	 * @var string 
	 */
	const SHORTCODE_TEMPLATE_CONVERSION = "[aviberry_conversion source_attachment_id='%s' width='%s' height='%s' conversion_id='%s']";
	
	/**
	 * Aviberry conversion tag regexp
	 * @var string 
	 */
	const REG_EXP_SHORTCODE_CONVERSION =	'/\[aviberry_conversion\s+source_attachment_id=\\\?\'(\d+)\\\?\'\s+(?:width=\\\?\'(\d+)\\\?\'\s+)?(?:height=\\\?\'(\d+)\\\?\'\s+)?conversion_id=\\\?\'([^\\\[\]\s]*)\\\?\'\s*\]/';
	const REG_EXP_SHORTCODE_CONVERSION_NEW ='/\[aviberry_conversion\s+source_attachment_id=\\\?\'(\d+)\\\?\'\s+(?:width=\\\?\'(\d+)\\\?\'\s+)?(?:height=\\\?\'(\d+)\\\?\'\s+)?conversion_id=\\\?\'\\\?\'\s*\]/';
	
	
	/**
	 * @var aviberryPlugin
	 */
	protected $aviberryPlugin = null;
	
	
	
	/**
	 * Constructor
	 * 
	 * @param string $WPVersion
	 */
	public function __construct($WPVersion){
		
		$this->aviberryPlugin = aviberryPluginFactory::create($WPVersion);
		
		add_shortcode('aviberry_conversion', array($this, 'shortcode_conversion'));

		if ($this->aviberryPlugin->getOption('aviberry_use_player') && 
			$this->aviberryPlugin->getOption('aviberry_player_embedding_type') == 'shortcode'
		){
			add_shortcode('aviberry_player', array($this, 'shortcode_aviberry_player'));
			// deprecated
			add_shortcode('aviberry-player', array($this, 'shortcode_aviberry_player'));
		}
	}
	
		
	/**
	 * Shortcodes 
	 */
	
	/**
	 * shortcode_aviberry_player
	 * 
	 * @param type $atts
	 * @param type $content
	 * 
	 * @return string 
	 */
	public function shortcode_aviberry_player($atts, $content = null) {
		extract(shortcode_atts(
			array(
				'title'  => '',
				'href'   => '',
				'width'  => $this->aviberryPlugin->getOption('aviberry_player_width'),
				'height' => $this->aviberryPlugin->getOption('aviberry_player_height'),
				'id'     => ''
			),
			$atts
		));

		return
			$this->getPlayerHTML($title, $href, $width, $height, $id);
	}
	
	
	/**
	 * shortcode_conversion
	 * 
	 * @global object $post
	 * 
	 * @param type $atts
	 * @param type $content
	 * 
	 * @return string
	 */
	public function shortcode_conversion($atts, $content = null){
		global $post;
		
		if(isset($post)){
			extract(shortcode_atts(
				array(
					'source_attachment_id' => '',
					'width' => false, 
					'height' => false,
					'conversion_id' => ''
				),
				$atts
			));
			
			if(	!empty($conversion_id) )
				if($conversion = $this->aviberryPlugin->getPostConversion($post->ID, $conversion_id))
					return $this->getShortcodeConversionPoster($conversion, $width, $height);
				else
					return $this->getShortcodeConversionPosterError(__('Conversion is not found in the post meta.'), 0, $width, $height);
			else
				return $this->getShortcodeConversionPosterError(__('Publish or save the post to start conversion.'), 0, $width, $height);
		}
		
		return $this->getShortcodeConversionPosterError(__('Shortcode error.'), 0, $width, $height);
	}
	
	
	/**
	 * getShortCodeConversion
	 * 
	 * @param integer $source_attachment_id
	 * @param string $conversion_id
	 * @param integer $width (optional)
	 * @param integer $height (optional)
	 * 
	 * @return string shortcode (optional)
	 */
	public function getShortCodeConversion(
		$source_attachment_id,
		$width = false, 
		$height = false,
		$conversion_id = ''
	){
		$width || ($width = $this->aviberryPlugin->getOption('aviberry_player_width'));
		$height || ($height = $this->aviberryPlugin->getOption('aviberry_player_height'));
		
		return
			sprintf(
				self::SHORTCODE_TEMPLATE_CONVERSION, 
				$source_attachment_id,
				$width,
				$height,
				$conversion_id
			);
	}
	
	
	/**
	 * getShortcodeConversionPoster
	 * 
	 * @param array $conversion
	 * @param integer $width (optional)
	 * @param integer $height (optional)
	 * 
	 * @return string
	 */
	private function getShortcodeConversionPoster($conversion, $width = false, $height = false){
		
		if(	empty($conversion) || 
			empty($conversion['status'])
		)
			return $this->getShortcodeConversionPosterError(__('Error in post meta.'), 0, $width, $height);
		
		$result = '';
		
		switch ($conversion['status']){
			case aviberryPlugin::CONVERSION_STATUS_CONVERTING:
				$result = $this->getShortcodeConversionPosterConverting($width, $height);
				break;

			case aviberryPlugin::CONVERSION_STATUS_FINISHED:
				// if success
				if(empty($conversion['error_code'])){
					
					// if attachment exists
					if(	!empty($conversion['attach_id']) && 
						($attachment = get_post($conversion['attach_id'])) &&
						($attach_url = wp_get_attachment_url($attachment->ID))
					){
						$result = $this->aviberryPlugin->getOption('aviberry_use_player') ? // if we need to show player
							(
								// if the type of embedding is "shortcode", then we will show HTML "object" player instead of the "shortcode", 
								// because the new "shortcode" will not be processed anymore
								$this->aviberryPlugin->getOption('aviberry_player_embedding_type') == 'shortcode' ?
									$this->getPlayerHTML(
										$attachment->post_title, 
										$attach_url, 
										$width, 
										$height, 
										$attachment->ID
									) : 
									// if the type of embedding is "javascript", then we will show HTML link which will be processed on client side
									$this->aviberryPlugin->getPlayerEmbeddingCode(
										$attachment->post_title, 
										$attach_url, 
										$attachment->ID,
										$width, 
										$height
									)
							) : 
							// if we need NOT to show player
							"<a href=\"$attach_url\">$attachment->post_title</a>";

					// if attachment does not exist
					} else
						$result = $this->getShortcodeConversionPosterError(__('Incorrect attachment.'), 0, $width, $height);
					
				// if conversion error
				} else 
					$result = $this->getShortcodeConversionPosterError($conversion['error_message'], $conversion['error_code'], $width, $height);
				
				break;
				
			default: 
				$result = $this->getShortcodeConversionPosterError(__('Incorrect conversion status in post meta.'), 0, $width, $height);
				break;
		}
		
		return $result;
	}
	
	
	/**
	 * Get the converting message for conversion shortcode 
	 * 
	 * @param integer $width (optional)
	 * @param integer $height (optional)
	 * 
	 * @return string
	 */
	private function getShortcodeConversionPosterConverting($width = false, $height = false){
		
		return 
			$this->getShortcodeConversionPosterCommon(
				__('Please wait, the file is being converted...'),
				'', 
				$width, 
				$height
			);
	}
	
	
	/**
	 * Get the error message for conversion shortcode 
	 * 
	 * @param string $error_message
	 * @param integer $error_code
	 * @param integer $width (optional)
	 * @param integer $height (optional)
	 * 
	 * @return string
	 */
	private function getShortcodeConversionPosterError($error_message = '', $error_code = 0, $width = false, $height = false){
		
		return 
			$this->getShortcodeConversionPosterCommon(
				__('Conversion error:'),
				$error_message, 
				$width, 
				$height
			);
	}
	
	
	/**
	 * Get the message for conversion shortcode 
	 * 
	 * @param string $textHeader
	 * @param string $textContent
	 * @param integer $width (optional)
	 * @param integer $height (optional)
	 * 
	 * @return string
	 */
	private function getShortcodeConversionPosterCommon($textHeader, $textContent = '', $width = false, $height = false){
		
		$width || ($width = $this->aviberryPlugin->getOption('aviberry_player_width'));
		$height || ($height = $this->aviberryPlugin->getOption('aviberry_player_height'));
		
		return 
			str_replace(
				array(
					'{CSS_STYLE}',
					'{TEXT_HEADER}',
					'{TEXT_CONTENT}'
				),
				array(
					"width:{$width}px; height:{$height}px;",
					$textHeader,
					$textContent
				),
				file_get_contents(AVIBERRY_CONVERSION_POSTER_TEMPLATE_FILE)
			);
	}
	
	
	/**
	 * getPlayerHTML
	 * 
	 * @param string $title
	 * @param string $href
	 * @param integer $width
	 * @param integer $height
	 * @param integer $id
	 * 
	 * @return string HTML "object"
	 */
	private function getPlayerHTML($title, $href, $width, $height, $id) {
		
		$width || ($width = $this->aviberryPlugin->getOption('aviberry_player_width'));
		$height || ($height = $this->aviberryPlugin->getOption('aviberry_player_height'));
		
		$player = AVIBERRY_PLUGIN_URL . 'player.swf';
		$title  = esc_html($title);
		$href   = esc_attr($href);
		$width  = esc_attr($width);
		$height = esc_attr($height);
		$id     = esc_attr($id);

		return "
			<object classid=\"clsid:D27CDB6E-AE6D-11cf-96B8-444553540000\" width=\"$width\" height=\"$height\" id=\"player-media-$id\" name=\"player-media-$id\" title=\"$title\">
				<param name=\"movie\" value=\"$player\"/>
				<param name=\"allowfullscreen\" value=\"true\"/>
				<param name=\"allowscriptaccess\" value=\"always\"/>
				<param name=\"flashvars\" value=\"file=$href\"/>
				<embed
					id=\"player-media-$id\"
					name=\"player-media-$id\"
					src=\"$player\"
					width=\"$width\"
					height=\"$height\"
					allowscriptaccess=\"always\"
					allowfullscreen=\"true\"
					flashvars=\"file=$href\"
				/>
			</object>
		";
	}
}

?>