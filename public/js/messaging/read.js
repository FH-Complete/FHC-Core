// ***********************************************************************
// List all personal messages, used by view system/messages/ajaxRead
// ***********************************************************************

// Global variable that contains tha tabulator instance
var tableMessageLst;
var selectedToggleMessage = "received";

/**
 * Use DialogLib to display a Generic error
 */
function readMessagesGenericError()
{
	FHC_DialogLib.alertError(FHC_PhrasesLib.t("global", "genericError"));
}

/**
 * Gets all the received messages
 */
function getReceivedMessages()
{
	tableMessageLst.hideColumn("to");
	tableMessageLst.showColumn("from");

	_getMessages(FHC_JS_DATA_STORAGE_OBJECT.called_path + "/listReceivedMessages");
}

/**
 * Gets all the sent messages
 */
function getSentMessages()
{
	tableMessageLst.hideColumn("from");
	tableMessageLst.showColumn("to");

	_getMessages(FHC_JS_DATA_STORAGE_OBJECT.called_path + "/listSentMessages");
}

/**
 * Change the TinyMCE content
 */
function changeTinyMCE(row)
{
	if (row == null)
	{
		tinyMCE.get("readMessagePanel").setContent('');
	}
	else
	{
		tinyMCE.get("readMessagePanel").setContent(row.getData().body);
	}
}

/**
 * Called on Tabulator row click event
 * - If a clicked message is unread thes is set as read
 * - Change the TinyMCE content with the clicked message body
 */
function rowClick(e, row)
{
	// If in received mode and the message is not unread
	if (selectedToggleMessage == "received" && row.getData().status == "0")
	{
		FHC_AjaxClient.ajaxCallPost(
			FHC_JS_DATA_STORAGE_OBJECT.called_path + "/setMessageRead",
			{
				message_id: row.getData().message_id,
				statusPersonId: row.getData().statusPersonId
			},
			{
				successCallback: function(data, textStatus, jqXHR) {

					if (FHC_AjaxClient.isSuccess(data))
					{
						rowFormatter(row, "normal"); // format row
						row.getData().status = "1"; // update status to read
					}
					else
					{
						readMessagesGenericError();
					}
				},
				errorCallback: readMessagesGenericError,
				veilTimeout: 300
			}
		);
	}

	// If NOT in send mode
	if (selectedToggleMessage == "received")
	{
		$("#replyMessage").show();
	}

	changeTinyMCE(row); // Change TinyMCE content
}

/**
 * Radio button click event to switch between received and sent messages
 */
function toggleMessages()
{
	if ($(this)[0].id == "received")
	{
		selectedToggleMessage = "received";
		getReceivedMessages();
	}
	else
	{
		selectedToggleMessage = "send";
		$("#replyMessage").hide();
		getSentMessages();
	}

	changeTinyMCE(null); // clean tinymce
	tableMessageLst.redraw(); // redraw table after its content is changed
}

/**
 * Formats tabulator rows
 */
function rowFormatter(row, fontWeight = 700)
{
	if (row.getData().status == "0")
	{
		var cells = row.getElement().childNodes;

		for (var i = 0; i < cells.length; i++)
		{
			cells[i].style.fontWeight = fontWeight;
		}
	}
}

/**
 * Get received/sent messages and change tabulator content
 */
function _getMessages(getMessagesURL)
{
	tableMessageLst.replaceData(Array());

	FHC_AjaxClient.ajaxCallGet(
		getMessagesURL,
		null,
		{
			successCallback: function(data, textStatus, jqXHR) {

				if (FHC_AjaxClient.hasData(data))
				{
					var messages = null;

					try
					{
						messages = JSON.parse(FHC_AjaxClient.getData(data));
					}
					catch (syntaxError) {}

					if ($.isArray(messages))
					{
						try
						{
							tableMessageLst.replaceData(messages);
						}
						catch (syntaxError)
						{
							readMessagesGenericError();
						}
					}
				}
			},
			errorCallback: readMessagesGenericError,
			veilTimeout: 300
		}
	);
}

/**
 * Open new tab/window to write a new message
 */
function writeNewMessage()
{
	window.location = FHC_JS_DATA_STORAGE_OBJECT.app_root +
		FHC_JS_DATA_STORAGE_OBJECT.ci_router + "/" +
		FHC_JS_DATA_STORAGE_OBJECT.called_path +
		"/write";
}

/**
 * Open new tab/window to reply to a received message
 */
function replyMessage()
{
	var selectedMessages = tableMessageLst.getSelectedData();

	if ($.isArray(selectedMessages))
	{
		var selectedMessage = selectedMessages[0];

		window.open("writeReply?token=" + selectedMessage.token, "_blank");
	}
	else //
	{
		FHC_DialogLib.alertInfo(FHC_PhrasesLib.t("ui", "pleaseSelectMessage"));
	}
}

/**
 * Start me up!
 */
$(document).ready(function () {

	$("#replyMessage").hide();

	// TinyMCE initialization
	tinymce.init({
		selector: "#readMessagePanel",
		plugins: "autoresize",
		menubar: false,
		toolbar: false,
		statusbar: false,
		readonly: 1,
		autoresize_min_height: 300,
		max_height: 600,
		autoresize_bottom_margin: 0
	});

	// Tabulator initialization
	tableMessageLst = new Tabulator("#lstMessagesPanel", {
		height: "270px",
		layout: "fitColumns",
		selectable: 1,
		layoutColumnsOnNewData: true,
		columns: [
			{title: "Subject", field: "subject", responsive: 0},
			{title: "From", field: "from", visible: false},
			{title: "To", field: "to", visible: false},
			{title: "Date", field: "sent", sorter: "datetime"}
		],
		rowClick: rowClick,
		rowFormatter: rowFormatter
	});

	// Bind radio buttons click event with toggleMessages function
	$("#toggleMessages input").click(toggleMessages);

	// Bind write a new message button
	$("#writeMessage").click(writeNewMessage);

	// Bind reply to a message button
	$("#replyMessage").click(replyMessage);

	// First retrieve the received message and populate the tabulator
	getReceivedMessages();

});
