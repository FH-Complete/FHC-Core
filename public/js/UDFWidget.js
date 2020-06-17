/**
 * UDFWidget JS magic
 */

/**
 * FHC_UDFWidget this object is used to render the GUI of a table widget and to operate with it
 */
var FHC_UDFWidget = {

	//------------------------------------------------------------------------------------------------------------------
	// Public methods

	/**
	 * To display the TableWidget using the loaded data prenset in the session
	 */
	display: function() {

		$("div[type*='UDFWidget']").each(function(i, udfWidgetDiv) {

			var saveButton = $('<input type="button" value="Speichern">');

			saveButton.on('click', function() {

				var udfs = {};

				$("div[udfUniqueId*='" + udfWidgetDiv.attributes["udfUniqueId"].nodeValue + "']").find("[name^='udf_']").each(function(i, udf) {

					if (udf.type == 'checkbox')
					{
						udfs[udf.id] = udf.checked == true;
					}
					else if (udf.type == 'select-one' || udf.type == 'select-multiple')
					{
						udfs[udf.id] = null;

						if (!isNaN(udf.value) && udf.value.trim() != '')
						{
							udfs[udf.id] = Number(udf.value);
						}
					}
					else
					{
						udfs[udf.id] = udf.value;
					}
				});

				FHC_AjaxClient.ajaxCallPost(
					"widgets/UDF/saveUDFs",
					{
						udfUniqueId: FHC_UDFWidget._getUDFUniqueIdPrefix() + "/" + udfWidgetDiv.attributes["udfUniqueId"].nodeValue,
						udfs: JSON.stringify(udfs)
					},
					{
						successCallback: function(data, textStatus, jqXHR) {

							if (FHC_AjaxClient.hasData(data))
							{
								FHC_DialogLib.alertSuccess('Done!');
							}
							else
							{
								console.log(FHC_AjaxClient.getError(data));
							}
						}
					},
					{
						errorCallback: function(data, textStatus, jqXHR) {
							console.log('Contact the administrator');
						}
					}
				);

			});

			saveButton.appendTo(udfWidgetDiv);

		});

	},

	//------------------------------------------------------------------------------------------------------------------
	// Private methods

	/**
	 * To retrive the page where the TableWidget is used, using the FHC_JS_DATA_STORAGE_OBJECT
	 */
	_getUDFUniqueIdPrefix: function() {

		return FHC_JS_DATA_STORAGE_OBJECT.called_path + "/" + FHC_JS_DATA_STORAGE_OBJECT.called_method;
	}
};

/**
 * When JQuery is up
 */
$(document).ready(function() {

	FHC_UDFWidget.display();

});
