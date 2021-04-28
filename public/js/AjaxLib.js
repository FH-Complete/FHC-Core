/**
 * FH-Complete
 *
 * @package
 * @author
 * @copyright   Copyright (c) 2016 fhcomplete.org
 * @license GPLv3
 * @link    https://fhcomplete.org
 * @since	Version 1.0.0
 */

//--------------------------------------------------------------------------------------------------------------------
// Configs

// To see debug messages into the browser console set this parameter as true
const DEBUG = false;

// Default veil timeout (milliseconds)
const VEIL_TIMEOUT = 1000;

//--------------------------------------------------------------------------------------------------------------------
// Constants

// Success
const SUCCESS = 0;

// Properties present in a response
const CODE = "error";
const RESPONSE = "retval";

// HTTP method parameters
const HTTP_GET_METHOD = "GET";
const HTTP_POST_METHOD = "POST";

const REMOTE_CONTROLLER = "remoteController";

const FHC_CONTROLLER_ID = "fhc_controller_id";

/**
 * Definition and initialization of object FHC_AjaxClient
 */
var FHC_AjaxClient = {
	//------------------------------------------------------------------------------------------------------------------
	// Properties

	_veilCallersCounter: 0, // count the number of callers that want to activate the veil

	//------------------------------------------------------------------------------------------------------------------
	// Public methods

	/**
	 * Performs a call using the HTTP GET method
	 * controllerParameters is an object
	 * ajaxCallParameters is an object
	 */
	ajaxCallGet: function(remoteController, controllerParameters, ajaxCallParameters) {
	    FHC_AjaxClient._ajaxCall(remoteController, controllerParameters, HTTP_GET_METHOD, ajaxCallParameters);
	},

	/**
	 * Performs a call using the HTTP POST method
	 * controllerParameters is an object
	 * ajaxCallParameters is an object
	 */
	ajaxCallPost: function(remoteController, controllerParameters, ajaxCallParameters) {
	    FHC_AjaxClient._ajaxCall(remoteController, controllerParameters, HTTP_POST_METHOD, ajaxCallParameters);
	},

	/**
	 * Checks if the response is a success
	 */
	isSuccess: function(response) {
		var isSuccess = false;

	    if (jQuery.type(response) == "object" && response.hasOwnProperty(CODE) && response.hasOwnProperty(RESPONSE))
	    {
	        if (response.error == SUCCESS)
	        {
	            isSuccess = true;
	        }
	    }

		return isSuccess;
	},

	/**
	 * Checks if the response is an error
	 */
	isError: function(response) {
		return !FHC_AjaxClient.isSuccess(response);
	},

	/**
	 * Checks if the response has data
	 */
	hasData: function(response) {
		var hasData = false;

	    if (FHC_AjaxClient.isSuccess(response))
	    {
			if ((jQuery.type(response.retval) == "object" && !jQuery.isEmptyObject(response.retval))
				|| (jQuery.isArray(response.retval) && response.retval.length > 0)
				|| (jQuery.type(response.retval) == "string" && response.retval.trim() != "")
				|| jQuery.type(response.retval) == "number")
			{
				hasData = true;
			}
	    }

		return hasData;
	},

	/**
	 * Retrives data from response object
	 */
	getData: function(response) {
		var data = null;

	    if (FHC_AjaxClient.hasData(response))
	    {
			data = response.retval;
	    }

		return data;
	},

	/**
	 * Retrives error message from response object
	 */
	getError: function(response) {
		var error = "Generic error";

	    if (jQuery.type(response) == "object" && !jQuery.isEmptyObject(response) && response.hasOwnProperty(RESPONSE))
	    {
			error = response.retval;
	    }

		return error;
	},

	/**
	 * Retrives code from response object
	 */
	getCode: function(response) {
		var code = 1; // Generic error

		if (jQuery.type(response) == "object" && response.hasOwnProperty(CODE))
	    {
	        code = response.error;
	    }

		return code;
	},

	/**
	 * Show a veil
	 */
	showVeil: function(veilTimeout) {
		if (typeof veilTimeout == "number")
		{
			FHC_AjaxClient._veilTimeout = veilTimeout;
		}
		else
		{
			FHC_AjaxClient._veilTimeout = VEIL_TIMEOUT;
		}
		FHC_AjaxClient._showVeil();
	},

	/**
	 * Hide a veil that was shown before
	 */
	hideVeil: function() {
		FHC_AjaxClient._hideVeil();
	},

	/**
	 * Retrives parameters from URL query string (HTTP GET parameters)
	 */
	getUrlParameter: function(sParam) {
	    var sPageURL = decodeURIComponent(window.location.search.substring(1)),
	        sURLVariables = sPageURL.split("&"),
	        sParameterName,
	        i;

	    for (var i = 0; i < sURLVariables.length; i++)
		{
	        sParameterName = sURLVariables[i].split("=");

	        if (sParameterName[0] === sParam)
			{
	            return sParameterName[1];
	        }
	    }
	},

	//------------------------------------------------------------------------------------------------------------------
	// Private methods

	/**
	 * Generate the router URI using the connection parameters
	 */
	_generateRouterURI: function(remoteController) {
		var uri = null;

		// Checks if global JS object FHC_JS_DATA_STORAGE_OBJECT exists
		if (typeof FHC_JS_DATA_STORAGE_OBJECT !== "undefined")
		{
			uri = FHC_JS_DATA_STORAGE_OBJECT.app_root + FHC_JS_DATA_STORAGE_OBJECT.ci_router + "/" + remoteController;
		}

		return uri;
	},

	/**
	 * Method to print debug info after a controller has been called
	 */
	_printDebug: function(parameters, response, errorThrown) {

		if (DEBUG === true) // If global const DEBUG is true, but really true!
		{
			// Print info about called controller
			console.log("Called controller: " + parameters.remoteController);
			console.log("Call parameters:"); // parameters given to this call
			console.log(parameters);

			if (response != null) // if there is a response...
			{
				console.log("Controller Response:");
				console.log(response); // ...print it
			}
			if (errorThrown != null) // if there is a jQuery error...
			{
				console.log("jQuery error:");
				console.log(errorThrown); // ...print it
			}
			console.log("--------------------------------------------------------------------------------------------");
		}
	},

	/**
	 * Method to call if the ajax call has succeeded
	 */
	_onSuccess: function(response, textStatus, jqXHR) {

		FHC_AjaxClient._printDebug(this._data, response); // debug time!

		// Call the success callback saved in _successCallback property
		// NOTE: this is not referred to FHC_AjaxClient but to the ajax object
		this._successCallback(response);
	},

	/**
	 * Method to call if the ajax call has raised an error
	 */
	_onError: function(jqXHR, textStatus, errorThrown) {

		FHC_AjaxClient._printDebug(this._data, null, errorThrown); // debug time!

		// Call the error callback saved in _errorCallback property
		// NOTE: this is not referred to FHC_AjaxClient but to the ajax object
	    this._errorCallback(jqXHR, textStatus, errorThrown);
	},

	/**
	 * Method to call after the ajax call has ended
	 */
	_onComplete: function(jqXHR, textStatus) {

		FHC_AjaxClient._printDebug(this._data, null, jqXHR.responseJSON); // debug time!

		// Call the complete callback if it was saved in the _completeCallback property
		// NOTE: this is not referred to FHC_AjaxClient but to the ajax object.
		//		It's known that it's a function because it was already checked before in the
		//		_checkAndGenerateAjaxParams method
		if (this.hasOwnProperty("_completeCallback"))
		{
			this._completeCallback(jqXHR, textStatus);
		}

		FHC_AjaxClient._hideVeil(); // finally hide the veil
	},

	/**
	 * If an error callback is not given, this is the default error callback that is used
	 * to display useful info about the occurred error. It uses the JQuery UI dialog
	 */
	_defaultErrorCallback: function(jqXHR, textStatus, errorThrown) {

		// Row table format
		var tableRowFormat = "<tr><td class=\"fhc-ajaxclient-error-td\"><b>%1s: </b></td><td>%2s</td></tr>";
		var strDivDialog = "<div id=\"fhc-ajaxclient-dialog\"><table>"; // dialog div and open the error table

		// If textStatus is usable then place it in the table
		if (textStatus != null) strDivDialog += tableRowFormat.replace(/%1s/g, "Error").replace(/%2s/g, textStatus);

		// If errorThrown is usable then place it in the table
		if (errorThrown != null) strDivDialog += tableRowFormat.replace(/%1s/g, "Error text").replace(/%2s/g, errorThrown);

		// If jqXHR.status is usable then place it in the table
		if (jqXHR != null && jqXHR.hasOwnProperty("status"))
		{
			strDivDialog += tableRowFormat.replace(/%1s/g, "HTTP status").replace(/%2s/g, jqXHR.status);
		}

		// If jqXHR.responseText is usable then place it in the table
		if (jqXHR != null && jqXHR.hasOwnProperty("responseText"))
		{
			strDivDialog += tableRowFormat.replace(/%1s/g, "HTTP response").replace(/%2s/g, jqXHR.responseText);
		}

		strDivDialog += "</table></div>"; // close table and div

		$(strDivDialog).appendTo("body"); // append the dialog div to the body

		// Dialog definition
		$("#fhc-ajaxclient-dialog").dialog({
			title: "Error occurred",
			dialogClass: "no-close",
			autoOpen: true,
			modal: true,
			resizable: false,
			height: "auto",
			width: 700,
			closeOnEscape: false,
			buttons: [{
				text: "Ok",
				click: function() {
					$(this).dialog("close");
				}
			}]
		});
	},

	/**
	 * Instantiate a new object and copy in it the properties from the parameter
	 */
	_cpObjProps: function(obj) {
	    var returnObj = {};

	    for (var prop in obj)
	    {
	        returnObj[prop] = obj[prop];
	    }

	    return returnObj;
	},

	/**
	 * Method to show the veil
	 */
	_showVeil: function() {
		if (FHC_AjaxClient._veilCallersCounter == 0)
		{
			$("<div class=\"fhc-ajaxclient-veil\"></div>").appendTo("body");
		}

		FHC_AjaxClient._veilCallersCounter++;
	},

	/**
	 * Method to hide the veil
	 */
	_hideVeil: function() {
		window.setTimeout(function() {
			if (FHC_AjaxClient._veilCallersCounter >= 0)
			{
				if (FHC_AjaxClient._veilCallersCounter > 0)
				{
					FHC_AjaxClient._veilCallersCounter--;
				}

				if (FHC_AjaxClient._veilCallersCounter == 0)
				{
					$(".fhc-ajaxclient-veil").remove();
				}
			}
		},
		this._veilTimeout);
	},

	/**
	 * Check if controllerParameters has a FileList of uploaded file(s).
	 *
	 * @param controllerParameters
	 * 			Example: {
	 * 			    name1: value,
	 * 			    name2: value,
	 * 			    files: $(selector)[0].files --> this is the FileList
	 * 			}
	 * @returns {boolean}
	 * @private
	 */
	_hasFileList(controllerParameters){
		return Object.values(controllerParameters)
			.some((value) => value instanceof FileList === true);
	},

	/**
	 * Returns a FormData object. Useful for passing uploaded files via AJAX.
	 *
	 * @param controllerParameters
	 * @returns {FormData}
	 * @private
	 */
	_convertToFormDataObject: function(controllerParameters)
	{
		// The new FormData instance
		const formData = new FormData();

		// Loop through controllerParameters
		for (const [key, value] of Object.entries(controllerParameters)) {

			// When FileList is found ( parameter with uploaded file(s))
			if (value instanceof FileList)
			{
				// Loop through uploaded files
				for (let file of value)
				{
					// Append file to FormData object (if more than 1 file, append as array)
					formData.append(value.length == 1 ? key : key + '[]', file);
				}
			}
			else
			{
				// For any other then FileList, just append to FormData object
				formData.append(key, value);
			}
		}
		return formData;
	},

	/**
	 * Checks call parameters, if they are present and are valid
	 * It generates and returns all the parameters needed to perform an ajax remote call
	 * NOTE: console.error is used here because those are not messages for the final user,
	 *		but for the web interface developer
	 */
	_checkAndGenerateAjaxParams: function(remoteController, controllerParameters, type, ajaxCallParameters) {

	    var valid = true; // by default they are ok (we want to trust you, please do not betray it)

		// Returned parameters
		var ajaxParameters = {
			cache: false, // data are never cached by the browser
			dataType: "json", // always json!
			type: type // set HTTP method, GET or POST
		};

		// remoteController must be a NON-empty string
	    if (typeof remoteController == "string" && remoteController.trim() != "")
	    {
			// Is it possible to generate the URL
			if ((url = FHC_AjaxClient._generateRouterURI(remoteController)) != null)
			{
				ajaxParameters.url = url;
			}
			else // but it could fail
			{
				console.error("FHC_JS_DATA_STORAGE_OBJECT is not present");
				valid = false;
			}
	    }
		else // otherwise is NOT possible to generate the URL
		{
			console.error("Invalid remoteController parameter");
	        valid = false;
		}

	    // controllerParameters must be an object
	    if (typeof controllerParameters == "object")
	    {
			// If controllerParameters contains uploaded file(s) as FileList
			if (FHC_AjaxClient._hasFileList(controllerParameters))
			{
				// Convert controllerParameters to FormData object to easily pass uploaded files via AJAX
				var data = FHC_AjaxClient._convertToFormDataObject(controllerParameters);	// data is a FormData object now

				// Add options to tell jQuery not to process data or worry about content-type
				ajaxParameters.processData = false;
				ajaxParameters.contentType = false;
			}
			// Anything else
			else
			{
				// Copy the properties of controllerParameters into a new object
				var data = FHC_AjaxClient._cpObjProps(controllerParameters);
			}

			// fhc_controller_id is given if present
			data[FHC_CONTROLLER_ID] = FHC_AjaxClient.getUrlParameter(FHC_CONTROLLER_ID);

			// Stores them into ajaxParameters
			// NOTE: property data is not possible to get later,
			//		so the variable data is saved also in _data and it will be used later
			ajaxParameters.data = data;
			ajaxParameters._data = data;
	    }
		else
		{
			console.error("Invalid controller parameters, must be an object");
			valid = false;
		}


		// Checks if ajaxCallParameters is an object
	    if (typeof ajaxCallParameters == "object")
	    {
			// If present, errorCallback must be a function
		    if (ajaxCallParameters.hasOwnProperty("errorCallback"))
			{
				if (typeof ajaxCallParameters.errorCallback == "function")
				{
					ajaxParameters._errorCallback = ajaxCallParameters.errorCallback; // save as property the callback error
					ajaxParameters.error = FHC_AjaxClient._onError; // function to call if an error occurred
				}
				else
				{
					console.error("Invalid errorCallback, it must be a function");
					valid = false;
				}
		    }
			else // if is not given then call the default errorCallback
			{
				ajaxParameters._errorCallback = FHC_AjaxClient._defaultErrorCallback; // save as property the callback error
				ajaxParameters.error = FHC_AjaxClient._onError; // function to call if an error occurred
			}

			// If present, successCallback must be a function
		    if (ajaxCallParameters.hasOwnProperty("successCallback"))
		    {
				if (typeof ajaxCallParameters.successCallback == "function")
				{
					ajaxParameters._successCallback = ajaxCallParameters.successCallback; // save as property the callback success
					ajaxParameters.success = FHC_AjaxClient._onSuccess; // function to call if succeeded
				}
				else
				{
					console.error("Invalid successCallback, it must be a function");
					valid = false;
				}
		    }

			// If present, completeCallback must be a function
		    if (ajaxCallParameters.hasOwnProperty("completeCallback"))
		    {
				if (typeof ajaxCallParameters.completeCallback == "function")
				{
					ajaxParameters._completeCallback = ajaxCallParameters.completeCallback; // save as property the callback complete
				}
				else
				{
					console.error("Invalid completeCallback, it must be a function");
					valid = false;
				}
		    }

			// If present, veilTimeout must be a number and cannot be less then 0 or greater then 60000
			if (ajaxCallParameters.hasOwnProperty("veilTimeout") && typeof ajaxCallParameters.veilTimeout == "number")
			{
				if (ajaxCallParameters.veilTimeout > 0 && ajaxCallParameters.veilTimeout < 60000)
				{
					ajaxParameters._veilTimeout = ajaxCallParameters.veilTimeout;
					ajaxParameters.beforeSend = FHC_AjaxClient._showVeil;
				}
				else if(ajaxCallParameters.veilTimeout == 0)
				{
					// veil is disabled
				}
				else
				{
					console.error("Invalid veilTimeout parameter, must be a number >= 0 and <= 60000");
					valid = false;
				}
			}
			else // is not present or the value is invalid
			{
				ajaxParameters._veilTimeout = VEIL_TIMEOUT;
				ajaxParameters.beforeSend = FHC_AjaxClient._showVeil;
			}

			// Function to call after the ajax call is ended, is it here because it must be always called
			ajaxParameters.complete = FHC_AjaxClient._onComplete;
		}

		if (valid === false)
		{
			ajaxParameters = null;
		}

	    return ajaxParameters;
	},

	/**
	 * Performs a call to the server were the CI PHP layer is running
	 * - remoteController: alias of the core controller to call
	 * - controllerParameters: parameters to give to the called controller
	 * - type: POST or GET HTTP method
	 * - ajaxCallParameters is an object and could contains:
	 *	- errorCallback: function to call after an error has been raised
	 *	- successCallback: function to call after succeeded
	 *	- veilTimeout: veil timeout
	 */
	_ajaxCall: function(remoteController, controllerParameters, type, ajaxCallParameters) {
		// Retrives the parameters for the ajax call
		var ajaxParameters = FHC_AjaxClient._checkAndGenerateAjaxParams(remoteController, controllerParameters, type, ajaxCallParameters);

		// Checks the given parameters if they are present and are valid
	    if (ajaxParameters != null)
	    {
	        $.ajax(ajaxParameters); // ajax call
	    }
	}
};
