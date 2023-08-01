/**
 * javascript file for UHSTAT1 GUI
 */
$(document).ready(function ()
{
	window.setTimeout(function() {
		$("#success-alert_uhstat1").fadeTo(500, 0).slideUp(500, function(){
			$(this).remove();
		});
	}, 1000);
});
