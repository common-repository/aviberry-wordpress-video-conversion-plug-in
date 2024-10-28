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
<select id="<?php echo $aviberry_preset_id; ?>" name="<?php echo $aviberry_preset_id; ?>" class="icon-menu aviberry-preset-select <?php echo $aviberry_preset_class; ?>" title="<?php echo $aviberry_preset_title; ?>">
	
	<optgroup label="Online Video">
		<option value="445">H.264 Video</option>
		<option value="519">H.264 Video (320x240)</option>
		<option value="522">H.264 Video (640x480)</option>
		<option value="521">H.264 Video (1280x720)</option>
		<option value="948">FLV - H.264 Flash Video</option>
		<option value="533">FLV - Optimal Quality (320x240)</option>
		<option value="542">FLV - Normal Quality (384x288)</option>
		<option value="543">FLV - Best Quality (480x360)</option>
		<option value="2335">WebM Video</option>
	</optgroup>
	<optgroup label="Flash video (.flv)">
		<option value="533">FLV - Optimal Quality (320x240)</option>
		<option value="542">FLV - Normal Quality (384x288)</option>
		<option value="543">FLV - Best Quality (480x360)</option>
		<option value="948">FLV - H.264 Flash Video</option>
		<option value="949">FLV - H.263 Flash Video</option>
	</optgroup>
	<optgroup label="MPEG-4 video (.mp4)">
		<option value="446" >MPEG4 Video</option>
		<option value="2336">MPEG4 Video (High Quality)</option>
		<option value="896" >HD MPEG4 720p</option>
		<option value="2734">HD MPEG4 720p (High Quality)</option>
		<option value="899" >HD MPEG4 1080p</option>
		<option value="3148">HD MPEG4 1080p (High Quality)</option>

		<option value="445">H.264 Video</option>
		<option value="519">H.264 Video (320x240)</option>
		<option value="522">H.264 Video (640x480)</option>
		<option value="521">H.264 Video (1280x720)</option>
		<option value="901" >HD H.264 720p, 4:3</option>
		<option value="3150">HD H.264 720p, 4:3 (High Quality)</option>
		<option value="903" >HD H.264 1080p, 4:3</option>
		<option value="2736">HD H.264 1080p, 4:3 (High Quality)</option>
		<option value="920" >iPod (320x240)</option>
		<option value="2739">iPod (320x240) (High Quality)</option>

		<option value="921" >iPod 5G (640x480)</option>
		<option value="2740">iPod 5G (640x480) (High Quality)</option>
		<option value="924" >iPhone (480x320)</option>
		<option value="2744">iPhone (480x320) (High Quality)</option>
		<option value="934" >Video for PSP</option>
		<option value="2746">Video for PSP (High Quality)</option>
	</optgroup>
	<optgroup label="WebM (.webm)">
		<option value="2335">WebM Video</option>
	</optgroup>
	<optgroup label="Best for video podcast, MPEG-4 video (.mov)">
		<option value="892">QuickTime Video</option>
	</optgroup>
	<optgroup label="3GPP for cellphone (.3gp)">
		<option value="894">Mobile Phone 3GP Video (352x288)</option>
	</optgroup>
	<optgroup label="3GPP2 for cellphone (.3gp2)">
		<option value="895">Mobile Phone 3GP2 Video (352x288)</option>
	</optgroup>
	<optgroup label="AVI video, Xvid (.avi)">
		<option value="442">AVI - Audio-Video Interleaved</option>
	</optgroup>
	<optgroup label="WMV (.wmv)">
		<option value="1493">WMV Video</option>
		<option value="13"  >WMV 9 for VHS quality video</option>
		<option value="14"  >WMV 9 for DVD quality video (1 mbps)</option>
		<option value="15"  >WMV 9 for DVD quality video (2 mbps)</option>
		<option value="468" >WMV HD Video (720p, 4:3)</option>
		<option value="469" >WMV HD Video (1080p, 4:3)</option>
		<option value="467" >WMV HD Video (720p, 16:9)</option>
		<option value="470" >WMV HD Video (1080p, 16:9)</option>
		<option value="2334">Video for PowerPoint</option>
	</optgroup>
	<optgroup label="MPEG-2 video (.mpeg)">
		<option value="8"  >DVD PAL Compatible</option>
		<option value="7"  >DVD NTSC Compatible</option>
		<option value="891">MPEG Best Quality</option>
		<option value="3"  >VCD NTSC Compatible</option>
		<option value="4"  >VCD PAL Compatible</option>
		<option value="5"  >SVCD NTSC Compatible</option>
		<option value="6"  >SVCD PAL Compatible</option>
	</optgroup>
	<optgroup label="MP3 audio (.mp3)">
		<option value="263">Audio Only: MP3 Normal Quality</option>
		<option value="119">Audio Only: MP3 Good Quality</option>
		<option value="120">Audio Only: MP3 High Quality</option>
	</optgroup>
</select>
