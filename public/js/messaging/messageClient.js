/**
 *
 */
$(document).ready(function () {

	FHC_AjaxClient.ajaxCallGet(
		FHC_JS_DATA_STORAGE_OBJECT.called_path + '/listMessages',
		null,
		{
			successCallback: function(data, textStatus, jqXHR) {

				if (FHC_AjaxClient.hasData(data))
				{
					try
					{
						var jsonMessageLst = JSON.parse(FHC_AjaxClient.getData(data));

						console.log(jsonMessageLst);

						var tableMessageLst = new Tabulator("#lstMessagesPanel", {
							height: "400px",
						    data: jsonMessageLst,
						    dataTree: true,
						    dataTreeStartExpanded: true,
							dataTreeElementColumn: "subject",
						    columns: [
							    {title: "Subject", field: "subject", width: 700, responsive: 0},
							    {title: "From", field: "from", width: 400},
							    {title: "Date", field: "sent", sorter: "datetime", width: 150}
						    ],
							rowClick: function(e, row) {
								// TODO
    						}
						});
					}
					catch (syntaxError)
					{
						FHC_DialogLib.alertError("An error occurred while retrieving message, contact the website administrator");
					}
				}
				else
				{
					FHC_DialogLib.alertWarning("No message currently available");
				}
			},
			errorCallback: function() {

			},
			veilTimeout: 300
		}
	);

});
