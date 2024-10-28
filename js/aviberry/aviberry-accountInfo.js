
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
 * "AviberryAccountInfo" scope and functionality.
 */
function AviberryAccountInfo() {

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
				"getAccountInfo"
			]
		}
	);
	
	/**
	 * Updates Account info on the page
	 * 
	 * @return void
	 */
	var setAccountInfo = function(info){
		var newStatus = (
			info && 
			info.payment_status
		) ? 
			info.payment_status : 
			null;
		
		if(newStatus){
			aviberryPlugin.accountInfo = info;
		}
	}

	/**
	 * Get account info and run callback
	 * 
	 * @param function callback
	 * @return void
	 */
	this.getAccountInfo = function(callback){
		rpcAviberry.getAccountInfo({
			onSuccess: function(response) {
				if(typeof callback === 'function')
					callback(response);
				setAccountInfo(response);
			},
			onException: function(e) {
				//alert('exception:'+e.toString());
			}
		});
	}
}