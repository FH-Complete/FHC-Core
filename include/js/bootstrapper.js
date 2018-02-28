/*
file for adding bootstrap classes, e.g. in case usage of non-bootstrap widgets in a bootstrap page
AVOID USING THIS IF POSSIBLE
 */
$(document).ready(
	function()
	{
		$("input[type=text], select").addClass("form-control");
		$("button, input[type=button]").addClass("btn btn-default");
		$("table").addClass("table-condensed");
	}
);
