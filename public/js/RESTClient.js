/**
 * FH-Complete
 *
 * @package	FHC-Helper
 * @author	FHC-Team
 * @copyright	Copyright (c) 2022 fhcomplete.net
 * @license	GPLv3
 * @link	https://fhcomplete.net
 * @since	Version 1.0.0
 */

//--------------------------------------------------------------------------------------------------------------------
// Configs

// To see debug messages into the browser console set this parameter as true
const CORE_REST_CLIENT_DEBUG = false;

// Default timeout (milliseconds)
const CORE_REST_CLIENT_TIMEOUT = 1000;

//--------------------------------------------------------------------------------------------------------------------
// Constants

// Success
const CORE_REST_CLIENT_SUCCESS = 0;

// Properties present in a response
const CORE_REST_CLIENT_ERROR = "error";
const CORE_REST_CLIENT_RETVAL = "retval";

// HTTP method parameters
const CRC_HTTP_GET_METHOD = "get";
const CRC_HTTP_POST_METHOD = "post";

/**
 * Definition and initialization of the object CoreRESTClient
 */
export const CoreRESTClient = {
	//------------------------------------------------------------------------------------------------------------------
	// Public methods

	/**
	 * Performs a call using the HTTP GET method
	 * wsParameters is an object
	 * axiosParameters is an object
	 */
	get: function(wsURL, wsParameters, axiosParameters = null) {
		return CoreRESTClient._axiosCall(wsURL, wsParameters, CRC_HTTP_GET_METHOD, axiosParameters);
	},

	/**
	 * Performs a call using the HTTP POST method
	 * wsParameters is an object
	 * axiosParameters is an object
	 */
	post: function(wsURL, wsParameters, axiosParameters = null) {
		return CoreRESTClient._axiosCall(wsURL, wsParameters, CRC_HTTP_POST_METHOD, axiosParameters);
	},

	/**
	 * Checks if the response is a success
	 */
	isSuccess: function(response) {

		if (typeof response === "object" && response.hasOwnProperty(CORE_REST_CLIENT_ERROR)
			&& response.hasOwnProperty(CORE_REST_CLIENT_RETVAL) && response.error == CORE_REST_CLIENT_SUCCESS)
		{
			return true;
		}

		return false;
	},

	/**
	 * Checks if the response is an error
	 */
	isError: function(response) {
		return !CoreRESTClient.isSuccess(response);
	},

	/**
	 * Checks if the response has data
	 */
	hasData: function(response) {

		if (CoreRESTClient.isSuccess(response))
		{
			if ((typeof response[CORE_REST_CLIENT_RETVAL] === "object" && Object.keys(response[CORE_REST_CLIENT_RETVAL]).length > 0)
				|| (typeof response[CORE_REST_CLIENT_RETVAL] === "array" && response[CORE_REST_CLIENT_RETVAL].length > 0)
				|| (typeof response[CORE_REST_CLIENT_RETVAL] === "string" && response[CORE_REST_CLIENT_RETVAL].trim() != "")
				|| typeof response[CORE_REST_CLIENT_RETVAL] === "number")
			{
				return true;
			}
		}

		return false;
	},

	/**
	 * Retrives data from response object
	 */
	getData: function(response) {

		if (CoreRESTClient.hasData(response))
		{
			return response[CORE_REST_CLIENT_RETVAL];
		}

		return null;
	},

	/**
	 * Retrives error message from response object
	 */
	getError: function(response) {

		if (typeof response[CORE_REST_CLIENT_RETVAL] === "object"
			&& Object.keys(response[CORE_REST_CLIENT_RETVAL]).length > 0
			&& response.hasOwnProperty(CORE_REST_CLIENT_RETVAL))
		{
			return response[CORE_REST_CLIENT_RETVAL];
		}

		return "Generic error";
	},

	/**
	 * Retrives code from response object
	 */
	getErrorCode: function(response) {

		if (typeof response[CORE_REST_CLIENT_RETVAL] === "object" && response.hasOwnProperty(CORE_REST_CLIENT_ERROR))
		{
			return response[CORE_REST_CLIENT_ERROR];
		}

		return 1; // Generic error
	},

	//------------------------------------------------------------------------------------------------------------------
	// Private methods

	/**
	 * Generate the router URI using the connection parameters
	 */
	_generateRouterURI: function(wsURL) {
		var uri = null;

		// Checks if global JS object FHC_JS_DATA_STORAGE_OBJECT exists
		if (typeof FHC_JS_DATA_STORAGE_OBJECT !== "undefined")
		{
			uri = FHC_JS_DATA_STORAGE_OBJECT.app_root + FHC_JS_DATA_STORAGE_OBJECT.ci_router + "/" + wsURL;
		}

		return uri;
	},

	/**
	 * Method to print debug info after a controller has been called
	 */
	_printDebug: function(parameters, response, errorThrown) {

		if (CORE_REST_CLIENT_DEBUG === true) // If global const CORE_REST_CLIENT_DEBUG is true, but really true!
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
	 * Performs a call to the server were the CI PHP layer is running
	 * - wsURL: alias of the core controller to call
	 * - wsParameters: parameters to give to the called controller
	 * - type: POST or GET HTTP method
	 * - axiosParameters: an object to configure the axios call
	 */
	_axiosCall: function(wsURL, wsParameters, type, axiosParameters) {

		// Axios config object
		let axiosCallObj = {
			method: type,
			url: CoreRESTClient._generateRouterURI(wsURL),
			timeout: CORE_REST_CLIENT_TIMEOUT // default time out
		};

		//
		if (type == CRC_HTTP_GET_METHOD)
		{
			axiosCallObj.params = wsParameters;
		}
		else
		{
			axiosCallObj.headers = { "Content-Type": "multipart/form-data" };
			axiosCallObj.data = wsParameters;
		}

		// Check if axiosParameters is an object
		if (typeof axiosParameters === "object")
		{
			// And then copies the its properties into axiosCallObj
	                for (var prop in axiosParameters) axiosCallObj[prop] = axiosParameters[prop];
		}

		console.log(axiosCallObj);

		// Perform the ajax call via axios
		return axios(axiosCallObj);
	}
};

