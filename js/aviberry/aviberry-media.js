
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
 * "AviberryMediaLibrary" scope and functionality.
 */
function AviberryMediaLibrary() {
	// Running conversions.
	var postConversions = new Object();
	
	// Aviberry API's proxy.
	var rpcAviberry = new rpc.ServiceProxy(
		aviberryPlugin.AVIBERRY_PLUGIN_API_URL, 
		{
			user:			aviberryPlugin.apiKey,
			password:		aviberryPlugin.apiPass,
			asynchronous:	true,      
			sanitize:		true,      
			protocol:		"JSON-RPC",
			methods: [
				"startConversion",
				"cancelConversion",
				"getProgress",
				"getAccountInfo"
			]
		}
	);
	
	// Plugin API synchronous.
	var rpcPlugin = new rpc.ServiceProxy(
		aviberryPlugin.AVIBERRY_PLUGIN_API_URL, 
		{
			user:			aviberryPlugin.apiKey,
			password:		aviberryPlugin.apiPass,
			asynchronous:	false, // !!! 
			sanitize:		true,
			protocol:		"JSON-RPC",
			methods: [ 
				"finishPostConversion"
			]
		}
	);



	/**
	 * Returns conversion id by post id.
	 * 
	 * @param Number post_id
	 * @return Number
	 */
	var getConversionByPost = function(post_id) {
		return postConversions[post_id];
	};

	/**
	 * Returns post id by conversion id. Function is reverse to  
	 * getConversionByPost.
	 * 
	 * @param Number conversion_id
	 * @return Number 
	 */
	var getPostByConversion = function(conversion_id) {
		for (var post_id in postConversions)
			if (postConversions[post_id] == conversion_id)
				return post_id;
	};
	
	
	var conversionDlgBuyNowLink = jQuery("#aviberry_conversion_dlg_buynow_wrap .aviberry-buynow-button");
	
	// "Convert" action link for specified post.
	var linkConvertFile = function (post_id) {return jQuery(".aviberry-conversion-link[post_id=" + post_id + "]");};
	var progressPanel   = function (post_id) {return jQuery("#aviberry-conversion-progress-" + post_id);};
	var progressMessage = function (post_id) {return jQuery("#aviberry-conversion-progress-" + post_id + " span.aviberry-message");};
	//var progressBar     = function (post_id) {return jQuery("#aviberry-conversion-progress-" + post_id + " div.aviberry-bar");};

	var postTitle = function (post_id) {
		
		return jQuery.trim(
			jQuery(
				"tr#post-" + post_id + " td.title a, " +  //wp 3.4
				"tr#post-" + post_id + " td.column-media a" //wp 2.9.2
			).slice(0, 1) // wp 2.9.2 has jQuery 1.3.2 without method "first"
			.text()
		);
	}

	
	// Dialog to select options and confirm start of conversion.
	var dlgStartPostConversion = jQuery("#dlg_start_post_conversion")
		.dialog({
			message:       "",   // Custom property.
			post_id:       null, // Custom property.
			filename:      null, // Custom property.
			conversion_id: null, // Custom property.
			
			title:     "Aviberry Plugin",
			autoOpen:  false,
			modal:     true,
			width:     400,
			resizable: false,
			
			open: function(event, ui) {
				// Set dialog message.
				jQuery(".message", this).html(
					jQuery(this).dialog("option", "message").replace(
						"{$filename}", 
						jQuery(this).dialog("option", "filename")
					)
				);
				
				var isPaid = jQuery(this).dialog("option", "isPaid");
				
				// if NOT convering 
				if (jQuery(this).dialog("option", "conversion_id") == undefined){
					
					// show preset list
					jQuery("#aviberry_preset_id").show();
					
					// Free account area
					if (isPaid || isPaid == undefined)
						jQuery("#aviberry_conversion_dlg_free").hide();
					else
						jQuery("#aviberry_conversion_dlg_free").show();
					
				// if convering 
				}else{
					// hide preset list
					jQuery("#aviberry_preset_id").hide();
					
					// hide free account area
					jQuery("#aviberry_conversion_dlg_free").hide();
				}
				
				// Set button "OK" focused by default.
				var context = jQuery(this).parents(".ui-dialog");
				jQuery(":button:contains('Ok')", context).focus();
			},
			
			buttons: { 
				"Cancel": function() {
					jQuery(this).dialog("close");
				},
				"Ok": function() {
					jQuery(this).dialog("close");
				
					// Save selected preset id into dialog attribute.
					jQuery(this).dialog("option", "preset_id", jQuery("#aviberry_preset_id").val());
					
					if (jQuery(this).dialog("option", "conversion_id") == null) {
						// Start file conversion.
						startPostConversion(jQuery(this).dialog("option", "post_id"));
					} else
						// Cancel file conversion.
						cancelPostConversion(jQuery(this).dialog("option", "post_id"));
				}
			}
	});

	/**
	 * Handler to start file conversion.
	 * 
	 * @return Boolean
	 */
	var linkConvertFile_OnClick = function() {
		var post_id			= jQuery(this).attr("post_id");
		var title			= postTitle(post_id);
		var conversion_id	= jQuery(this).attr("conversion_id");
		
		if (conversion_id == undefined) {
			// Confirm to start file conversion.
			dlgStartPostConversion.dialog("option", "message", "Convert file \"{$filename}\" to format:");
			dlgStartPostConversion.dialog("option", "conversion_id", null); 
		} else {
			// Confirm to cancel file conversion.
			dlgStartPostConversion.dialog("option", "message", aviberryPlugin.i18n.cancelPostConversion);
			dlgStartPostConversion.dialog("option", "conversion_id", conversion_id); 
		}
		dlgStartPostConversion.dialog("option", "post_id",  post_id);
		dlgStartPostConversion.dialog("option", "filename", title);
		dlgStartPostConversion.dialog("open");
		
		return false;
	};

	/**
	 * Alters WordPress' media table. There is no suitable hook in PHP to modify
	 * the table, so this is done by JS. Function also does actions to
	 * initialize certain data in the table. 
	 * 
	 * @return void
	 */
	var processMediaTable = function () {
		var table = jQuery("#the-list").parent();
		
		//
		// Add new column "Conversion progress".
		//
		
		// Append column headers on top and bottom of table.
		var th = 
			  '<th class="manage-column aviberry-column-conversion-progress" scope="col">' 
			+ aviberryPlugin.i18n.conversionProgress 
			+ '</th>';
		jQuery("thead > tr", table).append(th);
		jQuery("tfoot > tr", table).append(th);
		
		//
		// Init hash of running conversions.
		//
		
		// Run by all posts in table.
		var rows = jQuery("tbody > tr", table);
		for (var i = 0; i < rows.length; i++) {
			var post_id       = rows[i].id.substring(5);
			var conversion_id = linkConvertFile(post_id).attr("conversion_id");
			var thumbnail_url = linkConvertFile(post_id).attr("thumbnail_url");
	
			// At the end of each row append column cell to hold conversion progress.
			jQuery(rows[i]).append(
				  '<td class="aviberry-column-conversion-progress">'
					+ '<div id="aviberry-conversion-progress-' + post_id + '">'
						+ '<div class="aviberry-bar"></div>'
						+ '<span class="aviberry-message"></span>'
					+ '</div>'
				+ '</td>'
			);

			// Hide progress by default.
			progressPanel(post_id).hide();
			
			//if there is thumbnail then show it
			if(thumbnail_url){
				var img = jQuery(rows[i]).find(".column-icon img.attachment-80x60");
				if(img){
					img.removeAttr("width");
					img.attr("src", thumbnail_url);
				}
			}
			
			if (conversion_id) {
				// Init conversion on client side.
				postConversions[post_id] = conversion_id;
				// Update GUI.
				progressMessage(post_id).text(aviberryPlugin.i18n.startPostConversion);
				//progressBar(post_id).progressbar({value: 0}); // right way: progressbar({value: 78})
				progressPanel(post_id).show();				
			}
		}
	};

	/**
	 * Formats time in human-readable string.
	 * 
	 * @param Number time
	 * @return String
	 */
	var formatTime = function (time) {
		if (time == 0)
			time = 0; // Do nothing. 
		else if (time < 10)
			time = aviberryPlugin.i18n.few_secs;
		else if (time < 60)
			time = aviberryPlugin.i18n.less_min;
		else {
			timeH = parseInt(time / (60 * 60));
			timeM = parseInt((time - timeH * 60 * 60) / 60);
			timeS = time - timeH * 60 * 60 - timeM * 60;
			time = ""
				+ (timeH ? timeH + " " + aviberryPlugin.i18n.h + " " : "")
				+ (timeM ? timeM + " " + aviberryPlugin.i18n.m + " " : "")
				+ (timeS ? timeS + " " + aviberryPlugin.i18n.s + " " : "");
		}
		
		return typeof time == "string" ? time.replace(/(^\s+)|(\s+$)/g, "") : time;  // trim() if string.
	};
	

	/**
	 * Does required actions after conversion of file post_id. Conversion can be 
	 * successfully or unsuccessfully (with error) complete, or canceled. After
	 * all this actions finishPostConversion is called.
	 * 
	 * @param Number post_id
	 * @param string conversion_id
	 * @param integer error_code
	 * @param string error_message
	 * 
	 * @return void
	 */
	var finishPostConversion = function(post_id, conversion_id, error_code, error_message) {
		try {
			// Synchronous call to cancel conversion on plugin's server side.
			rpcPlugin.finishPostConversion(post_id, conversion_id, error_code, error_message);
		} catch (e) {
			// Error.
			progressMessage(post_id).text(aviberryPlugin.i18n.epicFail + ": " + e.message);
			progressMessage(post_id).addClass('aviberry-fail');
		}
		
		// Whatever cancel conversion on plugin's client side.
		delete postConversions[post_id]; 
		linkConvertFile(post_id).removeAttr("conversion_id");

		//
		// Update GUI.
		//
		
		linkConvertFile(post_id).html(aviberryPlugin.i18n.convert);
		linkConvertFile(post_id).attr("title", aviberryPlugin.i18n.tooltipConvert);
		updateTooltips();

		progressPanel(post_id);
	};
		
	/**
	 * Cancels conversion of file post_id.
	 * 
	 * @param Number post_id
	 * @return void
	 */
	var cancelPostConversion = function (post_id) {
		// First, get conversion id before local cancel.
		var conversion_id = getConversionByPost(post_id);
		
		// Cancel locally.
		finishPostConversion(post_id, conversion_id, 0, "Cancelled");
		
		// Update GUI.
		//progressBar(post_id).progressbar({value: 0});
		progressMessage(post_id).text(aviberryPlugin.i18n.progressCanceling);

		// Cancel remotely.
		rpcAviberry.cancelConversion({
			params: {
				"conversion_id": conversion_id
			},
			onSuccess: function (response) {
				progressMessage(post_id).text(aviberryPlugin.i18n.conversionCanceled);
			},
			onException: function(error) {
				// Error.
				progressMessage(post_id).text(aviberryPlugin.i18n.epicFail + ": " + e.message);
				progressMessage(post_id).addClass('aviberry-fail');
			}
		});
	};
	
	/**
	 * Starts conversion of file post_id. 
	 * 
	 * @param Number post_id 
	 * @return void
	 */
	var startPostConversion = function (post_id) {
		var title = postTitle(post_id);
		var source_url  = linkConvertFile(post_id).attr("filelink");
		var preset_id = dlgStartPostConversion.dialog("option", "preset_id");
		
		
		//
		// Update GUI.
		//
		
		progressMessage(post_id).removeClass('aviberry-success');
		progressMessage(post_id).removeClass('aviberry-fail');
		
		progressMessage(post_id).text(aviberryPlugin.i18n.initPostConversion);
		//progressBar(post_id).progressbar({value: 0});
		progressPanel(post_id).fadeIn('slow');
		
		//
		// Call API.
		//
		
		rpcAviberry.startConversion({
			params: {
				"post_id":	post_id,
				"source_url":    source_url,
				"preset": {
					"preset_id": preset_id
				},
				"data": {
					"title": title
				}
			},
			onSuccess: function (response) {
				var conversion_id = response.conversion_id;
				
				try {
					// If all is ok (no exception), start conversion on plugin's client side.
					postConversions[post_id] = conversion_id;
					linkConvertFile(post_id).attr("conversion_id", conversion_id);
					
					//
					// Update GUI.
					//
					
					linkConvertFile(post_id).html(aviberryPlugin.i18n.cancel);
					linkConvertFile(post_id).attr("title", aviberryPlugin.i18n.tooltipCancel);
					updateTooltips();
					
					progressMessage(post_id).text(aviberryPlugin.i18n.startPostConversion);
					
				} catch (e) {
					// Error.
					progressMessage(post_id).text(aviberryPlugin.i18n.epicFail + ": " + e.message);
					progressMessage(post_id).addClass('aviberry-fail');
				}
			},
			onException: function(e) {
				// Error.
				progressMessage(post_id).text(aviberryPlugin.i18n.epicFail + ": " + e.message);
				progressMessage(post_id).addClass('aviberry-fail');
				return true;
			}
		});
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
	
	/**
	 * Updates progress of conversions.
	 * 
	 * @return void
	 */
	var watchPostConversions = function () {
		var sids = [];
		// Run throw active conversions.
		for (var post_id in postConversions){
				var conversion_id = getConversionByPost(post_id);
				if (conversion_id)
					sids.push(conversion_id);
		}
		
		//if there are sids then get progress from Aviberry.
		if(sids.length > 0)							
			rpcAviberry.getProgress({
				params: {
					"conversion_id": sids
				},
				onSuccess: function(response) {
					
					for (var i in response) {							
						var conversion_id = response[i].conversion_id;
						var post_id       = getPostByConversion(conversion_id);
						if (typeof post_id == "undefined")
							continue;
						
						// Process progress.
						switch (response[i].status) {
							case "notfound":
								//progressBar(post_id).progressbar({value: 100});
								progressMessage(post_id)
									.text("Not found")
									.removeClass('aviberry-success')
									.addClass('aviberry-fail');

								finishPostConversion(post_id, conversion_id, response[i].error_code, response[i].error_message);
								
								break;
								
							case "queued":
								//progressBar(post_id).progressbar({value: 0});
								progressMessage(post_id)
									.removeClass('aviberry-success aviberry-fail')
									.text(aviberryPlugin.i18n.progressQueued);
								
								break;
								
							case "downloading":
								//progressBar(post_id).progressbar({value: response[i].percent});
								progressMessage(post_id)
									.removeClass('aviberry-success aviberry-fail')
									.text(aviberryPlugin.i18n.progressDownloading + " " + response[i].percent + "%");
								
								break;
								
							case "converting":
								var timeLeft = aviberryPlugin.i18n.timeLeft.replace("{$time}", formatTime(response[i].remaining_time)); 
								
								//progressBar(post_id).progressbar({value: response[i].percent});
								progressMessage(post_id)
									.removeClass('aviberry-success aviberry-fail')
									.text(
										aviberryPlugin.i18n.progressConverting + " " 
										+ timeLeft.replace(/^.{1}/, " " + timeLeft.charAt(0).toLocaleUpperCase()) // First char to upper case.
									);
								
								break;
								
							case "uploading":
								//progressBar(post_id).progressbar({value: response[i].percent});
								progressMessage(post_id)
									.removeClass('aviberry-success aviberry-fail')
									.text(aviberryPlugin.i18n.progressUploading + " " + response[i].percent + "%");
								
								break;
								
							case "finished":
								//progressBar(post_id).progressbar({value: 100});

								// No error.
								if (response[i].error_code == 0) {
									
									var message = aviberryPlugin.i18n.epicWin;
									
									if(	aviberryPlugin.storageType == 'wp_media_lib_ftp' ||
										aviberryPlugin.storageType == 'wp_media_lib_ftps')
										message += '. ' + aviberryPlugin.i18n.pleaseReloadThePage;

									progressMessage(post_id)
										.removeClass('aviberry-fail')
										.text(message)
										.addClass('aviberry-success');
								// Error.
								} else {
									progressMessage(post_id)
										.removeClass('aviberry-success')
										.text(aviberryPlugin.i18n.epicFail + ": " + response[i].error_message)
										.addClass('aviberry-fail');
								}
		
								finishPostConversion(post_id, conversion_id, response[i].error_code, response[i].error_message);
								
								break;
								
							case "canceled":
								//progressBar(post_id).progressbar({value: 100});
								progressMessage(post_id)
									.removeClass('aviberry-success aviberry-fail')
									.text(aviberryPlugin.i18n.conversionCanceled);
		
								finishPostConversion(post_id, conversion_id, response[i].error_code, response[i].error_message);
								
								break;
								
							default:
								
								break;
						}
					}
					setTimeout(watchPostConversions, aviberryPlugin.AVIBERRY_WATCH_TIMEOUT);
				},
				onException: function(e) {
					
					for (var i in sids){
						var post_id = getPostByConversion(sids[i]);
						if(post_id){
							progressMessage(post_id).text(aviberryPlugin.i18n.progressFailed + ": " + e.message);
							progressMessage(post_id).addClass('aviberry-fail');
						}
					}
					
					setTimeout(watchPostConversions, aviberryPlugin.AVIBERRY_WATCH_TIMEOUT);
					
					return true;
				}
			});
		else
			setTimeout(watchPostConversions, aviberryPlugin.AVIBERRY_WATCH_TIMEOUT);
	};
	
	// Assign handler to "Convert" action.
	jQuery(".aviberry-conversion-link").click(linkConvertFile_OnClick);
	
	// Prepare tooltips.
	updateTooltips();
	
	// Process existent table by JS because there is no appropriate hooks in PHP.
	processMediaTable();
	
	/**
	 * Updates Account info
	 * 
	 * @return void
	 */
	var watchAccountInfo = function(){
		var accountInfo = new AviberryAccountInfo();
		accountInfo.getAccountInfo(function(response){

			dlgStartPostConversion.dialog(
				"option", 
				"isPaid", 
				response.payment_status == aviberryPlugin.ACCOUNT_INFO_PAYMENT_STATUS_PAID
			);

			conversionDlgBuyNowLink.attr(
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
		});
		setTimeout(watchAccountInfo, aviberryPlugin.AVIBERRY_ACCOUNT_INFO_TIMEOUT);
	};
	
	if(aviberryPlugin.isInstallationCompleted){
		// Watch started file conversions.
		watchPostConversions();
		watchAccountInfo();
	}
}

jQuery(document).ready(function () {
	var aviberry = new AviberryMediaLibrary();
});
