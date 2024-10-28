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
 * Aviberry Plugin settings administration panel.
 *
 * @package aviberryPlugin
 */

// Plugin functionality.
require_once AVIBERRY_PLUGIN_ABSPATH . 'lib/aviberry/aviberryPlugin/aviberryPluginFactory.class.php';

$aviberryPlugin = aviberryPluginFactory::create($wp_version);


?>
<div class="aviberry-wrap aviberry-option-plugin">

	<div id="aviberry-options-signin-wrap" class="aviberry-options-wrap" style="display: none">
		
		<h1><?php _e('Thank you for installing Aviberry Wordpress Video Conversion Plugin.'); ?></h1>
		
		<div class="aviberry-options-unit aviberry-hint" id="aviberry-hint">
			<p><?php _e('Enter your Aviberry account information'); ?></p>
		
			<form>
				<div class="aviberry_valign_middle aviberry-signin-form-body-three">
					<table><tbody><tr><td>
						<table>
							<tbody>
								<tr>
									<th scope="row"><label for="aviberry_api_host_signin"><?php _e('API host'); ?>:</label></th>
									<td><input type="text" class="regular-text aviberry-option" id="aviberry_api_host_signin" name="aviberry_api_host" value="<?php esc_attr_e($aviberryPlugin->getOption('aviberry_api_host')); ?>" title="<?php _e('www.aviberry.com by default'); ?>" /></td>
								</tr>
								<tr>
									<th scope="row"><label for="aviberry_api_key_signin"><?php _e('API key'); ?>:</label></th>
									<td><input type="text" class="regular-text aviberry-option" id="aviberry_api_key_signin" name="aviberry_api_key" value="<?php form_option("aviberry_api_key"); ?>" title="<?php _e('API key of Aviberry\'s member. Get it on your account tab at http://www.aviberry.com/accounts/'); ?>" /></td>
								</tr>
								<tr>
									<th scope="row"><label for="aviberry_api_pass_signin"><?php _e('API pass'); ?>:</label></th>
									<td><input type="text" class="regular-text aviberry-option" id="aviberry_api_pass_signin" name="aviberry_api_pass" value="<?php form_option("aviberry_api_pass"); ?>" title="<?php _e('API pass of Aviberry\'s member. Get it on your account tab at http://www.aviberry.com/accounts/'); ?>" /></td>
								</tr>
							</tbody>
						</table>
					</td></tr></tbody></table>
				</div>

				<div class="aviberry-clear"></div>

				<div class="aviberry_dialog_buttons_panel">
					<span class="submit">
						<a class="button-primary dialog_button_submit" href="<?php echo $aviberryPlugin->getPluginSettingsURL(); ?>"><?php _e('Sign in'); ?></a>
					</span>
					<span style="display: none" class="aviberry_dialog_ajax_loader"></span>
					<span class="aviberry_dialog_message"></span>
				</div>
			</form>
		</div>
	</div>
	
	
	<div id="aviberry-options-notsignin-wrap" class="aviberry-options-wrap">
		
		<form action="options.php" method="post">
			<?php  settings_fields('aviberry-options-group'); ?>
		
			<div class="aviberry-options-submit-wrap aviberry-options-submit-wrap-top">
				<?php //screen_icon(); ?>
				<p class="submit">
					<input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
				</p>
			</div>
	
			<div class="aviberry-options-unit">

				<h3><?php _e('Your Aviberry Account Information'); ?></h3>
				
				<table class="form-table">
					<tbody>
						<tr>
							<th scope="row"><label for="aviberry_api_host"><strong><?php _e('API host'); ?>:</strong></label></th>
							<td><input type="text" class="regular-text aviberry-option" id="aviberry_api_host" name="aviberry_api_host" value="<?php esc_attr_e($aviberryPlugin->getOption('aviberry_api_host')); ?>" title="<?php _e('www.aviberry.com by default'); ?>" /></td>
						</tr>
						<tr>
							<th scope="row"><label for="aviberry_api_key"><strong><?php _e('API key'); ?>:</strong></label></th>
							<td><input type="text" class="regular-text aviberry-option" id="aviberry_api_key" name="aviberry_api_key" value="<?php form_option("aviberry_api_key"); ?>" title="<?php _e('API key of Aviberry\'s member. Get it on your account tab at http://www.aviberry.com/accounts/'); ?>" /></td>
						</tr>
						<tr>
							<th scope="row"><label for="aviberry_api_pass"><strong><?php _e('API pass'); ?>:</strong></label></th>
							<td><input type="text" class="regular-text aviberry-option" id="aviberry_api_pass" name="aviberry_api_pass" value="<?php form_option("aviberry_api_pass"); ?>" title="<?php _e('API pass of Aviberry\'s member. Get it on your account tab at http://www.aviberry.com/accounts/'); ?>" /></td>
						</tr>
					</tbody>
				</table>

				<div id="aviberry-options-notconfirmed-notice" style="display: none;">
					<p><?php
						$accountInfo = $aviberryPlugin->getAccountInfoDB();
						if(isset($accountInfo['email']))
							_e('This email has been registered but requires the confirmation.'); 
					?></p>
				</div>
			
				<div id="aviberry-options-trial-notice" style="display: none;">
					
					<p><?php _e('Please note, as a trial limitation the included encoding traffic is 2GB only. <br />To remove this limitation, you have to purchase the full license of the program.'); ?></p>
					<p class="aviberry-buynow"><a class="aviberry-buynow-button" href="<?php echo aviberryPlugin::BUY_NOW_URL; ?>"><?php _e('Buy now'); ?></a></p>
				</div>
			</div>



			<div class="aviberry-options-unit">
				<h3 title="<?php if (!$aviberryPlugin->isFileStorageSpecified()) 
					_e('You should select a storage type for your converted files and fill info about it.');
					?>"><?php _e('File Storage'); ?>
				</h3>
				
				<table class="form-table">
				
					<tr id="aviberry-options-storage-class-wrap" style="display: none">
						<td colspan="3">
							
							<?php 
								$current_storage_type = $aviberryPlugin->getOption('aviberry_storage_type'); 
							?>
							
							<label>
								<input 
									type="radio" 
									name="aviberry_storage_class" 
									value="media_library"
									autocomplete="off"
									<?php 
										if(!in_array(
											$current_storage_type, 
											array(
												'ftp',
												'ftps',
												's3',
											)
										))
											echo ' checked="checked"';
									?>
								>
								<?php _e('Store the conversion result in the Media Library and use video autoconversion when creating a new post'); ?>
							</label><br />
							<label>
								<input 
									type="radio" 
									name="aviberry_storage_class" 
									value="alternative"
									autocomplete="off"
									<?php 
										if(in_array(
											$current_storage_type, 
											array(
												'ftp',
												'ftps',
												's3',
											)
										))
											echo ' checked="checked"';
									?>
								>
								<?php _e('Use alternative storage (autoconversion will not be available while creating a post)'); ?>
							</label>
						</td>
					</tr>
				
					<tr id="aviberry-options-storage-type-wrap">
						<th scope="row"><label for="aviberry_storage_type"><strong><?php _e('Type'); ?>:</strong></label></th>
						<td colspan="2">
							<?php
							$storage_types = array(						
								'wp_media_lib_ftp' => array(
									'caption'     => __(' Media library (FTP)'),
									'description' => __('Wordpress media library via FTP'),
									'class' => 'aviberry-storage-class-media_library',
								),
								'wp_media_lib_ftps' => array(
									'caption'     => __(' Media library (FTPS)'),
									'description' => __('Wordpress media library via FTPS'),
									'class' => 'aviberry-storage-class-media_library',
								),
								'ftp' => array(
									'caption'     => ' FTP',
									'description' => __('File Transfer Protocol'),
									'class' => 'aviberry-storage-class-alternative',
								),
								'ftps' => array(
									'caption'     => ' FTPS',
									'description' => __('File Transfer Protocol + SSL'),
									'class' => 'aviberry-storage-class-alternative',
								),
								's3' => array(
									'caption'     => ' Amazon S3',
									'description' => __('Amazon\'s Simple Storage Service'),
									'class' => 'aviberry-storage-class-alternative',
								)
							);

							foreach ($storage_types as $storage_type => $storage) {
								echo 
									"<input 
										type='radio' 
										name='aviberry_storage_type' 
										value='" . esc_attr($storage_type) . "'
										id='aviberry-storage-type-" . esc_attr($storage_type) . "'
										autocomplete='off'";
										if ($storage_type === $current_storage_type)
											echo ' checked="checked"';
								echo " />
									<label 
										class='" . esc_attr($storage['class']) . "' 
										title='" . esc_attr($storage['description']) . "' for='aviberry-storage-type-" . esc_attr($storage_type) . "'>" .
										esc_html($storage['caption']) . 
									"</label>\n";
							}
							?>
						</td>
					</tr>
					<tr>
						<th scope="row"></th>
						<td colspan="2"><span id="aviberry_fileStorageType_description" class="aviberry-hint"><?php
							switch ($current_storage_type) {
								case 'wp_media_lib_ftp':
								case 'wp_media_lib_ftps':
									echo $aviberryPlugin->i18n('typeMediaLibraryDescription');
									break;
								default:
									echo $aviberryPlugin->i18n('typeNotMediaLibraryDescription');
							} ?></span>
						</td>
					</tr>
					<tr>
						<th scope="row"><strong><label class="colon" for="aviberry_storage_user"><?php _e('User name'); ?></label></strong></th>
						<td><input type="text" class="regular-text aviberry-option" id="aviberry_storage_user" name="aviberry_storage_user" value="<?php form_option("aviberry_storage_user"); ?>" title="<?php _e('User name to log into storage.'); ?>" /></td>
						<td></td>
					</tr>
					<tr>
						<th scope="row"><strong><label class="colon" for="aviberry_storage_pass"><?php _e('Password'); ?></label></strong></th>
						<td><input type="password" class="regular-text aviberry-option" id="aviberry_storage_pass" name="aviberry_storage_pass" value="<?php form_option("aviberry_storage_pass"); ?>" title="<?php _e('User password to log into storage.'); ?>" /></td>
						<td></td>
					</tr>
					<tr>
						<th scope="row"><label for="aviberry_storage_host"><strong><?php _e('Host'); ?>:</strong></label></th>
						<td>
							<input type="text" class="regular-text aviberry-option" id="aviberry_storage_host" name="aviberry_storage_host" value="<?php form_option("aviberry_storage_host"); ?>" title="<?php _e('Storage host name.'); ?>" />
						</td>
						<td class="aviberry_param_example">
							<strong><?php _e('Example:'); ?></strong>
							<span>example.com</span>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="aviberry_storage_port"><strong><?php _e('Port'); ?>:</strong></label></th>
						<td>
							<input type="text" class="regular-text aviberry-option" id="aviberry_storage_port" name="aviberry_storage_port" value="<?php form_option("aviberry_storage_port"); ?>" title="<?php _e('Storage port (if required).'); ?>" />
						</td>
						<td class="aviberry_param_example">
							<strong><?php _e('Example:'); ?></strong>
							<span>21</span>
						</td>
					</tr>
					<tr>
						<th scope="row"><strong>
							<label class="colon" for="aviberry_storage_path"><?php
								echo $aviberryPlugin->i18n('labelStoragePath');
							?></label></strong>
						</th>
						<td>
							<input type="text" class="regular-text aviberry-option" id="aviberry_storage_path" name="aviberry_storage_path" value="<?php form_option("aviberry_storage_path"); ?>" title="<?php _e('Storage path (if required).'); ?>" />
						</td>
						<td class="aviberry_param_example">
							<strong><?php _e('Example:'); ?></strong> 
							<span><?php
								switch ($current_storage_type) {
									case 'wp_media_lib_ftp':
									case 'wp_media_lib_ftps':
										echo $aviberryPlugin->i18n('examplePathToWordpressMediaLibrary');
										break;
									case 'ftp':
									case 'ftps':
										echo $aviberryPlugin->i18n('examplePath');
										break;
									case 's3':
										echo $aviberryPlugin->i18n('exampleBacket');
										break;
									} ?>
							</span>
						</td>
					</tr>
					<tr id="aviberry-storage-url-wrap" style="display: none">
						<th scope="row"><strong><label for="aviberry_storage_port"><?php _e('File storage URL'); ?></label>:</strong></th>
						<td colspan="2"><span id="aviberry_fileStorage_fullUrl"></span></td>
					</tr>
				</table>
			</div>
			
		
			
			<div class="aviberry-options-unit">
				<table class="form-table aviberry-automatic">
					<tr>
						<th scope="row"><label for="aviberry_preset_default"><strong><?php _e('Automatic conversion preset:') ?></strong></label></th>
						<td>
							<?php 
								$aviberry_preset_id = 'aviberry_preset_default';
								$aviberry_preset_class = 'aviberry-option';
								$aviberry_preset_title = __('All post attachments will be automatically converted to a preset you choose');
								require(AVIBERRY_PLUGIN_ABSPATH . 'tpl/preset_select.php');
							?>
						</td>
					</tr>
				</table>
			</div>

			
			<div class="aviberry-options-unit">
				<h3><?php _e('Aviberry video player configuration'); ?></h3>
				<table class="form-table">
					<tr>
						<th scope="row" colspan="2" class="th-full">
							<label for="aviberry_use_player">
								<input name="aviberry_use_player" type="checkbox" id="aviberry_use_player" value="1"<?php checked('1', $aviberryPlugin->getOption('aviberry_use_player')); ?> title="<?php _e('Whether to use Aviberry Player for video in published posts.'); ?>" />
								<?php _e('Use Aviberry Player to publish video'); ?>
							</label>
						</th>
					</tr>
					<tr>
						<th class="player-row" scope="row"><strong><label for="aviberry_player_embedding_type"><?php _e('Player embedding type:'); ?></label></strong></th>
						<td class="aviberry-configuration">
							<?php
							$embedding_types = array(
								'javascript' => array(
									'caption'     => ' JavaScript',
									'description' => __('Use JavaScript on client side to replace native "a" tag with the player. If you disable plugin, your posts will be not needed in modifications.')
								),
								'shortcode' => array(
									'caption'     => ' ShortCode',
									'description' => __('Use shortcode [aviberry_player] in post to embed the player on server side instead of native "a" tag. No need in JavaScript on client side, but if you disable plugin, your posts will be nedded to replace this shortcode.')
								)
							);
							$current_embedding_type = $aviberryPlugin->getOption('aviberry_player_embedding_type');

							foreach ($embedding_types as $embedding_type => $embedding) {
								echo '<label title="' . esc_attr($embedding['description']) . '"><input type="radio" name="aviberry_player_embedding_type" value="' . esc_attr($embedding_type) . '"';
								if ($embedding_type === $current_embedding_type)
									echo ' checked="checked"';
								echo '/>' . esc_html($embedding['caption']) . '</label>';
							}
							?>
						</td>
					</tr>
					<tr>
						<th class="player-row" scope="row"><strong><?php _e('Player size:') ?></strong></th>
						<td>
							<fieldset>
								<legend class="screen-reader-text"><span><?php _e('Player size'); ?></span></legend>
								<label for="player_width"><?php _e('Width'); ?></label>
								<input name="aviberry_player_width" type="text" id="aviberry_player_width" value="<?php esc_attr_e($aviberryPlugin->getOption('aviberry_player_width')); ?>" class="small-text" />
								<label for="aviberry_player_height"><?php _e('Height'); ?></label>
								<input name="aviberry_player_height" type="text" id="aviberry_player_height" value="<?php esc_attr_e($aviberryPlugin->getOption('aviberry_player_height')); ?>" class="small-text" />
							</fieldset>
						</td>
					</tr>
				</table>
			</div>
		

			<div class="aviberry-options-unit">
				<h3><?php _e('Others'); ?></h3>
				<table class="form-table">
					<tr>
						<th scope="row" colspan="2" class="th-full">
							<label for="aviberry_display_tooltips">
								<input name="aviberry_display_tooltips" type="checkbox" id="aviberry_display_tooltips" value="1"<?php checked('1', $aviberryPlugin->getOption('aviberry_display_tooltips')); ?> title="<?php _e('Whether to display more observable tooltips.'); ?>" />
								<?php _e('Display more observable tooltips'); ?>
							</label>
						</th>
					</tr>
				</table>
			</div>
			
			<div class="aviberry-options-submit-wrap  aviberry-options-submit-bottom">
				<p class="submit">
					<input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
				</p>
			</div>
		</form>
	</div>
	
	<p id="aviberry-more-info"><strong><?php _e('For more information, use the links below'); ?>:</strong><br />
		<a href="http://www.aviberry.com/?source=wppanel"><?php _e('Visit aviberry.com'); ?></a><br />
		<a href="http://www.aviberry.com/support.html?source=wppanel"><?php _e('Support'); ?></a>
	</p>
</div>
