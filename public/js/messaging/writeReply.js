// ****************************************************************************************
// Write a reply to a received message, used by view system/messages/ajaxWriteReply
// ****************************************************************************************

/**
 *
 */
function sendReply()
{
	FHC_AjaxClient.ajaxCallPost(
		FHC_JS_DATA_STORAGE_OBJECT.called_path + '/sendMessageReply',
		{
			receiver_id: $('#receiver_id').val(),
			relationmessage_id: $('#relationmessage_id').val(),
			token: $('#token').val(),
			subject: $('#subject').val(),
			body: tinyMCE.get("body").getContent()
		},
		{
			successCallback: function(data, textStatus, jqXHR) {

				FHC_DialogLib.alertSuccess("Message sent succesfully");
			},
			errorCallback: function() {
				FHC_DialogLib.alertError("Error");
			},
			veilTimeout: 300
		}
	);
}

/**
 *
 */
$(document).ready(function () {

	//
	tinymce.init({
		selector: "#body",
		plugins: "autoresize, link",
		toolbar: "undo redo | presentation | bold italic | link | alignleft aligncenter alignright alignjustify | outdent indent",
		max_height: 600,
		autoresize_min_height: 150,
		autoresize_max_height: 600,
		autoresize_bottom_margin: 10
	});

	$('#sendButton').click(sendReply);

});
