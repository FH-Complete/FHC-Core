// ********************************************************
// JS used by view system/messages/htmlWriteTemplate
// ********************************************************

function tinymcePreviewSetContent()
{
	if ($("#tinymcePreview"))
	{
		if ($("#recipients").children(":selected").val() > -1)
		{
			parseMessageText($("#recipients").children(":selected").val(), tinyMCE.get("bodyTextArea").getContent());
		}
		else
		{
			tinyMCE.get("tinymcePreview").setContent("");
		}
	}
}

function parseMessageText(receiver_id, text)
{
	FHC_AjaxClient.ajaxCallPost(
		"system/messages/Messages/parseMessageText",
		{
			receiver_id: receiver_id,
			text: text,
			type: $("#type").val()
		},
		{
			successCallback: function(data, textStatus, jqXHR) {

				if (FHC_AjaxClient.hasData(data))
				{
					tinyMCE.get("tinymcePreview").setContent(FHC_AjaxClient.getData(data));
				}
				else if (FHC_AjaxClient.isError(data))
				{
					FHC_DialogLib.alertError(data.retval);
				}
			}
		}
	);
}

$(document).ready(function ()
{

	tinymce.init({
		theme : "advanced",
		mode : "specific_textareas",
		editor_selector: "bodyTextArea",
		theme_advanced_buttons2: "undo, redo, outdent, indent, bullist, numlist, link",
		theme_advanced_buttons3: "",
		theme_advanced_buttons4: "",
		theme_advanced_toolbar_location: "top",
		height: "400px",
	});

	tinymce.init({
		theme : "advanced",
		mode : "specific_textareas",
		editor_selector: "tinymcePreview",
		height: 400,
		readonly: 1,
	});

	if ($("#variables"))
	{
		$("#variables").dblclick(function ()
		{
			if ($("#bodyTextArea"))
			{
				//if editor active add at cursor position, otherwise at end
				if (tinymce.activeEditor.id === "bodyTextArea")
					tinymce.activeEditor.execCommand('mceInsertContent', false, $(this).children(":selected").val());
				else
					tinyMCE.get("bodyTextArea").setContent(tinyMCE.get("bodyTextArea").getContent() + $(this).children(":selected").val());
			}
		});
	}

	if ($("#user_fields"))
	{
		$("#user_fields").dblclick(function ()
		{
			if ($("#bodyTextArea"))
			{
				//if editor active add at cursor position, otherwise at end
				if (tinymce.activeEditor.id === "bodyTextArea")
					tinymce.activeEditor.execCommand('mceInsertContent', false, $(this).children(":selected").val());
				else
					tinyMCE.get("bodyTextArea").setContent(tinyMCE.get("bodyTextArea").getContent() + $(this).children(":selected").val());
			}
		});
	}

	if ($("#recipients"))
	{
		$("#recipients").change(tinymcePreviewSetContent);
	}

	if ($("#refresh"))
	{
		$("#refresh").click(tinymcePreviewSetContent);
	}

	if ($("#sendButton") && $("#sendForm"))
	{
		$("#sendButton").click(function ()
		{
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

	if ($("#vorlageDnD"))
	{
		$("#vorlageDnD").change(function ()
		{
			var vorlage_kurzbz = this.value;

			if (vorlage_kurzbz != '')
			{
				FHC_AjaxClient.ajaxCallGet(
					"system/messages/Messages/getVorlage",
					{
						vorlage_kurzbz: vorlage_kurzbz
					},
					{
						successCallback: function(data, textStatus, jqXHR) {

							if (FHC_AjaxClient.hasData(data))
							{
								var msg = FHC_AjaxClient.getData(data);

								tinyMCE.get("bodyTextArea").setContent(msg[0].text);
								$("#subject").val(msg[0].subject);
							}
						}
					}
				);
			}
		});
	}

	$("#subject").focus();

});
