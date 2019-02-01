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
		var html = "<p class='dialogmessage'><i class='glyphicon glyphicon-ok-sign'></i>&nbsp;&nbsp;"+message+"</p>";
		FHC_DialogLib.alertDefault('Success', html);
		$(".ui-dialog-titlebar").addClass("alert-success text-center");
		$(".glyphicon-ok-sign").css("color", "#3c763d");
		FHC_DialogLib._formatShortDialog();
	},
	/**
	 * Show error message as jQueryUI alert. Works only with bootstrap.
	 * @param message
	 */
	alertError: function(message)
	{
		var html = "<p class='dialogmessage'><i class='glyphicon glyphicon-warning-sign'></i>&nbsp;&nbsp;"+message+"</p>";
		FHC_DialogLib.alertDefault('Error occured', html);
		$(".ui-dialog-titlebar").addClass("alert-danger text-center");
		$(".glyphicon-warning-sign").css("color", "#a94442");
		FHC_DialogLib._formatShortDialog();
	},
	/**
	 * Show info message as jQueryUI alert. Works only with bootstrap.
	 * @param message
	 */
	alertInfo: function(message)
	{
		var html = "<p class='dialogmessage'><i class='glyphicon glyphicon-info-sign'></i>&nbsp;&nbsp;"+message+"</p>";
		FHC_DialogLib.alertDefault('Info', html);
		$(".ui-dialog-titlebar").addClass("alert-info text-center");
		$(".glyphicon-info-sign").css("color", "#245269");
		FHC_DialogLib._formatShortDialog();
	},
	/**
	 * Default jQueryUI alert
	 * @param title shown as message box heading
	 * @param html shown inside message box
	 * @param width of the message box
	 */
	alertDefault: function(title, html, width)
	{
		var strDivDialog = "<div id=\"fhc-dialoglib-dialog\">";
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
					$(this).dialog("close");
				}
			}]
		});
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
	}

};
