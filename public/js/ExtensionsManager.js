/**
 * FH-Complete
 *
 * @package
 * @author
 * @copyright	Copyright (c) 2016-2021
 * @license	GPLv3
 * @link	https://fhcomplete.net
 * @since	Version 1.0.0
 */

/**
 * Toggle the status of an extension
 */
function toggleExtension(extensionId, enabled)
{
	FHC_AjaxClient.ajaxCallPost(
		"system/extensions/Manager/toggleExtension",
		{
			extension_id: extensionId,
			enabled: enabled
		},
		{
			successCallback: function(data, textStatus, jqXHR) {
				if (FHC_AjaxClient.hasData(data) && FHC_AjaxClient.getData(data) === true)
				{
					FHC_DialogLib.alertSuccess(FHC_PhrasesLib.t("extensions", "changeSuccess"));
				}
				else
				{
					FHC_DialogLib.alertError(FHC_PhrasesLib.t("extensions", "changeError"));
				}
			},
			errorCallback: function(data) {
				FHC_DialogLib.alertError(FHC_PhrasesLib.t("extensions", "changeError"));
			}
		}
	);
}

/**
 * Delete an extension
 * cellRow: tabulator row reference
 */
function deleteExtension(extensionId, cellRow)
{
	FHC_AjaxClient.ajaxCallPost(
		"system/extensions/Manager/delExtension",
		{
			extension_id: extensionId
		},
		{
			successCallback: function(data, textStatus, jqXHR) {
				if (FHC_AjaxClient.hasData(data) && FHC_AjaxClient.getData(data) === true)
				{
					cellRow.delete(); // delete the row from the tabulator
					FHC_DialogLib.alertSuccess(FHC_PhrasesLib.t("extensions", "changeSuccess"));
				}
				else
				{
					FHC_DialogLib.alertError(FHC_PhrasesLib.t("extensions", "changeError"));
				}
			},
			errorCallback: function() {
				FHC_DialogLib.alertError(FHC_PhrasesLib.t("extensions", "changeError"));
			}
		}
	);
}

/**
 * When JQuery is up
 */
$(document).ready(function() {

	$("#uploadExtension").click(function() {
		$("form").submit();
	});

});

