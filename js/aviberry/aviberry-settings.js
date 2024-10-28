
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
 * "AviberrySettings" scope and functionality.
 */ 
function AviberrySettings() {
	// Radio group.
	var radioFileStorageType = function() {return jQuery(":radio[name=aviberry_storage_type]");};
	
	// Lables.
	var lblFileStorageUser = function() {return jQuery("label[for=aviberry_storage_user]");};
	var lblFileStoragePass = function() {return jQuery("label[for=aviberry_storage_pass]");};
	var lblFileStorageHost = function() {return jQuery("label[for=aviberry_storage_host]");};
	var lblFileStoragePort = function() {return jQuery("label[for=aviberry_storage_port]");};
	var lblFileStoragePath = function() {return jQuery("label[for=aviberry_storage_path]");};

	// Text boxes.
	var txtFileStorageUser = function() {return jQuery("input[name=aviberry_storage_user]");};
	var txtFileStoragePass = function() {return jQuery("input[name=aviberry_storage_pass]");};
	var txtFileStorageHost = function() {return jQuery("input[name=aviberry_storage_host]");};
	var txtFileStoragePort = function() {return jQuery("input[name=aviberry_storage_port]");};
	var txtFileStoragePath = function() {return jQuery("input[name=aviberry_storage_path]");};
	
	// Selects
	var selPresetDefault = function() {return jQuery("select[name=aviberry_preset_default]");};

	// Examples.
	var lblFileStorageHostExample = function() {return jQuery(".aviberry_param_example span", txtFileStorageHost().parent().parent());};
	var lblFileStoragePortExample = function() {return jQuery(".aviberry_param_example span", txtFileStoragePort().parent().parent());};
	var lblFileStoragePathExample = function() {return jQuery(".aviberry_param_example span", txtFileStoragePath().parent().parent());};

	// Span
	var spanFileStorageTypeDescription = function() {return jQuery("span#aviberry_fileStorageType_description");};
	var spanFileStorageFullUrl = function() {return jQuery("span#aviberry_fileStorage_fullUrl");};
	
	var signInDialogSelector = '#aviberry-options-signin-wrap';
	var signUpDialogSelector = '#aviberry-options-signup-dialog';
	var signInDialogSubmitSelector = signInDialogSelector + " .dialog_button_submit";
	var signInLoginSelector = '#aviberry_email';
	var signInPasswordSelector = '#aviberry_password_plaintext';
	
	var optionsWrapSelector = '#aviberry-options-notsignin-wrap';	
	var storageClassWrapSelector = "#aviberry-options-storage-class-wrap";
	var storageClassSelector = "input[name=aviberry_storage_class]";
	
	
	
	var validateEmail = function(email){
		var re = /\S+@\S+\.\S+/;
	    return re.test(email);
	};
	
	// admin API proxy.
	var rpcAdminSynchronous = new rpc.ServiceProxy(
		aviberryPlugin.AVIBERRY_PLUGIN_API_URL + 'admin.php', 
		{
			asynchronous:	false,
			sanitize:		true,
			protocol:		"JSON-RPC",
			methods: [
				"getAPICredentials",
				"setAPICredentialsDB"
			]
		}
	);
	
	
	// Dialog.
	var dlgFileStorageChangeConfirm = jQuery("<div></div>")
		.dialog({
			message:   "All info about current file storage will be reseted. Continue?", // Custom property.
			title:     "Aviberry Plugin",
			autoOpen:  false,
			modal:     true,
			resizable: false,
			open:      function(event, ui) {
				dlgFileStorageChangeConfirm.html(
					dlgFileStorageChangeConfirm.dialog('option', 'message')
				);
				
				var context = dlgFileStorageChangeConfirm.parents(".ui-dialog");
				jQuery(":button:contains('" + aviberryPlugin.i18n.ok + "')", context).focus();
			},
			buttons:   { 
				"Cancel": function() { 
					jQuery(this).dialog("close");
				},
				"Ok": function() {
					jQuery(this).dialog("close");
			
					var callBack = jQuery(this).dialog("option", "callBack");
					if(typeof callBack == "function")
						callBack();
				}
			}
		});
	
	/**
	 * Handler for action "Change file storage".
	 * 
	 * @return void
	 */
	var radioFileStorageType_OnClick = function(event) {
		
		if (isSpecifiedFileStorageInfo()){
			
			event.preventDefault();
			
			dlgFileStorageChangeConfirm.dialog(
				"option", 
				"callBack", 
				function(){
					changeFileStorage();
					event.target.click();
				}
			);
			dlgFileStorageChangeConfirm.dialog('open');
			
		} else
			changeFileStorage();
	};
	
	/**
	 * signInDialog_OnSubmit
	 * 
	 * @return boolean
	 */
	var signInDialog_OnSubmit = function() {
		var dialogSelector = signInDialogSelector;
		var error_elements = [];

		var result = false;

		// prevents sending of the form in case of errors
		try{
			dialogClearError(dialogSelector);

			var aviberry_api_host = jQuery.trim( jQuery("input[name=aviberry_api_host]", dialogSelector).val() );
			if(!aviberry_api_host)
					aviberry_api_host = aviberryPlugin.apiHost;
				
			var aviberry_api_key = jQuery.trim( jQuery("input[name=aviberry_api_key]", dialogSelector).val() );
			var aviberry_api_pass = jQuery.trim( jQuery("input[name=aviberry_api_pass]", dialogSelector).val() );

			// If credentials have been filled then save them and proceed to the settings page
			if(	aviberry_api_host && 
				aviberry_api_key &&
				aviberry_api_pass
			){
				dialogBeginAjaxLoading(dialogSelector);
				
				rpcAdminSynchronous.setAPICredentialsDB(aviberry_api_host, aviberry_api_key, aviberry_api_pass);
				
				dialogEndAjaxLoading(dialogSelector);
				
				result = true;
			
			// If credentials have NOT been filled then try to get the credentials from server
			} else {
			
				// check for email and password
				var login = jQuery.trim( jQuery(signInLoginSelector, dialogSelector).val() );
				if(login == "")
					error_elements.push(signInLoginSelector);

				var password = jQuery.trim( jQuery(signInPasswordSelector, dialogSelector).val() );
				if(password == "")
					error_elements.push(signInPasswordSelector);

				//if there is incorrect input data then show error and break
				if(error_elements.length > 0){
					dialogShowErrorMessage(dialogSelector, {message: aviberryPlugin.i18n.signInEmpty}, error_elements.join(","));
				
				// email and password have been set. Now try to get credentials API from the server.
				} else {
					
					dialogBeginAjaxLoading(dialogSelector);

					// server will save the credentials in case of success
					var credentials = rpcAdminSynchronous.getAPICredentials(login, password, false);

					dialogEndAjaxLoading(dialogSelector);

					result = true;
				}
			}
		
		} catch(e){
			dialogEndAjaxLoading(dialogSelector);
			dialogShowErrorMessage(dialogSelector, {message:e.message});
		}
		
		return result;
	};
	
	/**
	 * Changes file storage (GUI).
	 */
	var changeFileStorage = function(use_options) {
		lblFileStorageUser().html(aviberryPlugin.i18n.user); 
		lblFileStoragePass().html(aviberryPlugin.i18n.pass);
		lblFileStoragePath().html(aviberryPlugin.i18n.path);

		if (use_options) {
			radioFileStorageType().filter('[value=' + aviberryPlugin.storageType + ']').attr('checked', true);
			txtFileStorageUser().val(aviberryPlugin.storageUser); 
			txtFileStoragePass().val(aviberryPlugin.storagePass);
			txtFileStorageHost().val(aviberryPlugin.storageHost);
			txtFileStoragePort().val(aviberryPlugin.storagePort);
			txtFileStoragePath().val(aviberryPlugin.storagePath);
		} else {
			txtFileStorageUser().val(""); 
			txtFileStoragePass().val("");
			txtFileStorageHost().val("");
			txtFileStoragePort().val("");
			txtFileStoragePath().val("");
		}

		txtFileStorageHost().attr("disabled", false);		
		txtFileStoragePort().parents("tr").show();
		
		var sFileStorageType = radioFileStorageType().filter(':checked').val();		
		
		switch (sFileStorageType) {			
			case "wp_media_lib_ftp":
			case "wp_media_lib_ftps":
				lblFileStorageHostExample().html("example.com");
				lblFileStoragePortExample().html("21");
				lblFileStoragePath().html(aviberryPlugin.i18n.labelPathWordpressMediaLibrary);
				lblFileStoragePathExample().html(aviberryPlugin.i18n.examplePathToWordpressMediaLibrary);
				spanFileStorageTypeDescription().html(aviberryPlugin.i18n.typeMediaLibraryDescription);
				break;
			
			case "ftp":
			case "ftps":
				lblFileStorageHostExample().html("example.com");
				lblFileStoragePortExample().html("21");
				lblFileStoragePath().html(aviberryPlugin.i18n.labelStoragePath);
				lblFileStoragePathExample().html(aviberryPlugin.i18n.examplePath);
				spanFileStorageTypeDescription().html(aviberryPlugin.i18n.typeNotMediaLibraryDescription);
			
				break;
				
			case "s3":
				lblFileStorageUser().html(aviberryPlugin.i18n.awsKey); 
				lblFileStoragePass().html(aviberryPlugin.i18n.awsPass);
				lblFileStoragePath().html(aviberryPlugin.i18n.awsBucket);
				
				txtFileStorageHost().val(aviberryPlugin.AVIBERRY_S3_HOST);
				
				txtFileStorageHost().attr("disabled", true);
				txtFileStoragePort().parents("tr").hide();
				
				lblFileStorageHostExample().html(aviberryPlugin.AVIBERRY_S3_HOST);
				lblFileStoragePortExample().html("");
				lblFileStoragePath().html(aviberryPlugin.i18n.labelStoragePath);
				lblFileStoragePathExample().html(aviberryPlugin.i18n.exampleBacket);
				spanFileStorageTypeDescription().html(aviberryPlugin.i18n.typeNotMediaLibraryDescription);
				
				break;
		}
		makeFullUrl();
	};
	
	/**
	 * File storage class OnClick (GUI).
	 */
	var fileStorageClass_OnClick = function(event){
		
		if (isSpecifiedFileStorageInfo()){
			
			event.preventDefault();
			
			dlgFileStorageChangeConfirm.dialog(
				"option", 
				"callBack", 
				function(){
					changeFileStorage();
					event.target.click();
				}
			);
			dlgFileStorageChangeConfirm.dialog('open');
			
		} else {
			setfileStorageClass(event.target.value);
			// hack. One time click() does not fire handler onClick
			radioFileStorageType().filter(':visible').slice(0, 1).click().click(); //slice: wp 2.9.2 has jQuery 1.3.2 without method "first"
		}
	}
	
	/**
	 * Set File storage class (GUI).
	 */
	var setfileStorageClass = function(storageClass){
		if(storageClass){
			jQuery("#aviberry-options-storage-type-wrap td label[class!=aviberry-storage-class-" + storageClass + "]").hide().prev().hide();
			jQuery("#aviberry-options-storage-type-wrap td label[class=aviberry-storage-class-" + storageClass + "]").show().prev().show();
		}
	}
	
	/**
	 * Generate full path string to file storage
 	 */
	var makeFullUrl = function() {
		var sFileStorageType = radioFileStorageType().filter(':checked').val();		
		
		var protocol = '';
		var user = '';
		var pass = '';
		var host = '';
		var port = '';
		var path = '';
		
		switch (sFileStorageType) {			
			case "wp_media_lib_ftp":
				protocol = 'ftp://';
				break;
			case "wp_media_lib_ftps":				
				protocol = 'ftps://';
				break;
			case "ftp":
				protocol = 'ftp://';
				break;
			case "ftps":
				protocol = 'ftps://';
				break;
			case "s3":
				protocol = 'http://';
				break;
		}
		
		user = txtFileStorageUser().val();
		pass = txtFileStoragePass().val().replace(/./gi, '*');
		host = txtFileStorageHost().val();
		port = txtFileStoragePort().val();
		path = txtFileStoragePath().val();
		
		spanFileStorageFullUrl().html(
			protocol + (user?user+':'+pass:'') + (pass?'@':'') + host + (port?':'+port:'') + (host?'/':'') + path
		);
	};
	
	/**
	 * Returns whether all required info about storage is filled.
	 * 
	 * @return Boolean
	 */
	var changePresetDefault = function() {
		if(aviberryPlugin.presetDefault)
			selPresetDefault().val(aviberryPlugin.presetDefault);
	};
	
	
	/**
	 * Returns whether all required info about storage is filled.
	 * 
	 * @return Boolean
	 */
	var isSpecifiedFileStorageInfo = function() {
		var sFileStorageType = radioFileStorageType().filter(':checked').val();
		
		return Boolean(
			   txtFileStorageUser().val() 
			|| txtFileStoragePass().val()
			|| (txtFileStorageHost().val() && txtFileStorageHost().val() != aviberryPlugin.AVIBERRY_S3_HOST) // Ignore host for "S3".
			|| txtFileStoragePort().val()
			|| txtFileStoragePath().val()
		);
	};
	
	
	/**
	 * Reactivates all tooltips.
	 * 
	 * @return void
	 */
	var updateTooltips = function () {
		if (aviberryPlugin.displayTooltips) {
			jQuery("[title]").tooltip({showURL: false});
		}
	};


	var optionsTrialBuyNowLink = jQuery("#aviberry-options-trial-notice .aviberry-buynow-button");

	/**
	 * Update account info
	 * 
	 * @return void
	 */
	var watchAccountInfo = function(){
		var accountInfo = new AviberryAccountInfo();
		accountInfo.getAccountInfo(function(response){
			if(response.payment_status == aviberryPlugin.ACCOUNT_INFO_PAYMENT_STATUS_PAID){
				jQuery('div#aviberry-options-trial-notice').hide();
			}else{
				jQuery('div#aviberry-options-trial-notice').show();
				optionsTrialBuyNowLink.attr(
					"href",
					aviberryPlugin.BUY_NOW_URL + (
						response.email || response.firstname || response.lastname || response.company || response.phone ? 
							'&' +
							jQuery.param({
								'email': response.email,
								'firstname': response.firstname,
								'lastname': response.lastname,
								'company': response.company_name,
								'phone': response.phone
							}) :
							''
					)
				);
			}
			if(response.status == 'not_confirmed'){
				jQuery('#aviberry-options-notconfirmed-notice').show();
			}else{
				jQuery('#aviberry-options-notconfirmed-notice').hide();
			}
		});
		setTimeout(watchAccountInfo, aviberryPlugin.AVIBERRY_ACCOUNT_INFO_TIMEOUT);
	}


	// if there are API credentials then show options dialog
	if(	aviberryPlugin.apiKey && 
		aviberryPlugin.apiPass
	){
		jQuery('label[for=aviberry-storage-type-wp_media_lib_ftp]').text(' FTP');
		jQuery('label[for=aviberry-storage-type-wp_media_lib_ftps]').text(' FTPS');
		
		jQuery(storageClassSelector).click(fileStorageClass_OnClick);
		
		// storage class
		// set default value
		if(!jQuery(storageClassSelector + ':checked').val())
			jQuery(storageClassSelector).slice(0, 1).attr('checked', 'checked'); //slice: wp 2.9.2 has jQuery 1.3.2 without method "first"
		
		setfileStorageClass(jQuery(storageClassSelector + ':checked').val());
		
		// storage type
		// radio on click
		radioFileStorageType().click(radioFileStorageType_OnClick);
		// set default value
		if(!radioFileStorageType().filter(':checked').val())
			radioFileStorageType().slice(0, 1).attr('checked', 'checked'); //slice: wp 2.9.2 has jQuery 1.3.2 without method "first"

		// Update storage url label on change
		// user
		txtFileStorageUser().change(makeFullUrl);
		// password
		txtFileStoragePass().change(makeFullUrl);
		// host
		txtFileStorageHost().change(makeFullUrl);
		// port
		txtFileStoragePort().change(makeFullUrl);
		// path
		txtFileStoragePath().change(makeFullUrl);
		
		// Prepare tooltips.
		updateTooltips();

		// Setup initially GUI for current options of plugin.
		changeFileStorage(true);
		changePresetDefault();
		
		// show storage class radio
		jQuery(storageClassWrapSelector).show();
		// show storage url label
		jQuery("#aviberry-storage-url-wrap").show();
		
		if(aviberryPlugin.accountInfo.status == 'not_confirmed'){
			jQuery('#aviberry-options-notconfirmed-notice').show();
		}else{
			jQuery('#aviberry-options-notconfirmed-notice').hide();
		}
		
		// if API credentials was set then update account info
		if(	aviberryPlugin.apiKey && 
			aviberryPlugin.apiPass && 
			aviberryPlugin.apiHost
		)
			watchAccountInfo();
		
		
	// if there are NO API credentials then show sign in dialog
	} else {

		var signUpDialog_OnSubmit = function(){

			jQuery("#aviberry-signup-error").text('').hide();
			
			var email	= jQuery.trim(jQuery(signUpDialogSelector + " #aviberry_signup_email").val());
			var pass	= jQuery.trim(jQuery(signUpDialogSelector + " #aviberry_signup_password").val());

			if( !jQuery(signUpDialogSelector + " #aviberry_signup_confirm").is(':checked') ){
				jQuery("#aviberry-signup-error").text(aviberryPlugin.i18n.errorAgreePolice).show();
				return;
			}
			if( !email.length || !pass.length ){
				jQuery("#aviberry-signup-error").text(aviberryPlugin.i18n.errorEmptyEmailAndPassword).show();
				return;
			}
			if( !validateEmail(email) ){
				jQuery("#aviberry-signup-error").text(aviberryPlugin.i18n.errorIncorrectEmail).show();
				return;
			}
			
			try{
				dialogBeginAjaxLoading(signUpDialogSelector);
				rpcAdminSynchronous.getAPICredentials(email, pass);
				jQuery(signUpDialogSelector).dialog( "close" );
				location.reload();
			} catch(e){
				jQuery("#aviberry-signup-error").text(e.message.toString()).show();
				dialogEndAjaxLoading(signUpDialogSelector);			
			}
		};

		jQuery(signInDialogSubmitSelector).click(signInDialog_OnSubmit);
		
		jQuery(optionsWrapSelector).hide();
		jQuery(signInDialogSelector).show(); 
		jQuery('#aviberry-options-sigup-link').click(function(){
			jQuery(signUpDialogSelector).dialog({
				modal: true,
				resizable: false,
				width: 285,
				height: 'auto',
				close: function(event, ui){
					jQuery(this).dialog('destroy');
				}
			})
			.show();
		
		});
		
		jQuery(signUpDialogSelector + ' .aviberry_dialog_button_submit').click(signUpDialog_OnSubmit);
	}
	
	
}

jQuery(document).ready(function() {
	var aviberry = new AviberrySettings();
});
