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
		FHC_DialogLib.alertWarning(FHC_PhrasesLib.t("global", "notValidOE"));
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
						window.location = FHC_JS_DATA_STORAGE_OBJECT.app_root +
							FHC_JS_DATA_STORAGE_OBJECT.ci_router + "/" +
							FHC_JS_DATA_STORAGE_OBJECT.called_path +
							"/read";
					}
					else
					{
						FHC_DialogLib.alertError(FHC_PhrasesLib.t("global", "genericError"));
					}
				},
				errorCallback: function() {
					FHC_DialogLib.alertError(FHC_PhrasesLib.t("global", "genericError"));
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
