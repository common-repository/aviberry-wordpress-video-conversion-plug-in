
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
 * "AviberryPlayer" scope and functionality.
 */ 
function AviberryPlayer() {
	/**
	 * Replaces native <a> tag with Aviberry player.
	 * 
	 * @return void
	 */
	var embedPlayer = function() {
		jQuery("a.aviberry-player").each(function(i) {
			var flashvars = {
				"file": jQuery(this).attr("href")
			};
			var params = {
				"allowfullscreen":   "true",
				"allowscriptaccess": "always"
			};
			var attributes = {
				"id":   "player-" + this.id,
				"name": "player-" + this.id
			};
			var width = jQuery(this).attr("width") ? jQuery(this).attr("width") : aviberryPlugin.playerWidth;
			var height = jQuery(this).attr("height") ? jQuery(this).attr("height") : aviberryPlugin.playerHeight;
			
			swfobject.embedSWF(
				aviberryPlugin.AVIBERRY_PLUGIN_URL + "player.swf",
				this.id,
				width, 
				height,
				"9.0.0",
				"false",
				flashvars, params, attributes
			); 
		});
	};
	
	embedPlayer();
}

jQuery(document).ready(function () {
	var aviberry = new AviberryPlayer();
});
