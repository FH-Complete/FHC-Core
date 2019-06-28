// ***********************************************************************
// List all personal messages, used by view system/messages/ajaxRead
// ***********************************************************************

//
var tableMessageLst;

/**
 *
 */
function genericError()
{
	FHC_DialogLib.alertError("An error occurred while retrieving message, contact the website administrator");
}

/**
 *
 */
function getReceivedMessages()
{
	_getMessages(FHC_JS_DATA_STORAGE_OBJECT.called_path + '/listReceivedMessages');
}

/**
 *
 */
function getSentMessages()
{
	_getMessages(FHC_JS_DATA_STORAGE_OBJECT.called_path + '/listSentMessages');
}

/**
 *
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
					try
					{
						tableMessageLst.replaceData(JSON.parse(FHC_AjaxClient.getData(data)));
					}
					catch (syntaxError)
					{
						genericError();
					}
				}
			},
			errorCallback: genericError,
			veilTimeout: 300
		}
	);
}

/**
 *
 */
function changeTinyMCE(e, row)
{
	tinyMCE.get("readMessagePanel").setContent(row._row.data.body);
}

/**
 *
 */
function toggleMessages()
{
	//
	if ($(this)[0].className.search('active') == -1)
	{
		$(this)[0].id == 'r' ? getReceivedMessages() : getSentMessages();
	}
}

/**
 *
 */
$(document).ready(function () {

	//
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

	//
	tableMessageLst = new Tabulator("#lstMessagesPanel", {
		height: "400px",
		pagination: "local",
		columns: [
			{title: "Subject", field: "subject", width: 700, responsive: 0},
			{title: "From", field: "from", width: 400},
			{title: "Date", field: "sent", sorter: "datetime", width: 150}
		],
		rowClick: changeTinyMCE
	});

	//
	$('.toggleMessages .btn').click(toggleMessages);

	//
	getReceivedMessages();

});
