/**
 * JS used by view system/messages/messageWriteReply
 */

$(document).ready(function ()
{
	tinymce.init({
		selector: "#bodyTextArea",
		plugins: "autoresize, link",
		toolbar: "undo redo | presentation | bold italic | link | alignleft aligncenter alignright alignjustify | outdent indent",
		max_height: 600,
		autoresize_min_height: 150,
		autoresize_max_height: 600,
		autoresize_bottom_margin: 10,
		auto_focus: "bodyTextArea"
	});

	if ($("#sendButton") && $("#sendForm"))
	{
		$("#sendButton").click(function () {

			if ($("#subject") && $("#subject").val() != '' && tinyMCE.get("bodyTextArea").getContent() != '')
			{
				$("#sendForm").submit();
			}
			else
			{
				FHC_DialogLib.alertInfo("Subject and text are required fields!");
			}
		});
	}
});
