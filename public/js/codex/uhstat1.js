/**
 * javascript file for UHSTAT1 GUI
 */
$(document).ready(function ()
{
	window.setTimeout(function() {
		$("#uhstat_success_alert").fadeTo(500, 0).slideUp(500, function(){
			$(this).remove();
		});
	}, 1000);
});
