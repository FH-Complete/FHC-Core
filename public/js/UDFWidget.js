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
								FHC_DialogLib.alertSuccess("Successfully saved");
							}
							else
							{
								var msgError = "An error occurred while saving these fields:<br>";
								var errors = FHC_AjaxClient.getError(data);

								for (var i = 0; i < errors.length; i++)
								{
									var error = errors[i];

									msgError += FHC_AjaxClient.getError(error)+ "<br>";
								}

								FHC_DialogLib.alertError(msgError);
							}
						}
					},
					{
						errorCallback: function(data, textStatus, jqXHR) {
							FHC_DialogLib.alertError("A generic error occurred, please contact the support");
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

