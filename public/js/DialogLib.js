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

var FHC_DialogLib = {

	/**
	 * Show success message as jQueryUI alert. Works only with bootstrap.
	 * @param message
	 */
	alertSuccess: function(message)
	{
		FHC_DialogLib._displayDialog('Success', message, 'ok-sign', 'alert-success');
	},

	/**
	 * Show error message as jQueryUI alert. Works only with bootstrap.
	 * @param message
	 */
	alertError: function(message)
	{
		FHC_DialogLib._displayDialog('Error occured', message, 'warning-sign', 'alert-danger');
	},

	/**
	 * Show warning message as jQueryUI alert. Works only with bootstrap.
	 * @param message
	 */
	alertWarning: function(message)
	{
		FHC_DialogLib._displayDialog('Warning', message, 'warning-sign', 'alert-warning');
	},

	/**
	 * Show info message as jQueryUI alert. Works only with bootstrap.
	 * @param message
	 */
	alertInfo: function(message)
	{
		FHC_DialogLib._displayDialog('Info', message, 'info-sign', 'alert-info');
	},

	/**
	 * Default jQueryUI alert
	 * @param title shown as message box heading
	 * @param html shown inside message box
	 * @param width of the message box
	 */
	alertDefault: function(title, html, width)
	{
		var dialogdiv = $("#fhc-dialoglib-dialog");

		if (dialogdiv.length)
			dialogdiv.remove();

		var strDivDialog = "<div id='fhc-dialoglib-dialog'>";
		strDivDialog += html;
		strDivDialog += "</div>";

		$(strDivDialog).appendTo("body"); // append the dialog div to the body

		$("#fhc-dialoglib-dialog").dialog({
			title: title,
			dialogClass: "no-close",
			autoOpen: true,
			modal: true,
			resizable: false,
			height: "auto",
			width: width,
			minWidth: 300,
			closeOnEscape: false,
			buttons: [{
				text: "Ok",
				click: function() {
					$("#fhc-dialoglib-dialog").remove();
				}
			}]
		});
	},

	/**
	 * displays the dialog box
	 * @param heading
	 * @param message
	 * @param icon
	 * @param colorClass for heading and icon
	 * @private
	 */
	_displayDialog: function(heading, message, icon, colorClass)
	{
		var html = "<p class='dialogmessage'><i class='glyphicon glyphicon-"+icon+"'></i>&nbsp;&nbsp;"+message+"</p>";
		FHC_DialogLib.alertDefault(heading, html);
		$(".ui-dialog-titlebar").addClass(colorClass+" text-center");
		$(".glyphicon-"+icon).addClass(colorClass);
		FHC_DialogLib._formatShortDialog();
	},

	/**
	 * formats jQueryUI messagebox as "short", i.e. containing only one line of text,
	 * centers the text
	 * @private
	 */
	_formatShortDialog: function()
	{
		$(".ui-dialog-title").width("100%");
		$(".ui-dialog-buttonpane.ui-widget-content").css("padding", ".3em .4em .5em .4em");
		$(".ui-dialog .ui-dialog-content").css("padding", "0");
		$(".ui-dialog-buttonset button").css("margin", "0");
		$(".dialogmessage i.glyphicon").css("background-color", "transparent");
	}

};
