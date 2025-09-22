/**
 * To add bootstrap classes, e.g. in case usage of non-bootstrap widgets in a bootstrap page
 * NOTE: avoid using this if possible
 */
$(document).ready(function() {
	Bootstrapper.bootstraphtml();
});

var Bootstrapper = {
	bootstraphtml: function ()
	{
		$("input[type=text], select, textarea").addClass("form-control");
		$("button, input[type=button]").addClass("btn btn-default");
		$("table").addClass("table-condensed");
		$("#tableDataset").addClass('table-bordered');
	}
};

