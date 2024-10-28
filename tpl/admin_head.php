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
 * Aviberry Plugin template page header.
 *
 * @package aviberryPlugin
 */

// Plugin functionality.
require_once AVIBERRY_PLUGIN_ABSPATH . 'lib/aviberry/aviberryPlugin/aviberryPluginFactory.class.php';

$aviberryPlugin = aviberryPluginFactory::create($wp_version);


// Pass data to JS.
?>
<script type="text/javascript">
//<!--<![CDATA[
var aviberryPlugin = {
	
	// Aviberry conversion tag template
	SHORTCODE_TEMPLATE_CONVERSION: "<?php echo aviberryShortCode::SHORTCODE_TEMPLATE_CONVERSION;  ?>",
	REG_EXP_SHORTCODE_CONVERSION_NEW: <?php echo aviberryShortCode::REG_EXP_SHORTCODE_CONVERSION_NEW;  ?>,
	
	// Definitions
	AVIBERRY_PLUGIN_API_URL:			"<?php echo AVIBERRY_PLUGIN_API_URL;  ?>",
	AVIBERRY_S3_HOST:					"<?php echo AVIBERRY_S3_HOST;         ?>",
	AVIBERRY_WATCH_TIMEOUT:				<?php  echo AVIBERRY_WATCH_TIMEOUT;   ?>,
	AVIBERRY_ACCOUNT_INFO_TIMEOUT:		<?php  echo AVIBERRY_ACCOUNT_INFO_TIMEOUT; ?>,
	ACCOUNT_INFO_PAYMENT_STATUS_PAID:	"<?php echo aviberryPlugin::ACCOUNT_INFO_PAYMENT_STATUS_PAID;  ?>",
	BUY_NOW_URL:						"<?php echo aviberryPlugin::BUY_NOW_URL;  ?>",

	// Options
	apiHost:             "<?php echo $aviberryPlugin->getOption('aviberry_api_host');?>",
	apiKey:              "<?php echo $aviberryPlugin->getOption('aviberry_api_key');?>",
	apiPass:             "<?php echo $aviberryPlugin->getOption('aviberry_api_pass');?>",
	storageType:         "<?php echo $aviberryPlugin->getOption('aviberry_storage_type');?>",
	storageUser:         "<?php echo $aviberryPlugin->getOption('aviberry_storage_user');?>",
	storagePass:         "<?php echo $aviberryPlugin->getOption('aviberry_storage_pass');?>",
	storageHost:         "<?php echo $aviberryPlugin->getOption('aviberry_storage_host');?>",
	storagePort:         "<?php echo $aviberryPlugin->getOption('aviberry_storage_port');?>",
	storagePath:         "<?php echo $aviberryPlugin->getOption('aviberry_storage_path');?>",
	storageUrl:          "<?php echo $aviberryPlugin->getStorageUrl();?>",
	displayTooltips:     "<?php echo $aviberryPlugin->getOption('aviberry_display_tooltips');?>",
	usePlayer:           "<?php echo $aviberryPlugin->getOption('aviberry_use_player');?>",
	playerEmbeddingType: "<?php echo $aviberryPlugin->getOption('aviberry_player_embedding_type');?>",
	playerWidth:         "<?php echo $aviberryPlugin->getOption('aviberry_player_width');?>",
	playerHeight:        "<?php echo $aviberryPlugin->getOption('aviberry_player_height');?>",
	presetDefault:       "<?php echo $aviberryPlugin->getOption('aviberry_preset_default');?>",

	isInstallationCompleted: <?php echo (int)$aviberryPlugin->isInstallationCompleted();?>,
	accountInfo:         <?php echo json_encode($aviberryPlugin->getAccountInfoDB());?>,

	// Strings
	i18n: {

		<?php
			$aviberryPlugin->init();
			$i = 0;
			foreach($aviberryPlugin->_i18n as $key => $value){
				if($i > 0) echo ",\n";
				echo $key . ": \"$value\"";
				$i++;
			}
		?>
	}
};
//]]>-->
</script>
