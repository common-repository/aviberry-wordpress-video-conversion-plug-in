
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
 * Aviberry post scope and functionality.
 */
(function($){
 
	var ATTACHMENT_DISPLAY_SETTINGS_TEMPLATE_ID = 'attachment-display-settings';
 
    $(document).ready(function(){
		
		// Add "Convert with Aviberry" option to the select
		// modify template of settings display attachment of media manager
		var selectTemplate = jQuery('#tmpl-' + ATTACHMENT_DISPLAY_SETTINGS_TEMPLATE_ID);
		
		// there is no template without media manager
		if(selectTemplate.length > 0){
			selectTemplate.html(
				selectTemplate
					.html()
					.replace(
						/(<select class="link-to"[\s\S]*<option value="none">[\s\S]*<\/option>\s*)(<\/select>)/,
						"$1\n<option value=\"aviberry\">Convert with Aviberry</option>\n$2"
					)
			);
		
			// Select change handler
			var updateLinkToOld = wp.media.view.Settings.AttachmentDisplay.prototype.updateLinkTo;

			// replace method "updateLinkTo" with our
			wp.media.view.Settings.AttachmentDisplay = wp.media.view.Settings.AttachmentDisplay.extend({
				updateLinkTo: function() {

					var linkTo = this.model.get('link'),
						$input = this.$('.link-to-custom'),
						attachment = this.options.attachment;

					// if "Convert with Aviberry" option selected
					if ( linkTo == 'aviberry' && attachment ) {

						var linkUrl = this.model.get('linkUrl');

						// set default aviberry shortcode
						if( !linkUrl || !linkUrl.match(aviberryPlugin.REG_EXP_SHORTCODE_CONVERSION_NEW) ){
							$input.val(
								aviberryPlugin.SHORTCODE_TEMPLATE_CONVERSION
									.replace('%s', attachment.get('id'))
									.replace('%s', aviberryPlugin.playerWidth)
									.replace('%s', aviberryPlugin.playerHeight)
									.replace('%s', '')
							);
							// update the model
							$input.trigger('change');
						}

						$input.prop( 'readonly', false );
						$input.show();
						$input.focus()[0].select();


					// if "Convert with Aviberry" option NOT selected
					} else
						// call default handler
						updateLinkToOld.apply(this, arguments);
				}
			});

			// insert into post handler
			var mediaStringPropsOld = wp.media.string.props;

			// 
			// replace method "props" with our
			// 
			// Joins the `props` and `attachment` objects,
			// outputting the proper object format based on the
			// attachment's type.
			wp.media.string.props = function( props, attachment ) {
				var link = props.link || getUserSetting( 'urlbutton', 'post' );

				if(link == 'aviberry')
					props.link = 'custom';

				// call default handler
				return mediaStringPropsOld.apply(this, arguments);
			};
		}
    });
})(jQuery);
