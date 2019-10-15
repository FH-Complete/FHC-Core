// ***********************************************************************
// List all personal messages, used by view system/messages/ajaxRead
// ***********************************************************************

// Global variable that contains tha tabulator instance
var tableMessageLst;

/**
 * Use DialogLib to display a Generic error
 */
function readMessagesGenericError()
{
	FHC_DialogLib.alertError("An error occurred while retrieving message, contact the website administrator");
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
	tinyMCE.get("readMessagePanel").setContent(row.getData().body);
}

/**
 * Called on Tabulator row click event
 * - If a clicked message is unread thes is set as read
 * - Change the TinyMCE content with the clicked message body
 */
function rowClick(e, row)
{
	// If the message is unread
	if (row.getData().status == "0")
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
						rowFormatter(row, "normal");
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

	changeTinyMCE(row); // Change TinyMCE content
}

/**
 * Radio button click event to switch between received and sent messages
 */
function toggleMessages()
{
	$(this)[0].id == "received" ? getReceivedMessages() : getSentMessages();

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
	FHC_AjaxClient.ajaxCallGet(
		getMessagesURL,
		null,
		{
			successCallback: function(data, textStatus, jqXHR) {

				if (FHC_AjaxClient.hasData(data))
				{
					var messages = FHC_AjaxClient.getData(data);

					if ($.isArray(messages))
					{
						try
						{
							tableMessageLst.replaceData(JSON.parse());
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
 * Start!
 */
$(document).ready(function () {

	// TinyMCE initialization
	tinymce.init({
		selector: "#readMessagePanel",
		plugins: "autoresize",
		menubar: false,
		toolbar: false,
		statusbar: false,
		readonly: 1,
		autoresize_min_height: 200,
		autoresize_bottom_margin: 0
	});

	// Tabulator initialization
	tableMessageLst = new Tabulator("#lstMessagesPanel", {
		height: "400px",
		layout: "fitColumns",
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

	// First retrieve the received message and populate the tabulator
	getReceivedMessages();

});
