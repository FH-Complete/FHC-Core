// ****************************************************************************************
// Write a message to an organisation unit, used by view system/messages/ajaxWrite
// ****************************************************************************************

/**
 *
 */
function sendMessageToOU()
{
	if ($('#organisationUnit').val() == 0)
	{
		FHC_DialogLib.alertWarning("Not valid organisation unit");
	}
	else
	{
		FHC_AjaxClient.ajaxCallPost(
			FHC_JS_DATA_STORAGE_OBJECT.called_path + '/sendMessageToOU',
			{
				receiverOU: $('#organisationUnit').val(),
				subject: $('#subject').val(),
				body: tinyMCE.get("body").getContent()
			},
			{
				successCallback: function(data, textStatus, jqXHR) {

					if (FHC_AjaxClient.isSuccess(data))
					{
						FHC_DialogLib.alertSuccess("Message sent succesfully");
					}
					else
					{
						FHC_DialogLib.alertError("Error");
					}
				},
				errorCallback: function() {
					FHC_DialogLib.alertError("Error");
				},
				veilTimeout: 300
			}
		);
	}
}

/**
 *
 */
$(document).ready(function () {

	//
	tinymce.init({
		selector: "#body",
		plugins: "autoresize",
		autoresize_min_height: 150,
		autoresize_max_height: 600,
		autoresize_bottom_margin: 10
	});

	$('#sendButton').click(sendMessageToOU);

});
