
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


function dialogClearError(selector, css_class_add){
	jQuery(selector + " .aviberry_dialog_message").text("").removeClass("ui-state-error");
	jQuery(selector).find("input,textarea").removeClass("ui-state-highlight");
	
	if(css_class_add)
		jQuery(selector).find("input[type=text],input[type=password]").addClass(css_class_add);
}

function dialogBeginAjaxLoading(selector){	
	jQuery(selector + " .aviberry_dialog_button_submit").attr('disabled', 'disabled');
	jQuery(selector + " .aviberry_dialog_button_cancel").attr('disabled', 'disabled');
	jQuery(selector + " .aviberry_dialog_ajax_loader").show();
}

function dialogEndAjaxLoading(selector){	
	jQuery(selector + " .aviberry_dialog_button_submit").removeAttr('disabled');
	jQuery(selector + " .aviberry_dialog_button_cancel").removeAttr('disabled');
	jQuery(selector + " .aviberry_dialog_ajax_loader").hide();
}

function dialogShowCompleteMessage(selector, data){
	if(data && data.message)
		jQuery(selector + " .aviberry_dialog_message").text(data.message);
	else
		jQuery(selector + " .aviberry_dialog_message").text(AJAX_END_MESSAGE_DEFAULT_SUCCESS);
}

function dialogShowErrorMessage(selector, data, error_input_elements, css_class_remove, html){
	jQuery(selector + " .aviberry_dialog_message").addClass("ui-state-error");
	
	if(data && data.message)
		if(html)
			jQuery(selector + " .aviberry_dialog_message").html(data.message);
		else
			jQuery(selector + " .aviberry_dialog_message").text(data.message);
	else
		if(html)
			jQuery(selector + " .aviberry_dialog_message").html(AJAX_END_MESSAGE_DEFAULT_ERROR);
		else
			jQuery(selector + " .aviberry_dialog_message").text(AJAX_END_MESSAGE_DEFAULT_ERROR);
		
	if(error_input_elements){
		if(css_class_remove)
			jQuery(error_input_elements).removeClass(css_class_remove);
			
		jQuery(error_input_elements).addClass("ui-state-highlight");
	}
}
