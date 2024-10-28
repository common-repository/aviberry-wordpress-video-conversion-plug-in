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

<!--[if gte IE 9]>
  <style type="text/css">
    .aviberry_conversion_poster {
       filter: none;
    }
  </style>
<![endif]-->

<script type="text/javascript">
//<!--
	var aviberryPlugin = {
		// Definitions
		AVIBERRY_PLUGIN_URL: "<?php echo AVIBERRY_PLUGIN_URL; ?>",
		// Options
		playerWidth:         "<?php echo $aviberryPlugin->getOption('aviberry_player_width');?>",
		playerHeight:        "<?php echo $aviberryPlugin->getOption('aviberry_player_height');?>"
	};
//-->
</script>
