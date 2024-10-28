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
 * Aviberry Plugin preset dialogs.
 *
 * @package aviberryPlugin
 */
 ?>
<div id="dlg_start_post_conversion" style="display: none;">
	<div class="message"></div>
	<div>
		<?php
			$aviberry_preset_id = 'aviberry_preset_id';
			$aviberry_preset_class = '';
			$aviberry_preset_title = '';
			require(AVIBERRY_PLUGIN_ABSPATH . 'tpl/preset_select.php');
		?>
	</div>
	<div id="aviberry_conversion_dlg_free" style="display: none;">
		<div><?php _e('Please note, as a trial limitation the included encoding traffic is 2GB only. To remove this limitation, you have to purchase the full license of the program.'); ?></div>
		<div id="aviberry_conversion_dlg_buynow_wrap"><a href="<?php echo aviberryPlugin::BUY_NOW_URL; ?>" class="aviberry-button aviberry-buynow-button" target="_blank"><?php _e('Buy Now'); ?></a></div>
	</div>
</div>
