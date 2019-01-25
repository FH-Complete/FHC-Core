const CONTROLLER_URL = FHC_JS_DATA_STORAGE_OBJECT.app_root + FHC_JS_DATA_STORAGE_OBJECT.ci_router + "/system/Messages";

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
	$.ajax({
		dataType: "json",
		url: CONTROLLER_URL + "/parseMessageText",
		data: {
			"person_id": receiver_id,
			"text": text
		},
		success: function(data, textStatus, jqXHR)
		{
			tinyMCE.get("tinymcePreview").setContent(data);
		},
		error: function(jqXHR, textStatus, errorThrown)
		{
			alert(textStatus + " - " + errorThrown + " - " + jqXHR.responseText);
		}
	});
}

$(document).ready(function ()
{
	tinymce.init({
		selector: "#bodyTextArea",
		plugins: "autoresize",
		autoresize_min_height: 150,
		autoresize_max_height: 600,
		autoresize_bottom_margin: 10
	});

	tinymce.init({
		menubar: false,
		toolbar: false,
		statusbar: false,
		readonly: 1,
		selector: "#tinymcePreview",
		plugins: "autoresize",
		autoresize_min_height: 150,
		autoresize_bottom_margin: 10
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
				alert("Subject and text are required fields!");
			}
		});
	}

	if ($("#vorlageDnD"))
	{
		$("#vorlageDnD").change(function ()
		{
			if (this.value != '')
			{
				$.ajax({
					dataType: "json",
					url: CONTROLLER_URL + "/getVorlage",
					data: {
						"vorlage_kurzbz": this.value
					},
					success: function(data, textStatus, jqXHR)
					{
						tinyMCE.get("bodyTextArea").setContent(data.retval[0].text);
						$("#subject").val(data.retval[0].subject);
					},
					error: function(jqXHR, textStatus, errorThrown)
					{
						alert(textStatus + " - " + errorThrown);
					}
				});
			}
		});
	}

	$("#subject").focus();

});
